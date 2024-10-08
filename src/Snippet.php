<?php
namespace ddGetMultipleField;

class Snippet extends \DDTools\Snippet {
	protected
		$version = '3.10.0',
		
		$params = [
			// Defaults
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
			'colTpl' => [],
			'outerTpl' => null,
			'placeholders' => [],
			'urlencode' => false,
			'totalRowsToPlaceholder' => null,
			'resultToPlaceholder' => null,
		],
		
		$paramsTypes = [
			'removeEmptyRows' => 'boolean',
			'removeEmptyCols' => 'boolean',
			'urlencode' => 'boolean',
			'startRow' => 'integer',
			'placeholders' => 'objectArray',
		],
		
		$renamedParamsCompliance = [
			'inputString' => 'string',
			'inputString_docField' => 'docField',
			'inputString_docId' => 'docId',
			'inputString_rowDelimiter' => 'rowDelimiter',
			'inputString_colDelimiter' => 'colDelimiter',
		]
	;
	
	/**
	 * prepareParams
	 * @version 1.1.5 (2024-09-06)
	 * 
	 * @param $params {stdClass|arrayAssociative|stringJsonObject|stringHjsonObject|stringQueryFormatted}
	 * 
	 * @return {void}
	 */
	protected function prepareParams($params = []){
		// Call base method
		parent::prepareParams($params);
		
		// Comma separated string
		foreach (
			[
				'sortBy',
				'typography',
				'colTpl'
			]
			as $paramName
		){
			if (
				// Only strings can be exploded
				!is_array($this->params->{$paramName})
				&& (
					// Zero indexes for `sortBy` and `typography` must be used
					$this->params->{$paramName} === '0'
					|| !empty($this->params->{$paramName})
				)
			){
				$this->params->{$paramName} = explode(
					',',
					$this->params->{$paramName}
				);
			}
		}
		
		if (!is_numeric($this->params->totalRows)){
			$this->params->totalRows = 'all';
		}
		
		if (
			$this->params->columns !== 'all'
			&& !is_array($this->params->columns)
		){
			$this->params->columns = explode(
				',',
				$this->params->columns
			);
		}
		
		// Хитро-мудро для array_intersect_key
		if (is_array($this->params->columns)){
			$this->params->columns = array_combine(
				$this->params->columns,
				$this->params->columns
			);
		}
		
		if (!empty($this->params->sortDir)){
			$this->params->sortDir = strtoupper($this->params->sortDir);
		}
		
		$this->params->outputFormat = strtolower($this->params->outputFormat);
		
		// Prepare templates
		foreach (
			[
				'rowTpl',
				'outerTpl'
			]
			as $paramName
		){
			// Chunk content or inline template
			$this->params->{$paramName} = \ddTools::getTpl($this->params->{$paramName});
		}
		
		if (empty($this->params->colTpl)){
			// Without templates by default
			$this->params->colTpl = [''];
		}else{
			// Получим содержимое шаблонов
			foreach (
				$this->params->colTpl
				as $colTpl_itemNumber
				=> $colTpl_itemValue
			){
				// Chunk content or inline template
				$this->params->colTpl[$colTpl_itemNumber] = \ddTools::getTpl($this->params->colTpl[$colTpl_itemNumber]);
			}
			
			$this->params->colTpl = str_replace(
				'null',
				'',
				$this->params->colTpl
			);
		}
		
		// Unfold for arrays support (e. g. `{"somePlaceholder1": "test", "somePlaceholder2": {"a": "one", "b": "two"} }` => `[+somePlaceholder1+]`, `[+somePlaceholder2.a+]`, `[+somePlaceholder2.b+]`; `{"somePlaceholder1": "test", "somePlaceholder2": ["one", "two"] }` => `[+somePlaceholder1+]`, `[+somePlaceholder2.0+]`, `[somePlaceholder2.1]`)
		$this->params->placeholders = \ddTools::unfoldArray($this->params->placeholders);
		
		// Если задано имя поля, которое необходимо получить
		if (!empty($this->params->inputString_docField)){
			$this->params->inputString = \ddTools::getTemplateVarOutput(
				[$this->params->inputString_docField],
				$this->params->inputString_docId
			);
			
			$this->params->inputString = $this->params->inputString[$this->params->inputString_docField];
		}
		
		// Если заданы условия фильтрации
		if (!empty($this->params->filter)){
			// Backward compatibility
			$this->params->filter = str_replace(
				[
					'::',
					'<>',
				],
				[
					'==',
					'!=',
				],
				$this->params->filter
			);
			
			// Разбиваем по условию «или»
			$filterSource = explode(
				'||',
				$this->params->filter
			);
			
			// Clear
			$this->params->filter = [];
			
			// Перебираем по условию «или»
			foreach (
				$filterSource
				as $orIndex
				=> $orCondition
			){
				$this->params->filter[$orIndex] = [];
				
				// Перебираем по условию «и»
				foreach (
					// Разбиваем по условию «и»
					explode(
						'&&',
						$orCondition
					)
					as $andIndex
					=> $andCondition
				){
					// Добавляем вид сравнения для колонки
					$this->params->filter[$orIndex][$andIndex] = [
						'isEqual' =>
							strpos(
								$andCondition,
								'=='
							)
							!== false
						,
						'columnKey' => '',
						'columnValue' => '',
					];
					
					// Разбиваем по колонке/значению
					$andCondition = explode(
						(
							$this->params->filter[$orIndex][$andIndex]['isEqual']
							? '=='
							: '!='
						),
						$andCondition
					);
					
					// Добавляем правило для соответствующей колонки
					$this->params->filter[$orIndex][$andIndex]['columnKey'] = trim($andCondition[0]);
					$this->params->filter[$orIndex][$andIndex]['columnValue'] = trim(
						$andCondition[1],
						// Trim whitespaces and quotes
						" \t\n\r\0\x0B\"'"
					);
				}
			}
		}
	}
	
	/**
	 * run
	 * @version 1.9.1 (2024-09-06)
	 * 
	 * @return {string}
	 */
	public function run(){
		// The snippet must return an empty string even if result is absent
		$result = '';
		
		// Если задано значение поля
		if (strlen($this->params->inputString) > 0){
			// Являются ли разделители регулярками
			$inputString_rowDelimiterIsRegexp =
				(
					filter_var(
						$this->params->inputString_rowDelimiter,
						FILTER_VALIDATE_REGEXP,
						[
							'options' => [
								'regexp' => '/^\/.*\/[a-z]*$/',
							],
						]
					)
					!== false
				)
				? true
				: false
			;
			
			$inputString_colDelimiterIsRegexp =
				(
					filter_var(
						$this->params->inputString_colDelimiter,
						FILTER_VALIDATE_REGEXP,
						[
							'options' => [
								'regexp' => '/^\/.*\/[a-z]*$/',
							],
						]
					)
					!== false
				)
				? true
				: false
			;
			
			// JSON (first letter is “{” or “[”)
			if (
				in_array(
					substr(
						ltrim($this->params->inputString),
						0,
						1
					),
					[
						'{',
						'[',
					]
				)
			){
				$data = \DDTools\ObjectTools::convertType([
					'object' => $this->params->inputString,
					'type' => 'objectArray',
				]);
			}
			
			// Not JSON
			if (empty($data)){
				// Разбиваем на строки
				$data =
					$inputString_rowDelimiterIsRegexp
					? preg_split(
						$this->params->inputString_rowDelimiter,
						$this->params->inputString
					)
					: explode(
						$this->params->inputString_rowDelimiter,
						$this->params->inputString
					)
				;
			}
			
			// Convert data to array for code simplification
			$data = (array) $data;
			
			// Общее количество строк
			$total = count($data);
			
			// Перебираем строки, разбиваем на колонки
			foreach (
				$data
				as $rowKey
				=> $rowValue
			){
				if (!is_array($rowValue)){
					$data[$rowKey] =
						$inputString_colDelimiterIsRegexp
						? preg_split(
							$this->params->inputString_colDelimiter,
							$rowValue
						)
						: explode(
							$this->params->inputString_colDelimiter,
							$rowValue
						)
					;
				}
				
				// Если необходимо получить какие-то конкретные значения
				if (!empty($this->params->filter)){
					// Перебираем условия `or`
					foreach (
						$this->params->filter
						as $orIndex
						=> $orCondition
					){
						// Считаем, что вариант проходит, если не доказано обратное
						$isFound = true;
						
						// Перебираем условия `and`
						foreach (
							$orCondition
							as $andIndex
							=> $andCondition
						){
							// В зависимости от того, должно или нет значение в колонке быть равно фильтру, присваиваем флагу результат
							if ($andCondition['isEqual']){
								// Если должно быть равно
								$isFound = $data[$rowKey][$andCondition['columnKey']] == $andCondition['columnValue'];
							}else{
								// Если не должно быть равно
								$isFound = $data[$rowKey][$andCondition['columnKey']] != $andCondition['columnValue'];
							}
							
							// Если условие сменилось на ложь, значит переходим к следующему условию `or`
							if (!$isFound){
								break;
							}
						}
						
						// Если все условия `and` прошли проверку, выходим из цикла `or`
						if ($isFound){
							break;
						}
					}
					
					// Если на выходе из цикла мы видим, что ни одно из условий не выполнено, сносим строку нафиг
					if (!$isFound){
						unset($data[$rowKey]);
					}
				}
				
				// Если нужно получить какую-то конкретную колонку
				if (
					$this->params->columns != 'all'
					// Также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее
					&& isset($data[$rowKey])
				){
					// Выбираем только необходимые колонки + Сбрасываем ключи массива
					$data[$rowKey] = array_values(array_intersect_key(
						$data[$rowKey],
						$this->params->columns
					));
				}
				
				// Если нужно удалять пустые строки
				if (
					$this->params->removeEmptyRows
					// Также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее
					&& isset($data[$rowKey])
					// Если строка пустая
					&& strlen(
						implode(
							'',
							$data[$rowKey]
						)
					) == 0
				){
					unset($data[$rowKey]);
				}
			}
			
			// Если что-то есть (могло ничего не остаться после удаления пустых и/или получения по значениям)
			if (count($data) > 0){
				// Если надо сортировать
				if (!empty($this->params->sortDir)){
					// Если надо в случайном порядке - шафлим
					if ($this->params->sortDir == 'RAND'){
						// Shuffle array preserve keys
						uksort(
							$data,
							function(){
								return rand(
									-1,
									1
								);
							}
						);
					// Если надо просто в обратном порядке
					}elseif ($this->params->sortDir == 'REVERSE'){
						$data = array_reverse($data);
					}else{
						// Сортируем результаты
						$data = \ddTools::sort2dArray(
							$data,
							$this->params->sortBy,
							(
								$this->params->sortDir == 'ASC'
								? 1
								: -1
							)
						);
					}
				}
				
				// Обрабатываем слишком большой индекс
				if ($this->params->startRow > count($data) - 1){
					$this->params->startRow = count($data) - 1;
				}
				
				// Если нужны все элементы
				if ($this->params->totalRows == 'all'){
					$data = array_slice(
						$data,
						$this->params->startRow,
						null,
						// preserve keys
						true
					);
				}else{
					$data = array_slice(
						$data,
						$this->params->startRow,
						$this->params->totalRows,
						// preserve keys
						true
					);
				}
				
				// Общее количество возвращаемых строк
				$resultTotal = count($data);
				
				// Плэйсхолдер с общим количеством
				if (!empty($this->params->totalRowsToPlaceholder)){
					\ddTools::$modx->setPlaceholder(
						$this->params->totalRowsToPlaceholder,
						$resultTotal
					);
				}
				
				// Если нужно типографировать
				if (!empty($this->params->typography)){
					// Придётся ещё раз перебрать результат
					foreach (
						$data
						as $rowKey
						=> $rowValue
					){
						// Перебираем колонки, заданные для типографирования
						foreach (
							$this->params->typography
							as $columnKey
						){
							// Если такая колонка существует, типографируем
							if (isset($data[$rowKey][$columnKey])){
								$data[$rowKey][$columnKey] = \DDTools\Snippet::runSnippet([
									'name' => 'ddTypograph',
									'params' => [
										'text' => $data[$rowKey][$columnKey],
									],
								]);
							}
						}
					}
				}
				
				// Если вывод в массив
				if ($this->params->outputFormat == 'array'){
					$result = $data;
				}else{
					$resTemp = [];
					
					$placeholdersGeneral = \DDTools\ObjectTools::extend([
						'objects' => [
							[
								// Количество элементов
								'total' => $total,
								'resultTotal' => $resultTotal,
							],
							// User's placeholders can overwrite original data if needed, so they must be placed at the end
							$this->params->placeholders,
						],
					]);
					
					// Если вывод просто в формате html
					if (
						$this->params->outputFormat == 'html'
						|| $this->params->outputFormat == 'htmlarray'
					){
						$rowIndex = 0;
						
						// Перебираем строки
						foreach (
							$data
							as $rowKey
							=> $rowValue
						){
							$rowPlaceholders = \DDTools\ObjectTools::extend([
								'objects' => [
									// Row placeholders
									[
										// Запишем номер строки
										'rowNumber.zeroBased' => $rowIndex,
										'rowNumber' => $rowIndex + 1,
										'rowKey' => $rowKey,
									],
									// User's placeholders can overwrite original data if needed, so they must be placed at the end
									$placeholdersGeneral,
								],
							]);
							
							$rowData = (object) [
								'allColumnValues' => [],
								'allColumnValuesObjectJson' => new \stdClass(),
							];
							
							$columnIndex = 0;
							
							// Перебираем колонки
							foreach (
								$rowValue
								as $columnKey
								=> $columnValue
							){
								// If the column is used
								if (
									!empty($columnValue)
									|| !$this->params->removeEmptyCols
								){
									// If template for this column is not set
									if (!isset($this->params->colTpl[$columnIndex])){
										// Use previous
										$this->params->colTpl[$columnIndex] = $this->params->colTpl[$columnIndex - 1];
									}
									
									// If template for the column is needed
									if (!empty($this->params->colTpl[$columnIndex])){
										$columnValue = \ddTools::parseText([
											'text' => $this->params->colTpl[$columnIndex],
											'data' => \DDTools\ObjectTools::extend([
												'objects' => [
													[
														'val' => $columnValue,
														'columnIndex' => $columnIndex,
														'columnKey' => $columnKey,
													],
													// User's placeholders can overwrite original data if needed, so they must be placed at the end
													$rowPlaceholders,
												],
											]),
											'isCompletelyParsingEnabled' => false,
										]);
									}
									
									// Save for implode later by $this->params->colGlue
									$rowData->allColumnValues[] =
										! is_array($columnValue)
										? $columnValue
										: \DDTools\ObjectTools::convertType([
											'object' => $columnValue,
											'type' => 'stringJsonAuto',
										])
									;
								}
								
								// Save column value by index
								$rowData->{'col' . $columnIndex} = $columnValue;
								// And by original column key
								$rowData->{$columnKey} = $columnValue;
								// Row JSON
								$rowData->allColumnValuesObjectJson->{$columnKey} = $columnValue;
								
								$columnIndex++;
							}
							
							$rowData->allColumnValues = implode(
								$this->params->colGlue,
								$rowData->allColumnValues
							);
							
							if (!empty($this->params->rowTpl)){
								$resTemp[$rowKey] = \ddTools::parseText([
									'text' => $this->params->rowTpl,
									'data' => \DDTools\ObjectTools::extend([
										'objects' => [
											$rowData,
											// User's placeholders can overwrite original data if needed, so they must be placed at the end
											$rowPlaceholders,
										],
									]),
								]);
							}else{
								$resTemp[$rowKey] = $rowData->allColumnValues;
							}
							
							$rowIndex++;
						}
						
						if ($this->params->outputFormat == 'html'){
							$result = implode(
								$this->params->rowGlue,
								$resTemp
							);
						}else{
							$result = $resTemp;
						}
					// Если вывод в формате JSON
					}elseif ($this->params->outputFormat == 'json'){
						$resTemp = $data;
						
						// Если нужно выводить только одну колонку
						if (
							$this->params->columns != 'all'
							&& count($this->params->columns) == 1
						){
							$resTemp = array_map(
								'implode',
								$resTemp
							);
						}
						
						// Если нужно получить какой-то конкретный элемент, а не все
						if ($this->params->totalRows == '1'){
							$result = json_encode($resTemp[$this->params->startRow]);
						}else{
							$result = json_encode($resTemp);
						}
						
						// Это чтобы MODX не воспринимал как вызов сниппета
						$result = strtr(
							$result,
							[
								'[[' => '[ [',
								']]' => '] ]',
							]
						);
					}
					
					// Если оборачивающий шаблон задан (и вывод не в массив), парсим его
					if (!empty($this->params->outerTpl)){
						$resTemp = [];
						
						// Элемент массива 'result' должен находиться самым первым, иначе дополнительные переданные плэйсхолдеры в тексте не найдутся!
						$resTemp['result'] = $result;
						
						// Преобразуем результат в одномерный массив
						$data = \ddTools::unfoldArray($data);
						
						$rowIndex = 0;
						
						// Добавляем 'row' и 'val' к ключам
						foreach (
							$data
							as $rowKey
							=> $rowValue
						){
							$rowKeyNew = preg_replace(
								'/(.+?)\.(.+?)/',
								'row$1.col$2',
								$rowKey
							);
							$rowKeyNewIndexed = preg_replace(
								'/(.+?)\.(.+?)/',
								'row' . $rowIndex . '.col$2',
								$rowKey
							);
							
							$resTemp[$rowKeyNew] = $rowValue;
							
							if ($rowKeyNewIndexed != $rowKeyNew){
								$resTemp[$rowKeyNewIndexed] = $rowValue;
							}
							
							$rowIndex++;
						}
						
						$resTemp = \DDTools\ObjectTools::extend([
							'objects' => [
								$resTemp,
								// User's placeholders can overwrite original data if needed, so they must be placed at the end
								$placeholdersGeneral,
							],
						]);
						
						$result = \ddTools::parseText([
							'text' => $this->params->outerTpl,
							'data' => $resTemp,
						]);
					}
					
					// Если нужно URL-кодировать строку
					if ($this->params->urlencode){
						$result = rawurlencode($result);
					}
				}
			}
		}
		
		// Если надо, выводим в плэйсхолдер
		if (!empty($this->params->resultToPlaceholder)){
			\ddTools::$modx->setPlaceholder(
				$this->params->resultToPlaceholder,
				$result
			);
			
			$result = '';
		}
		
		return $result;
	}
}