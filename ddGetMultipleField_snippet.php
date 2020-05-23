<?php
/**
 * ddGetMultipleField
 * @version 3.4 (2018-11-14)
 * 
 * @see README.md
 * 
 * @link https://code.divandesign.biz/modx/ddgetmultiplefield
 * 
 * @copyright 2009–2018 DD Group {@link https://DivanDesign.biz }
 */

global $modx;

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

//The snippet must return an empty string even if result is absent
$snippetResult = '';

//Backward compatibility
extract(\ddTools::verifyRenamedParams(
	$params,
	[
		'inputString' => 'string',
		'inputString_docField' => 'docField',
		'inputString_docId' => 'docId',
		'inputString_rowDelimiter' => 'rowDelimiter',
		'inputString_colDelimiter' => 'colDelimiter'
	]
));

//Если задано имя поля, которое необходимо получить
if (isset($inputString_docField)){
	$inputString = \ddTools::getTemplateVarOutput(
		[$inputString_docField],
		$inputString_docId
	);
	$inputString = $inputString[$inputString_docField];
}

//Если задано значение поля
if (
	isset($inputString) &&
	strlen($inputString) > 0
){
	if (!isset($inputString_rowDelimiter)){
		$inputString_rowDelimiter = '||';
	}
	if (!isset($inputString_colDelimiter)){
		$inputString_colDelimiter = '::';
	}
	
	//Являются ли разделители регулярками
	$inputString_rowDelimiterIsRegexp =
		(
			filter_var(
				$inputString_rowDelimiter,
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
				$inputString_colDelimiter,
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
	
	//Если заданы условия фильтрации
	if (isset($filter)){
		//Backward compatibility
		$filter = str_replace(
			[
				'::',
				'<>'
			],
			[
				'==',
				'!='
			],
			$filter
		);
		
		//Разбиваем по условию «или»
		$filterSource = explode(
			'||',
			$filter
		);
		
		//Clear
		$filter = [];
		
		//Перебираем по условию «или»
		foreach (
			$filterSource as
			$orIndex =>
			$orCondition
		){
			$filter[$orIndex] = [];
			
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
				$filter[$orIndex][$andIndex] = [
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
						$filter[$orIndex][$andIndex]['isEqual'] ?
						'==' :
						'!='
					),
					$andCondition
				);
				
				//Добавляем правило для соответствующей колонки
				$filter[$orIndex][$andIndex]['columnKey'] = $andCondition[0];
				$filter[$orIndex][$andIndex]['columnValue'] = $andCondition[1];
			}
		}
	}else{
		$filter = false;
	}
	
	$columns =
		isset($columns) ?
		explode(
			',',
			$columns
		) :
		'all'
	;
	//Хитро-мудро для array_intersect_key
	if (is_array($columns)){
		$columns = array_combine(
			$columns,
			$columns
		);
	}
	if (!isset($rowGlue)){
		$rowGlue = '';
	}
	if (!isset($colGlue)){
		$colGlue = '';
	}
	
	$removeEmptyRows =
		(
			isset($removeEmptyRows) &&
			$removeEmptyRows == '0'
		) ?
		false :
		true
	;
	$removeEmptyCols =
		(
			isset($removeEmptyCols) &&
			$removeEmptyCols == '0'
		) ?
		false :
		true
	;
	$urlencode =
		(
			isset($urlencode) &&
			$urlencode == '1'
		) ?
		true :
		false
	;
	$outputFormat =
		isset($outputFormat) ?
		strtolower($outputFormat) :
		'html'
	;
	
	//JSON (first letter is “{” or “[”)
	if (
		in_array(
			substr(
				ltrim($inputString),
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
				$inputString,
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
				$inputString_rowDelimiter,
				$inputString
			) :
			explode(
				$inputString_rowDelimiter,
				$inputString
			)
		;
	}
	
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
					$inputString_colDelimiter,
					$rowValue
				) :
				explode(
					$inputString_colDelimiter,
					$rowValue
				)
			;
		}
		
		//Если необходимо получить какие-то конкретные значения
		if ($filter !== false){
			//Перебираем условия `or`
			foreach (
				$filter as
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
			$columns != 'all' &&
			//Также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее
			isset($data[$rowKey])
		){
			//Выбираем только необходимые колонки + Сбрасываем ключи массива
			$data[$rowKey] = array_values(array_intersect_key(
				$data[$rowKey],
				$columns
			));
		}
		
		//Если нужно удалять пустые строки
		if (
			$removeEmptyRows &&
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
	
	//Сбрасываем ключи массива (пригодится для выборки конкретного значения)
	$data = array_values($data);
	
	//Если что-то есть (могло ничего не остаться после удаления пустых и/или получения по значениям)
	if (count($data) > 0){
		//Если надо сортировать
		if (isset($sortDir)){
			$sortDir = strtoupper($sortDir);
			
			if (!isset($sortBy)){
				$sortBy = '0';
			}
			
			//Если надо в случайном порядке - шафлим
			if ($sortDir == 'RAND'){
				shuffle($data);
			//Если надо просто в обратном порядке
			}else if ($sortDir == 'REVERSE'){
				$data = array_reverse($data);
			}else{
				//Сортируем результаты
				$data = \ddTools::sort2dArray(
					$data,
					explode(
						',',
						$sortBy
					),
					(
						$sortDir == 'ASC' ?
						1 :
						-1
					)
				);
			}
		}
		
		if (
			!isset($startRow) ||
			!is_numeric($startRow)
		){
			$startRow = '0';
		}
		
		//Обрабатываем слишком большой индекс
		if (!isset($data[$startRow])){
			$startRow = count($data) - 1;
		}
		
		//Если общее количество элементов не задано или задано плохо, читаем, что нужны все
		if (
			!isset($totalRows) ||
			!is_numeric($totalRows)
		){
			$totalRows = 'all';
		}
		
		//Если нужны все элементы
		if ($totalRows == 'all'){
			$data = array_slice(
				$data,
				$startRow
			);
		}else{
			$data = array_slice(
				$data,
				$startRow,
				$totalRows
			);
		}
		
		//Общее количество возвращаемых строк
		$resultTotal = count($data);
		
		//Плэйсхолдер с общим количеством
		if (isset($totalRowsToPlaceholder)){
			$modx->setPlaceholder(
				$totalRowsToPlaceholder,
				$resultTotal
			);
		}
		
		//Если нужно типографировать
		if (isset($typography)){
			$typography = explode(
				',',
				$typography
			);
			
			//Придётся ещё раз перебрать результат
			foreach (
				$data as
				$rowKey =>
				$rowValue
			){
				//Перебираем колонки, заданные для типографирования
				foreach (
					$typography as
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
		if ($outputFormat == 'array'){
			$snippetResult = $data;
		}else{
			$resTemp = [];
			
			//Дополнительные данные
			if (
				isset($placeholders) &&
				trim($placeholders) != ''
			){
				$placeholders = \ddTools::encodedStringToArray($placeholders);
				//Unfold for arrays support (e. g. `{"somePlaceholder1": "test", "somePlaceholder2": {"a": "one", "b": "two"} }` => `[+somePlaceholder1+]`, `[+somePlaceholder2.a+]`, `[+somePlaceholder2.b+]`; `{"somePlaceholder1": "test", "somePlaceholder2": ["one", "two"] }` => `[+somePlaceholder1+]`, `[+somePlaceholder2.0+]`, `[somePlaceholder2.1]`)
				$placeholders = \ddTools::unfoldArray($placeholders);
			}else{
				$placeholders = [];
			}
			
			//Если вывод просто в формате html
			if (
				$outputFormat == 'html' ||
				$outputFormat == 'htmlarray'
			){
				//Шаблоны колонок
				$colTpl =
					isset($colTpl) ?
					explode(
						',',
						$colTpl
					) :
					false
				;
				
				//Если шаблоны колонок заданы, но их не хватает
				if ($colTpl !== false){
					//Получим содержимое шаблонов
					foreach (
						$colTpl as
						$colTpl_itemNumber =>
						$colTpl_itemValue
					){
						//Chunk content or inline template
						$colTpl[$colTpl_itemNumber] = $modx->getTpl($colTpl[$colTpl_itemNumber]);
					}
					
					if (
						(
							$temp =
								count($data[0]) -
								count($colTpl)
						) > 0
					){
						//Дозабьём недостающие последним
						$colTpl = array_merge(
							$colTpl,
							array_fill(
								$temp - 1,
								$temp,
								$colTpl[count($colTpl) - 1]
							)
						);
					}
					
					$colTpl = str_replace(
						'null',
						'',
						$colTpl
					);
				}
				
				//Если задан шаблон строки
				if (isset($rowTpl)){
					//Chunk content or inline template
					$rowTpl = $modx->getTpl($rowTpl);
					
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
								$removeEmptyCols &&
								!strlen($colValue)
							){
								$resTemp[$rowKey]['col' . $colKey] = '';
							}else{
								//Если есть шаблоны значений колонок
								if (
									$colTpl !== false &&
									strlen($colTpl[$colKey]) > 0
								){
									$resTemp[$rowKey]['col'.$colKey] = $modx->parseText(
										$colTpl[$colKey],
										array_merge(
											[
												'val' => $colValue,
												'rowNumber.zeroBased' => $resTemp[$rowKey]['rowNumber.zeroBased'],
												'rowNumber' => $resTemp[$rowKey]['rowNumber']
											],
											$placeholders
										)
									);
								}else{
									$resTemp[$rowKey]['col' . $colKey] = $colValue;
								}
							}
						}
						
						$resTemp[$rowKey] = $modx->parseText(
							$rowTpl,
							array_merge(
								$resTemp[$rowKey],
								$placeholders
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
						if ($colTpl !== false){
							foreach (
								$rowValue as
								$colKey =>
								$colValue
							){
								if (
									$removeEmptyCols &&
									!strlen($colValue)
								){
									unset($rowValue[$colKey]);
								}else if (strlen($colTpl[$colKey]) > 0){
									$rowValue[$colKey] = $modx->parseText(
										$colTpl[$colKey],
										array_merge(
											[
												'val' => $colValue,
												'rowNumber.zeroBased' => $rowKey,
												'rowNumber' => $rowKey + 1
											],
											$placeholders
										)
									);
								}
							}
						}
						$resTemp[$rowKey] = implode(
							$colGlue,
							$rowValue
						);
					}
				}
				
				if ($outputFormat == 'html'){
					$snippetResult = implode(
						$rowGlue,
						$resTemp
					);
				}else{
					$snippetResult = $resTemp;
				}
			//Если вывод в формате JSON
			}else if ($outputFormat == 'json'){
				$resTemp = $data;
				
				//Если нужно выводить только одну колонку
				if (
					$columns != 'all' &&
					count($columns) == 1
				){
					$resTemp = array_map(
						'implode',
						$resTemp
					);
				}
				
				//Если нужно получить какой-то конкретный элемент, а не все
				if ($totalRows == '1'){
					$snippetResult = json_encode($resTemp[$startRow]);
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
			if (isset($outerTpl)){
				//Chunk content or inline template
				$outerTpl = $modx->getTpl($outerTpl);
				
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
					$placeholders
				);
				
				$resTemp['total'] = $total;
				$resTemp['resultTotal'] = $resultTotal;
				
				$snippetResult = $modx->parseText(
					$outerTpl,
					$resTemp
				);
			}
			
			//Если нужно URL-кодировать строку
			if ($urlencode){
				$snippetResult = rawurlencode($snippetResult);
			}
		}
	}
}

//Если надо, выводим в плэйсхолдер
if (isset($resultToPlaceholder)){
	$modx->setPlaceholder(
		$resultToPlaceholder,
		$snippetResult
	);
	
	$snippetResult = '';
}

return $snippetResult;
?>