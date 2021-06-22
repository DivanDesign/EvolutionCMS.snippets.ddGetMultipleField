<?php
/**
 * ddGetMultipleField
 * @version 3.5.1 (2020-06-22)
 * 
 * @see README.md
 * 
 * @link https://code.divandesign.biz/modx/ddgetmultiplefield
 * 
 * @copyright 2009–2020 DD Group {@link https://DivanDesign.biz }
 */

global $modx;


//# Include
$ddToolsPath =
	$modx->getConfig('base_path') .
	'assets/libs/ddTools/modx.ddtools.class.php'
;

if (!file_exists($ddToolsPath)){
	$ddToolsPath = str_replace(
		'assets/libs/',
		'assets/snippets/',
		$ddToolsPath
	);
	$modx->logEvent(
		1,
		2,
		'<p>Please update the “<a href="http://code.divandesign.biz/modx/ddtools">modx.ddTools</a>” library.</p><p>The snippet has been called in the document with id ' . $modx->documentIdentifier . '.</p>',
		$modx->currentSnippet
	);
}

//Include (MODX)EvolutionCMS.libraries.ddTools
require_once($ddToolsPath);


//# Prepare params
//Backward compatibility
$params = \ddTools::verifyRenamedParams([
	'params' => $params,
	'compliance' => [
		'inputString' => 'string',
		'inputString_docField' => 'docField',
		'inputString_docId' => 'docId',
		'inputString_rowDelimiter' => 'rowDelimiter',
		'inputString_colDelimiter' => 'colDelimiter'
	],
	'returnCorrectedOnly' => false
]);

$params = \DDTools\ObjectTools::extend([
	'objects' => [
		//Defaults
		(object) [
			'inputString' => '',
			'inputString_docField' => null,
			'inputString_docId' => null,
			'inputString_rowDelimiter' => '||',
			'inputString_colDelimiter' => '::',
			'startRow' => 0,
			'totalRows' => 'all',
			'columns' => 'all',
			'filter' => null,
			'removeEmptyRows' => true,
			'removeEmptyCols' => true,
			'sortBy' => '0',
			'sortDir' => null,
			'typography' => null,
			'outputFormat' => 'html',
			'rowGlue' => '',
			'colGlue' => '',
			'rowTpl' => null,
			'colTpl' => null,
			'outerTpl' => null,
			'placeholders' => [],
			'urlencode' => false,
			'totalRowsToPlaceholder' => null,
			'resultToPlaceholder' => null
		],
		$params
	]
]);

//Boolean
foreach (
	[
		'removeEmptyRows',
		'removeEmptyCols',
		'urlencode'
	] as
	$paramName
){
	$params->{$paramName} = boolval($params->{$paramName});
}

//Integer
$params->startRow = intval($params->startRow);

//Comma separated string
foreach (
	[
		'sortBy',
		'typography',
		'colTpl'
	] as
	$paramName
){
	if (
		//Zero indexes for `sortBy` and `typography` must be used
		$params->{$paramName} === '0' ||
		!empty($params->{$paramName})
	){
		$params->{$paramName} = explode(
			',',
			$params->{$paramName}
		);
	}
}

if (!is_numeric($params->totalRows)){
	$params->totalRows = 'all';
}

if ($params->columns != 'all'){
	$params->columns = explode(
		',',
		$params->columns
	);
}

//Хитро-мудро для array_intersect_key
if (is_array($params->columns)){
	$params->columns = array_combine(
		$params->columns,
		$params->columns
	);
}

if (!empty($params->sortDir)){
	$params->sortDir = strtoupper($params->sortDir);
}

$params->outputFormat = strtolower($params->outputFormat);

//Prepare templates
foreach (
	[
		'rowTpl',
		'outerTpl'
	] as
	$paramName
){
	//Chunk content or inline template
	$params->{$paramName} = $modx->getTpl($params->{$paramName});
}

if (!empty($params->colTpl)){
	//Получим содержимое шаблонов
	foreach (
		$params->colTpl as
		$colTpl_itemNumber =>
		$colTpl_itemValue
	){
		//Chunk content or inline template
		$params->colTpl[$colTpl_itemNumber] = $modx->getTpl($params->colTpl[$colTpl_itemNumber]);
	}
	
	$params->colTpl = str_replace(
		'null',
		'',
		$params->colTpl
	);
}

//Дополнительные данные
$params->placeholders = \ddTools::encodedStringToArray($params->placeholders);
//Unfold for arrays support (e. g. `{"somePlaceholder1": "test", "somePlaceholder2": {"a": "one", "b": "two"} }` => `[+somePlaceholder1+]`, `[+somePlaceholder2.a+]`, `[+somePlaceholder2.b+]`; `{"somePlaceholder1": "test", "somePlaceholder2": ["one", "two"] }` => `[+somePlaceholder1+]`, `[+somePlaceholder2.0+]`, `[somePlaceholder2.1]`)
$params->placeholders = \ddTools::unfoldArray($params->placeholders);

//Если задано имя поля, которое необходимо получить
if (!empty($params->inputString_docField)){
	$params->inputString = \ddTools::getTemplateVarOutput(
		[$params->inputString_docField],
		$params->inputString_docId
	);
	
	$params->inputString = $params->inputString[$params->inputString_docField];
}

//Если заданы условия фильтрации
if (!empty($params->filter)){
	//Backward compatibility
	$params->filter = str_replace(
		[
			'::',
			'<>'
		],
		[
			'==',
			'!='
		],
		$params->filter
	);
	
	//Разбиваем по условию «или»
	$filterSource = explode(
		'||',
		$params->filter
	);
	
	//Clear
	$params->filter = [];
	
	//Перебираем по условию «или»
	foreach (
		$filterSource as
		$orIndex =>
		$orCondition
	){
		$params->filter[$orIndex] = [];
		
		//Перебираем по условию «и»
		foreach (
			//Разбиваем по условию «и»
			explode(
				'&&',
				$orCondition
			) as
			$andIndex =>
			$andCondition
		){
			//Добавляем вид сравнения для колонки
			$params->filter[$orIndex][$andIndex] = [
				'isEqual' =>
					strpos(
						$andCondition,
						'=='
					) !== false
				,
				'columnKey' => '',
				'columnValue' => ''
			];
			
			//Разбиваем по колонке/значению
			$andCondition = explode(
				(
					$params->filter[$orIndex][$andIndex]['isEqual'] ?
					'==' :
					'!='
				),
				$andCondition
			);
			
			//Добавляем правило для соответствующей колонки
			$params->filter[$orIndex][$andIndex]['columnKey'] = trim($andCondition[0]);
			$params->filter[$orIndex][$andIndex]['columnValue'] = trim(
				$andCondition[1],
				//Trim whitespaces and quotes
				" \t\n\r\0\x0B\"'"
			);
		}
	}
}


//# Run
//The snippet must return an empty string even if result is absent
$snippetResult = '';

//Если задано значение поля
if (strlen($params->inputString) > 0){
	//Являются ли разделители регулярками
	$inputString_rowDelimiterIsRegexp =
		(
			filter_var(
				$params->inputString_rowDelimiter,
				FILTER_VALIDATE_REGEXP,
				[
					'options' => [
						'regexp' => '/^\/.*\/[a-z]*$/'
					]
				]
			) !== false
		) ?
		true :
		false
	;
	
	$inputString_colDelimiterIsRegexp =
		(
			filter_var(
				$params->inputString_colDelimiter,
				FILTER_VALIDATE_REGEXP,
				[
					'options' => [
						'regexp' => '/^\/.*\/[a-z]*$/'
					]
				]
			) !== false
		) ?
		true :
		false
	;
	
	//JSON (first letter is “{” or “[”)
	if (
		in_array(
			substr(
				ltrim($params->inputString),
				0,
				1
			),
			[
				'{',
				'['
			]
		)
	){
		try {
			$data = json_decode(
				$params->inputString,
				true
			);
		}catch (\Exception $e){
			//Flag
			$data = [];
		}
	}
	//Not JSON
	if (empty($data)){
		//Разбиваем на строки
		$data =
			$inputString_rowDelimiterIsRegexp ?
			preg_split(
				$params->inputString_rowDelimiter,
				$params->inputString
			) :
			explode(
				$params->inputString_rowDelimiter,
				$params->inputString
			)
		;
	}
	
	//Convert data to array for code simplification
	$data = (array) $data;
	
	//Общее количество строк
	$total = count($data);
	
	//Перебираем строки, разбиваем на колонки
	foreach (
		$data as
		$rowKey =>
		$rowValue
	){
		if (!is_array($rowValue)){
			$data[$rowKey] =
				$inputString_colDelimiterIsRegexp ?
				preg_split(
					$params->inputString_colDelimiter,
					$rowValue
				) :
				explode(
					$params->inputString_colDelimiter,
					$rowValue
				)
			;
		}
		
		//Если необходимо получить какие-то конкретные значения
		if (!empty($params->filter)){
			//Перебираем условия `or`
			foreach (
				$params->filter as
				$orIndex =>
				$orCondition
			){
				//Считаем, что вариант проходит, если не доказано обратное
				$isFound = true;
				
				//Перебираем условия `and`
				foreach (
					$orCondition as
					$andIndex =>
					$andCondition
				){
					//В зависимости от того, должно или нет значение в колонке быть равно фильтру, присваиваем флагу результат
					if ($andCondition['isEqual']){
						//Если должно быть равно
						$isFound = $data[$rowKey][$andCondition['columnKey']] == $andCondition['columnValue'];
					}else{
						//Если не должно быть равно 
						$isFound = $data[$rowKey][$andCondition['columnKey']] != $andCondition['columnValue'];
					}
					
					//Если условие сменилось на ложь, значит переходим к следующему условию `or`
					if (!$isFound){
						break;
					}
				}
				
				//Если все условия `and` прошли проверку, выходим из цикла `or`
				if ($isFound){
					break;
				}
			}
			
			//Если на выходе из цикла мы видим, что ни одно из условий не выполнено, сносим строку нафиг
			if (!$isFound){
				unset($data[$rowKey]);
			}
		}
		
		//Если нужно получить какую-то конкретную колонку
		if (
			$params->columns != 'all' &&
			//Также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее
			isset($data[$rowKey])
		){
			//Выбираем только необходимые колонки + Сбрасываем ключи массива
			$data[$rowKey] = array_values(array_intersect_key(
				$data[$rowKey],
				$params->columns
			));
		}
		
		//Если нужно удалять пустые строки
		if (
			$params->removeEmptyRows &&
			//Также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее
			isset($data[$rowKey])
		){
			//Если строка пустая, удаляем
			if (
				strlen(implode(
					'',
					$data[$rowKey]
				)) == 0
			){
				unset($data[$rowKey]);
			}
		}
	}
	
	//Если что-то есть (могло ничего не остаться после удаления пустых и/или получения по значениям)
	if (count($data) > 0){
		//Если надо сортировать
		if (!empty($params->sortDir)){
			//Если надо в случайном порядке - шафлим
			if ($params->sortDir == 'RAND'){
				//Shuffle array preserve keys
				uksort(
					$data,
					function(){
						return rand(
							-1,
							1
						);
					}
				);
			//Если надо просто в обратном порядке
			}elseif ($params->sortDir == 'REVERSE'){
				$data = array_reverse($data);
			}else{
				//Сортируем результаты
				$data = \ddTools::sort2dArray(
					$data,
					$params->sortBy,
					(
						$params->sortDir == 'ASC' ?
						1 :
						-1
					)
				);
			}
		}
		
		//Обрабатываем слишком большой индекс
		if ($params->startRow > count($data) - 1){
			$params->startRow = count($data) - 1;
		}
		
		//Если нужны все элементы
		if ($params->totalRows == 'all'){
			$data = array_slice(
				$data,
				$params->startRow
			);
		}else{
			$data = array_slice(
				$data,
				$params->startRow,
				$params->totalRows
			);
		}
		
		//Общее количество возвращаемых строк
		$resultTotal = count($data);
		
		//Плэйсхолдер с общим количеством
		if (!empty($params->totalRowsToPlaceholder)){
			$modx->setPlaceholder(
				$params->totalRowsToPlaceholder,
				$resultTotal
			);
		}
		
		//Если нужно типографировать
		if (!empty($params->typography)){
			//Придётся ещё раз перебрать результат
			foreach (
				$data as
				$rowKey =>
				$rowValue
			){
				//Перебираем колонки, заданные для типографирования
				foreach (
					$params->typography as
					$colKey
				){
					//Если такая колонка существует, типографируем
					if (isset($data[$rowKey][$colKey])){
						$data[$rowKey][$colKey] = $modx->runSnippet(
							'ddTypograph',
							[
								'text' => $data[$rowKey][$colKey]
							]
						);
					}
				}
			}
		}
		
		//Если вывод в массив
		if ($params->outputFormat == 'array'){
			$snippetResult = $data;
		}else{
			$resTemp = [];
			
			//Если вывод просто в формате html
			if (
				$params->outputFormat == 'html' ||
				$params->outputFormat == 'htmlarray'
			){
				if (
					//Если шаблоны колонок заданы
					!empty($params->colTpl) &&
					//Но их не хватает
					(
						$temp =
							count(array_values($data)[0]) -
							count($params->colTpl)
					) > 0
				){
					//Дозабьём недостающие последним
					$params->colTpl = array_merge(
						$params->colTpl,
						array_fill(
							$temp - 1,
							$temp,
							$params->colTpl[count($params->colTpl) - 1]
						)
					);
				}
				
				//Если задан шаблон строки
				if (!empty($params->rowTpl)){
					//Перебираем строки
					foreach (
						$data as
						$rowKey =>
						$rowValue
					){
						$resTemp[$rowKey] = [
							//Запишем номер строки
							'rowNumber.zeroBased' => $rowKey,
							'rowNumber' => $rowKey + 1,
							//И общее количество элементов
							'total' => $total,
							'resultTotal' => $resultTotal
						];
						
						//Перебираем колонки
						foreach (
							$rowValue as
							$colKey =>
							$colValue
						){
							//Если нужно удалять пустые значения
							if (
								$params->removeEmptyCols &&
								!strlen($colValue)
							){
								$resTemp[$rowKey]['col' . $colKey] = '';
							}else{
								//Если есть шаблоны значений колонок
								if (
									!empty($params->colTpl) &&
									strlen($params->colTpl[$colKey]) > 0
								){
									$resTemp[$rowKey]['col' . $colKey] = $modx->parseText(
										$params->colTpl[$colKey],
										array_merge(
											[
												'val' => $colValue,
												'rowNumber.zeroBased' => $resTemp[$rowKey]['rowNumber.zeroBased'],
												'rowNumber' => $resTemp[$rowKey]['rowNumber']
											],
											$params->placeholders
										)
									);
								}else{
									$resTemp[$rowKey]['col' . $colKey] = $colValue;
								}
							}
						}
						
						$resTemp[$rowKey] = $modx->parseText(
							$params->rowTpl,
							array_merge(
								$resTemp[$rowKey],
								$params->placeholders
							)
						);
					}
				}else{
					foreach (
						$data as
						$rowKey =>
						$rowValue
					){
						//Если есть шаблоны значений колонок
						if (!empty($params->colTpl)){
							foreach (
								$rowValue as
								$colKey =>
								$colValue
							){
								if (
									$params->removeEmptyCols &&
									!strlen($colValue)
								){
									unset($rowValue[$colKey]);
								}elseif (strlen($params->colTpl[$colKey]) > 0){
									$rowValue[$colKey] = $modx->parseText(
										$params->colTpl[$colKey],
										array_merge(
											[
												'val' => $colValue,
												'rowNumber.zeroBased' => $rowKey,
												'rowNumber' => $rowKey + 1
											],
											$params->placeholders
										)
									);
								}
							}
						}
						
						$resTemp[$rowKey] = implode(
							$params->colGlue,
							$rowValue
						);
					}
				}
				
				if ($params->outputFormat == 'html'){
					$snippetResult = implode(
						$params->rowGlue,
						$resTemp
					);
				}else{
					$snippetResult = $resTemp;
				}
			//Если вывод в формате JSON
			}elseif ($params->outputFormat == 'json'){
				$resTemp = $data;
				
				//Если нужно выводить только одну колонку
				if (
					$params->columns != 'all' &&
					count($params->columns) == 1
				){
					$resTemp = array_map(
						'implode',
						$resTemp
					);
				}
				
				//Если нужно получить какой-то конкретный элемент, а не все
				if ($params->totalRows == '1'){
					$snippetResult = json_encode($resTemp[$params->startRow]);
				}else{
					$snippetResult = json_encode($resTemp);
				}
				
				//Это чтобы MODX не воспринимал как вызов сниппета
				$snippetResult = strtr(
					$snippetResult,
					[
						'[[' => '[ [',
						']]' => '] ]'
					]
				);
			}
			
			//Если оборачивающий шаблон задан (и вывод не в массив), парсим его
			if (!empty($params->outerTpl)){
				$resTemp = [];
				
				//Элемент массива 'result' должен находиться самым первым, иначе дополнительные переданные плэйсхолдеры в тексте не найдутся! 
				$resTemp['result'] = $snippetResult;
				
				//Преобразуем результат в одномерный массив
				$data = \ddTools::unfoldArray($data);
				
				//Добавляем 'row' и 'val' к ключам
				foreach (
					$data as
					$rowKey =>
					$rowValue
				){
					 $resTemp[preg_replace(
					 	'/(\d)\.(\d)/',
					 	'row$1.col$2',
					 	$rowKey
					 )] = $rowValue;
				}
				
				$resTemp = array_merge(
					$resTemp,
					$params->placeholders
				);
				
				$resTemp['total'] = $total;
				$resTemp['resultTotal'] = $resultTotal;
				
				$snippetResult = $modx->parseText(
					$params->outerTpl,
					$resTemp
				);
			}
			
			//Если нужно URL-кодировать строку
			if ($params->urlencode){
				$snippetResult = rawurlencode($snippetResult);
			}
		}
	}
}

//Если надо, выводим в плэйсхолдер
if (!empty($params->resultToPlaceholder)){
	$modx->setPlaceholder(
		$params->resultToPlaceholder,
		$snippetResult
	);
	
	$snippetResult = '';
}

return $snippetResult;
?>