<?php
/**
 * ddGetMultipleField.php
 * @version 3.2 (2015-06-23)
 * 
 * @desc A snippet for separated by delimiters data output.
 * @note The fields formed by the mm_ddMultipleFields widget values ooutput gets more convinient with the snippet.
 * 
 * @uses The library modx.ddTools 0.11.
 * @uses The snippet ddTypograph 1.4.3 (if typographing is required).
 * 
 * @param $string {separated string} - The input string containing separated values. @required
 * @param $docField {string} - The name of the document field/TV which value is required to get. If the parameter is passed then the input string will be taken from the field/TV and “string” will be ignored. Default: —.
 * @param $docId {integer} - ID of the document which field/TV value is required to get. “docId” equals the current document id since “docId” is unset. Default: —.
 * @param $rowDelimiter {string; regexp} - The input string row delimiter. Default: '||'.
 * @param $colDelimiter {string; regexp} - The input string column delimiter. Default: '::'.
 * @param $startRow {integer} - The index of the initial row (indexes start at 0). Default: 0.
 * @param $totalRows {integer; 'all'} - The maximum number of rows to return. All rows will be returned if “totalRows” == 'all'. Default: 'all'.
 * @param $columns {comma separated string; 'all'} - The indexes of columns to return (indexes start at 0). All columns will be returned if “columns” == 'all'. Default: 'all'.
 * @param $filter {separated string} - Filter clause for columns. Thus, '0::a||0::b||1::1' makes the columns with either 'a' or 'b' in the 0 column and with 1 in the 1 column to be returned. Default: ''.
 * @param $removeEmptyRows {0; 1} - Is it required to remove empty rows? Default: 1.
 * @param $removeEmptyCols {0; 1} - Is it required to remove empty columns? Default: 1.
 * @param $sortBy {comma separated string} - The index of the column to sort by (indexes start at 0). The parameter also takes comma-separated values for multiple sort, e.g. '0,1'. Default: '0'.
 * @param $sortDir {'ASC'; 'DESC'; 'RAND'; 'REVERSE'; ''} - Rows sorting direction. The rows will be returned in reversed order if “sortDir” == 'REVERSE'. Default: ''.
 * @param $typography {comma separated string} - The comma separated indexes of the columns which values have to be corrected (indexes start at 0). If unset, there will be no correction. Default: —.
 * @param $outputFormat {'html'; 'JSON'; 'array'; 'htmlarray'} - Result output format. Default: 'html'.
 * @param $rowGlue {string} - The string that combines rows while rendering. It can be used along with “rowTpl”. Default: ''.
 * @param $colGlue {string} - The string that combines columns while rendering. It can be used along with “colTpl”, but not with “rowTpl” for obvious reasons. Default: ''.
 * @param $rowTpl {string: chunkName} - The template for row rendering (“outputFormat” has to be == 'html'). Available placeholders: [+rowNumber+] (index of current row, starts at 1), [+rowNumber.zeroBased+] (index of current row, starts at 0), [+total+] (total number of rows), [+resultTotal+] (total number of returned rows), [+col0+],[+col1+],… (column values). Default: ''.
 * @param $colTpl {comma separated string: chunkName; 'null'} - The comma-separated list of templates for column rendering (“outputFormat” has to be == 'html'). If the number of templates is lesser than the number of columns then the last passed template will be used to render the rest of the columns. 'null' specifies rendering without a template. Available placeholders: [+val+], [+rowNumber+] (index of current row, starts at 1), [+rowNumber.zeroBased+] (index of current row, starts at 0). Default: ''.
 * @param $outerTpl {string: chunkName} - Wrapper template (“outputFormat” has to be != 'array'). Available placeholders: [+result+], [+total+] (total number of rows), [+resultTotal+] (total number of returned rows), [+rowY.colX+] (“Y” — row number, “X” — column number). Default: ''.
 * @param $placeholders {separated string} - Additional data has to be passed into “outerTpl”. Syntax: string separated with '::' between key and value and '||' between key-value pairs. Default: ''.
 * @param $urlencode {0; 1} - Is it required to URL encode the result? “outputFormat” has to be != 'array'. URL encoding is used according to RFC 3986. Default: 0.
 * @param $totalRowsToPlaceholder {string} - The name of the global MODX placeholder that holds the total number of rows. The placeholder won't be set if “totalRowsToPlaceholder” is empty. Default: ''.
 * @param $resultToPlaceholder {string} - The name of the global MODX placeholder that holds the snippet result. The result will be returned in a regular manner if the parameter is empty. Default: ''.
 * 
 * @link http://code.divandesign.biz/modx/ddgetmultiplefield/3.2
 * 
 * @copyright 2015, DivanDesign
 * http://www.DivanDesign.biz
 */

//Подключаем modx.ddTools
require_once $modx->config['base_path'].'assets/snippets/ddTools/modx.ddtools.class.php';

//Если задано имя поля, которое необходимо получить
if (isset($docField)){
	$string = ddTools::getTemplateVarOutput(array($docField), $docId);
	$string = $string[$docField];
}

//Если задано значение поля
if (isset($string) && strlen($string) > 0){
	if (!isset($rowDelimiter)){$rowDelimiter = '||';}
	if (!isset($colDelimiter)){$colDelimiter = '::';}
	
	//Являются ли разделители регулярками
	$rowDelimiterIsRegexp = (filter_var($rowDelimiter, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^\/.*\/[a-z]*$/'))) !== false) ? true : false;
	$colDelimiterIsRegexp = (filter_var($colDelimiter, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^\/.*\/[a-z]*$/'))) !== false) ? true : false;
	
	//Если заданы условия фильтрации
	if (isset($filter)){
		//Разбиваем по условиям
		$temp = explode('||', $filter);
		
		$filter = array();
		
		foreach ($temp as $value){
			//Разбиваем по колонке/значению
			$value = explode('::', $value);
			
			//Если указали просто значение (значит, это нулевая колонка) TODO: Удалить через пару версий.
			if (count($value) < 2){
				$value[1] = $value[0];
				$value[0] = '0';
			}
			
			//Если ни одно правило для этой колонки ещй не задано
			if (!isset($filter[$value[0]])){
				$filter[$value[0]] = array();
			}
			
			//Добавляем правило для соответствующей колонки
			$filter[$value[0]][] = $value[1];
		}
	}else{
		$filter = false;
	}
	
	$columns = isset($columns) ? explode(',', $columns) : 'all';
	//Хитро-мудро для array_intersect_key
	if (is_array($columns)){$columns = array_combine($columns, $columns);}
	if (!isset($rowGlue)){$rowGlue = '';}
	if (!isset($colGlue)){$colGlue = '';}
	$removeEmptyRows = (isset($removeEmptyRows) && $removeEmptyRows == '0') ? false : true;
	$removeEmptyCols = (isset($removeEmptyCols) && $removeEmptyCols == '0') ? false : true;
	$urlencode = (isset($urlencode) && $urlencode == '1') ? true : false;
	$outputFormat = isset($outputFormat) ? strtolower($outputFormat) : 'html';
	
	//Разбиваем на строки
	$data = $rowDelimiterIsRegexp ? preg_split($rowDelimiter, $string) : explode($rowDelimiter, $string);
	
	//Общее количество строк
	$total = count($data);
	
	//Перебираем строки, разбиваем на колонки
	foreach ($data as $rowNumber => $row){
		$data[$rowNumber] = $colDelimiterIsRegexp ? preg_split($colDelimiter, $row) : explode($colDelimiter, $row);
		
		//Если необходимо получить какие-то конкретные значения
		if ($filter !== false){
			//Перебираем колонки для фильтрации
			foreach ($filter as $columnNumber => $value){
				//Если текущего значения в списке нет, сносим нафиг
				if (!in_array($data[$rowNumber][$columnNumber], $value)){
					unset($data[$rowNumber]);
					//Уходим (строку уже снесли, больше ничего не важно)
					break;
				}
			}
		}
		
		//Если нужно получить какую-то конкретную колонку (также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее)
		if ($columns != 'all' && isset($data[$rowNumber])){
			//Выбираем только необходимые колонки + Сбрасываем ключи массива
			$data[$rowNumber] = array_values(array_intersect_key($data[$rowNumber], $columns));
		}
		
		//Если нужно удалять пустые строки (также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее)
		if ($removeEmptyRows && isset($data[$rowNumber])){
			//Если строка пустая, удаляем
			if (strlen(implode('', $data[$rowNumber])) == 0){unset($data[$rowNumber]);}
		}
	}
	
	//Сбрасываем ключи массива (пригодится для выборки конкретного значения)
	$data = array_values($data);
	
	$result = '';
	
	//Если что-то есть (могло ничего не остаться после удаления пустых и/или получения по значениям)
	if (count($data) > 0){
		//Если надо сортировать
		if (isset($sortDir)){
			$sortDir = strtoupper($sortDir);
			
			if (!isset($sortBy)){$sortBy = '0';}
			
			//Если надо в случайном порядке - шафлим
			if ($sortDir == 'RAND'){
				shuffle($data);
			//Если надо просто в обратном порядке
			}else if ($sortDir == 'REVERSE'){
				$data = array_reverse($data);
			}else{
				//Сортируем результаты
				$data = ddTools::sort2dArray($data, explode(',', $sortBy), ($sortDir == 'ASC') ? 1 : -1);
			}
		}
		
		if (!isset($startRow) || !is_numeric($startRow)){$startRow = '0';}
		
		//Обрабатываем слишком большой индекс
		if (!isset($data[$startRow])){$startRow = count($data) - 1;}
		
		//Если общее количество элементов не задано или задано плохо, читаем, что нужны все
		if (!isset($totalRows) || !is_numeric($totalRows)){$totalRows = 'all';}
		
		//Если нужны все элементы
		if ($totalRows == 'all'){
			$data = array_slice($data, $startRow);
		}else{
			$data = array_slice($data, $startRow, $totalRows);
		}
		
		//Общее количество возвращаемых строк
		$resultTotal = count($data);
		
		//Плэйсхолдер с общим количеством
		if (isset($totalRowsToPlaceholder)){
			$modx->setPlaceholder($totalRowsToPlaceholder, $resultTotal);
		}
		
		//Если нужно типографировать
		if (isset($typography)){
			$typography = explode(',', $typography);
			
			//Придётся ещё раз перебрать результат
			foreach ($data as $rowNumber => $row){
				//Перебираем колонки, заданные для типографирования
				foreach ($typography as $v){
					//Если такая колонка существует, типографируем
					if (isset($data[$rowNumber][$v])){
						$data[$rowNumber][$v] = $modx->runSnippet('ddTypograph', array('text' => $data[$rowNumber][$v]));
					}
				}
			}
		}
		
		//Если вывод в массив
		if ($outputFormat == 'array'){
			$result = $data;
		}else{
			$resTemp = array();
			
			//Если вывод просто в формате html
			if ($outputFormat == 'html' || $outputFormat == 'htmlarray'){
				//Шаблоны колонок
				$colTpl = isset($colTpl) ? explode(',', $colTpl) : false;
				
				//Если шаблоны колонок заданы, но их не хватает
				if ($colTpl !== false){
					if (($temp = count($data[0]) - count($colTpl)) > 0){
						//Дозабьём недостающие последним
						$colTpl = array_merge($colTpl, array_fill($temp - 1, $temp, $colTpl[count($colTpl) - 1]));
					}
					
					$colTpl = str_replace('null', '', $colTpl);
				}
				
				//Если задан шаблон строки
				if (isset($rowTpl)){
					//Перебираем строки
					foreach ($data as $rowNumber => $row){
						$resTemp[$rowNumber] = array(
							//Запишем номер строки
							'rowNumber.zeroBased' => $rowNumber,
							'rowNumber' => $rowNumber + 1,
							//И общее количество элементов
							'total' => $total,
							'resultTotal' => $resultTotal
						);
						
						//Перебираем колонки
						foreach ($row as $columnNumber => $column){
							//Если нужно удалять пустые значения
							if ($removeEmptyCols && !strlen($column)){
								$resTemp[$rowNumber]['col'.$columnNumber] = '';
							}else{
								//Если есть шаблоны значений колонок
								if ($colTpl !== false && strlen($colTpl[$columnNumber]) > 0){
									$resTemp[$rowNumber]['col'.$columnNumber] = $modx->parseChunk($colTpl[$columnNumber], array(
										'val' => $column,
										'rowNumber.zeroBased' => $resTemp[$rowNumber]['rowNumber.zeroBased'],
										'rowNumber' => $resTemp[$rowNumber]['rowNumber']
									), '[+', '+]');
								}else{
									$resTemp[$rowNumber]['col'.$columnNumber] = $column;
								}
							}
						}
						
						$resTemp[$rowNumber] = $modx->parseChunk($rowTpl, $resTemp[$rowNumber], '[+', '+]');
					}
				}else{
					foreach ($data as $rowNumber => $row){
						//Если есть шаблоны значений колонок
						if ($colTpl !== false){
							foreach ($row as $columnNumber => $column){
								if ($removeEmptyCols && !strlen($column)){
									unset($row[$columnNumber]);
								}else if (strlen($colTpl[$columnNumber]) > 0){
									$row[$columnNumber] = $modx->parseChunk($colTpl[$columnNumber], array(
										'val' => $column,
										'rowNumber.zeroBased' => $rowNumber,
										'rowNumber' => $rowNumber + 1
									), '[+', '+]');
								}
							}
						}
						$resTemp[$rowNumber] = implode($colGlue, $row);
					}
				}
				
				if ($outputFormat == 'html'){
					$result = implode($rowGlue, $resTemp);
				}else{
					$result = $resTemp;
				}
			//Если вывод в формате JSON
			}else if ($outputFormat == 'json'){
				$resTemp = $data;
				
				//Если нужно выводить только одну колонку
				if ($columns != 'all' && count($columns) == 1){
					$resTemp = array_map('implode', $resTemp);
				}
				
				//Если нужно получить какой-то конкретный элемент, а не все
				if ($totalRows == '1'){
					$result = json_encode($resTemp[$startRow]);
				}else{
					$result = json_encode($resTemp);
				}
				
				//Это чтобы модекс не воспринимал как вызов сниппета
				$result = strtr($result, array('[[' => '[ [', ']]' => '] ]'));
			}
			
			//Если оборачивающий шаблон задан (и вывод не в массив), парсим его
			if (isset($outerTpl)){
				$resTemp = array();
				
				//Элемент массива 'result' должен находиться самым первым, иначе дополнительные переданные плэйсхолдеры в тексте не найдутся! 
				$resTemp['result'] = $result;
				
				//Преобразуем результат в одномерный массив
				$data = ddTools::unfoldArray($data);
				
				//Добавляем 'row' и 'val' к ключам
				foreach ($data as $rowNumber => $row){
					 $resTemp[preg_replace('/(\d)\.(\d)/', 'row$1.col$2', $rowNumber)] = $row;
				}
				
				//Если есть дополнительные данные
				if (isset($placeholders)){
					$resTemp = array_merge($resTemp, ddTools::explodeAssoc($placeholders));
				}
				
				$resTemp['total'] = $total;
				$resTemp['resultTotal'] = $resultTotal;
				$result = $modx->parseChunk($outerTpl, $resTemp, '[+','+]');
			}
			
			//Если нужно URL-кодировать строку
			if ($urlencode){
				$result = rawurlencode($result);
			}
		}
	}
	
	//Если надо, выводим в плэйсхолдер
	if (isset($resultToPlaceholder)){
		$modx->setPlaceholder($resultToPlaceholder, $result);
	}else{
		return $result;
	}
}
// выдать пустой результат, еслы ранее не было завершения сниппета.
return '';
?>