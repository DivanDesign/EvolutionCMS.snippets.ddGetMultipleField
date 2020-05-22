<?php
/**
 * ddGetMultipleField
 * @version 3.4 (2018-11-14)
 * 
 * @desc A snippet for processing, manipulations and custom output structured data (JSON or separated by delimiters strings).
 * @note The fields formed by the “mm_ddMultipleFields” widget values output gets more convinient with the snippet.
 * 
 * @uses PHP >= 5.4.
 * @uses (MODX)EvolutionCMS >= 1.1.
 * @uses (MODX)EvolutionCMS.libraries.ddTools >= 0.18.
 * @uses (MODX)EvolutionCMS.snippets.ddTypograph >= 1.4.3 (if typography is required).
 * 
 * @param $inputString {stirngJsonArray|stirngJsonObject|stringSeparated} — The input string containing values in JSON (https://en.wikipedia.org/wiki/JSON) or separated by `$inputString_rowDelimiter` and `$inputString_colDelimiter`. @required
 * @param $inputString_docField {string} — The name of the document field/TV which value is required to get. If the parameter is passed then the input string will be taken from the field/TV and `inputString` will be ignored. Default: —.
 * @param $inputString_docId {integer} — ID of the document which field/TV value is required to get. `inputString_docId` equals the current document id since `inputString_docId` is unset. Default: —.
 * @param $inputString_rowDelimiter {string|regexp} — The input string row delimiter (if not JSON). Default: '||'.
 * @param $inputString_colDelimiter {string|regexp} — The input string column delimiter (if not JSON). Default: '::'.
 * @param $startRow {integer} — The index of the initial row (indexes start at 0). Default: 0.
 * @param $totalRows {integer|'all'} — The maximum number of rows to return. All rows will be returned if `totalRows` == 'all'. Default: 'all'.
 * @param $columns {stringCommaSeparated|'all'} — The indexes of columns to return (indexes start at 0). All columns will be returned if `columns` == 'all'. Default: 'all'.
 * @param $filter {stringSeparated} — Filter clause for columns. Thus, '0::a||0::b&&1::1&&2<>' makes the columns with either 'a' in the 0 column or 'b' in the 0 column and with 1 in the 1 and not '' in the 2 column to be returned. Default: ''.
 * @param $removeEmptyRows {0|1} — Is it required to remove empty rows? Default: 1.
 * @param $removeEmptyCols {0|1} — Is it required to remove empty columns? Default: 1.
 * @param $sortBy {stringCommaSeparated} — The index of the column to sort by (indexes start at 0). The parameter also takes comma-separated values for multiple sort, e.g. '0,1'. Default: '0'.
 * @param $sortDir {'ASC'|'DESC'|'RAND'|'REVERSE'|''} — Rows sorting direction. The rows will be returned in reversed order if `sortDir` == 'REVERSE'. Default: ''.
 * @param $typography {stringCommaSeparated} — The comma separated indexes of the columns which values have to be corrected (indexes start at 0). If unset, there will be no correction. Default: —.
 * @param $outputFormat {'html'|'JSON'|'array'|'htmlarray'} — Result output format. Default: 'html'.
 * @param $rowGlue {string} — The string that combines rows while rendering. It can be used along with `rowTpl`. Default: ''.
 * @param $colGlue {string} — The string that combines columns while rendering. It can be used along with `colTpl`, but not with `rowTpl` for obvious reasons. Default: ''.
 * @param $rowTpl {stringChunkName|string} — The template for row rendering (`outputFormat` has to be == 'html'). Use inline templates starting with `@CODE:`. Available placeholders: [+rowNumber+] (index of current row, starts at 1), [+rowNumber.zeroBased+] (index of current row, starts at 0), [+total+] (total number of rows), [+resultTotal+] (total number of returned rows), [+col0+],[+col1+],… (column values). Default: ''.
 * @param $colTpl {stringCommaSeparated_chunkName|string|'null'} — The comma-separated list of templates for column rendering (`outputFormat` has to be == 'html'). Use inline templates starting with `@CODE:`. If the number of templates is lesser than the number of columns then the last passed template will be used to render the rest of the columns. 'null' specifies rendering without a template. Available placeholders: [+val+], [+rowNumber+] (index of current row, starts at 1), [+rowNumber.zeroBased+] (index of current row, starts at 0). Default: ''.
 * @param $outerTpl {stringChunkName|string} — Wrapper template (`outputFormat` has to be != 'array'). Use inline templates starting with `@CODE:`. Available placeholders: [+result+], [+total+] (total number of rows), [+resultTotal+] (total number of returned rows), [+rowY.colX+] (`Y` — row number, `X` — column number). Default: ''.
 * @param $placeholders {stirngJsonObject|stringQueryFormated} — Additional data as JSON (https://en.wikipedia.org/wiki/JSON) or Query string (https://en.wikipedia.org/wiki/Query_string) has to be passed into `outerTpl`, `rowTpl` and `colTpl`. E. g. `{"pladeholder1": "value1", "pagetitle": "My awesome pagetitle!"}` or `pladeholder1=value1&pagetitle=My awesome pagetitle!`. Arrays are supported too: `some[a]=one&some[b]=two` => `[+some.a+]`, `[+some.b+]`; `some[]=one&some[]=two` => `[+some.0+]`, `[some.1]`. Default: ''.
 * @param $urlencode {0|1} — Is it required to URL encode the result? `outputFormat` has to be != 'array'. URL encoding is used according to RFC 3986. Default: 0.
 * @param $totalRowsToPlaceholder {string} — The name of the global MODX placeholder that holds the total number of rows. The placeholder won't be set if `totalRowsToPlaceholder` is empty. Default: ''.
 * @param $resultToPlaceholder {string} — The name of the global MODX placeholder that holds the snippet result. The result will be returned in a regular manner if the parameter is empty. Default: ''.
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

$result = '';

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
		//Разбиваем по условию или
		$temp = explode(
			'||',
			$filter
		);
		
		$filter = [];
		
		//Перебираем по условию или
		foreach (
			$temp as 
			$orKey =>
			$orValue
		){
			//Разбиваем по условию и
			$and_array = explode(
				'&&',
				$orValue
			);
			//Перебираем по условию и
			foreach (
				$and_array as 
				$andKey =>
				$andValue
			){
				if (
					strpos(
						$andValue,
						'::'
					) === false
				){
					//Разбиваем по колонке/значению
					$value = explode(
						'<>',
						$andValue
					);
					//Добавляем вид сравнения для колонки
					$filter[$orKey][$andKey]['eqal'] = false;
				}else{
					//Разбиваем по колонке/значению
					$value = explode(
						'::',
						$andValue
					);
					//Добавляем вид сравнения для колонки
					$filter[$orKey][$andKey]['eqal'] = true;
				}
				//Добавляем правило для соответствующей колонки
				$filter[$orKey][$andKey]['value'][$value[0]] = $value[1];
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
		$rowNumber =>
		$row
	){
		if (!is_array($row)){
			$data[$rowNumber] =
				$inputString_colDelimiterIsRegexp ?
				preg_split(
					$inputString_colDelimiter,
					$row
				) :
				explode(
					$inputString_colDelimiter,
					$row
				)
			;
		}
		
		//Если необходимо получить какие-то конкретные значения
		if ($filter !== false){
			//Перебираем условия `or`
			foreach (
				$filter as
				$orKey =>
				$orValue
			){
				//Считаем, что вариант проходит, если не доказано обратное
				$isFound = true;
				//Перебираем условия `and`
				foreach (
					$orValue as
					$andKey =>
					$andValue
				){
					//Здесь не более одного значения! фактически получаем колонку и значение
					foreach (
						$andValue['value'] as 
						$colKey =>
						$colValue
					){
						//В зависимости от того, должно или нет значение в колонке быть равно фильтру, присваиваем флагу результат
						if ($andValue['eqal']){
							//Если должно быть равно
							$isFound = $data[$rowNumber][$colKey] == $colValue;
						}else{
							//Если не должно быть равно 
							$isFound = $data[$rowNumber][$colKey] != $colValue;
						}
					}
					
					//Если условие сменилось на ложь, значит переходим к следующему условию `or`
					if (!$isFound){
						break;
					}
				}
				
				//если все условия `and` прошли проверку, выходим из цикла `or`
				if ($isFound){
					break;
				}
			}
			
			//если на выходе из цикла мы видим, что ни одно из условий не выполнено, сносим строку нафиг
			if (!$isFound){
				unset($data[$rowNumber]);
			}
		}
		
		//Если нужно получить какую-то конкретную колонку
		if (
			$columns != 'all' &&
			//Также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее
			isset($data[$rowNumber])
		){
			//Выбираем только необходимые колонки + Сбрасываем ключи массива
			$data[$rowNumber] = array_values(array_intersect_key(
				$data[$rowNumber],
				$columns
			));
		}
		
		//Если нужно удалять пустые строки
		if (
			$removeEmptyRows &&
			//Также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее
			isset($data[$rowNumber])
		){
			//Если строка пустая, удаляем
			if (
				strlen(implode(
					'',
					$data[$rowNumber]
				)) == 0
			){
				unset($data[$rowNumber]);
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
				$rowNumber =>
				$row
			){
				//Перебираем колонки, заданные для типографирования
				foreach (
					$typography as
					$v
				){
					//Если такая колонка существует, типографируем
					if (isset($data[$rowNumber][$v])){
						$data[$rowNumber][$v] = $modx->runSnippet(
							'ddTypograph',
							[
								'text' => $data[$rowNumber][$v]
							]
						);
					}
				}
			}
		}
		
		//Если вывод в массив
		if ($outputFormat == 'array'){
			$result = $data;
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
						$rowNumber =>
						$row
					){
						$resTemp[$rowNumber] = [
							//Запишем номер строки
							'rowNumber.zeroBased' => $rowNumber,
							'rowNumber' => $rowNumber + 1,
							//И общее количество элементов
							'total' => $total,
							'resultTotal' => $resultTotal
						];
						
						//Перебираем колонки
						foreach (
							$row as
							$columnNumber =>
							$column
						){
							//Если нужно удалять пустые значения
							if (
								$removeEmptyCols &&
								!strlen($column)
							){
								$resTemp[$rowNumber]['col' . $columnNumber] = '';
							}else{
								//Если есть шаблоны значений колонок
								if (
									$colTpl !== false &&
									strlen($colTpl[$columnNumber]) > 0
								){
									$resTemp[$rowNumber]['col'.$columnNumber] = $modx->parseText(
										$colTpl[$columnNumber],
										array_merge(
											[
												'val' => $column,
												'rowNumber.zeroBased' => $resTemp[$rowNumber]['rowNumber.zeroBased'],
												'rowNumber' => $resTemp[$rowNumber]['rowNumber']
											],
											$placeholders
										)
									);
								}else{
									$resTemp[$rowNumber]['col' . $columnNumber] = $column;
								}
							}
						}
						
						$resTemp[$rowNumber] = $modx->parseText(
							$rowTpl,
							array_merge(
								$resTemp[$rowNumber],
								$placeholders
							)
						);
					}
				}else{
					foreach (
						$data as
						$rowNumber =>
						$row
					){
						//Если есть шаблоны значений колонок
						if ($colTpl !== false){
							foreach (
								$row as
								$columnNumber =>
								$column
							){
								if (
									$removeEmptyCols &&
									!strlen($column)
								){
									unset($row[$columnNumber]);
								}else if (strlen($colTpl[$columnNumber]) > 0){
									$row[$columnNumber] = $modx->parseText(
										$colTpl[$columnNumber],
										array_merge(
											[
												'val' => $column,
												'rowNumber.zeroBased' => $rowNumber,
												'rowNumber' => $rowNumber + 1
											],
											$placeholders
										)
									);
								}
							}
						}
						$resTemp[$rowNumber] = implode(
							$colGlue,
							$row
						);
					}
				}
				
				if ($outputFormat == 'html'){
					$result = implode(
						$rowGlue,
						$resTemp
					);
				}else{
					$result = $resTemp;
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
					$result = json_encode($resTemp[$startRow]);
				}else{
					$result = json_encode($resTemp);
				}
				
				//Это чтобы MODX не воспринимал как вызов сниппета
				$result = strtr(
					$result,
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
				$resTemp['result'] = $result;
				
				//Преобразуем результат в одномерный массив
				$data = \ddTools::unfoldArray($data);
				
				//Добавляем 'row' и 'val' к ключам
				foreach (
					$data as
					$rowNumber =>
					$row
				){
					 $resTemp[preg_replace(
					 	'/(\d)\.(\d)/',
					 	'row$1.col$2',
					 	$rowNumber
					 )] = $row;
				}
				
				$resTemp = array_merge(
					$resTemp,
					$placeholders
				);
				
				$resTemp['total'] = $total;
				$resTemp['resultTotal'] = $resultTotal;
				
				$result = $modx->parseText(
					$outerTpl,
					$resTemp
				);
			}
			
			//Если нужно URL-кодировать строку
			if ($urlencode){
				$result = rawurlencode($result);
			}
		}
	}
}

//Если надо, выводим в плэйсхолдер
if (isset($resultToPlaceholder)){
	$modx->setPlaceholder(
		$resultToPlaceholder,
		$result
	);
	
	$result = '';
}

return $result;
?>