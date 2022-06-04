# (MODX)EvolutionCMS.snippets.ddGetMultipleField

Сниппет для обработки, изменения и произвольного вывода структурированных данных (JSON или разделённых через определённые разделители).
Удобно использовать для вывода значений полей документов, сформированных виджетом [mm_ddMultipleFields](https://code.divandesign.biz/modx/mm_ddmultiplefields).

Возможности:
* Получение необходимого поля документа / TV по его ID. Параметры `inputString_docField` и `inputString_docId`.
* Вывод необходимого количества значений по номерам строк и значениям. Параметры `startRow`, `totalRows` и `filter`.
* Вывод необходимых значений по номерам колонок. Параметр `columns`.
* Сортировка строк по значениям колонок перед выводом (`'ASC'`, `'DESC'`, `'RAND'`, `'REVERSE'`), в том числе множественная сортировка. Параметры `sortDir` и `sortBy`.
* Вывод значений через разделители строк и колонок. Параметры `rowGlue` и `colGlue`.
* Удаление пустых значений колонок и строк перед выводом. Параметры `removeEmptyRows` и `removeEmptyCols`.
* Типографирование значений перед выводом (используется сниппет ddTypograph). Параметр `typography`.
* URL-кодирование результата перед выводом. Параметр `urlencode`.
* Вывод результата в JSON. Параметр `outputFormat`.
* Вывод значений по шаблонам (чанкам) строк и колонок (в шаблонах строк и колонок также доступны плэйсхолдеры `[+rowNumber+]` и `[+rowNumber.zeroBased+]` с номерами строки). Параметры `rowTpl` и `colTpl`.
* Вывод результата выполнения в чанк `outerTpl` с передачей дополнительных данных через параметр `placeholders`.


## Использует

* PHP >= 5.6
* [(MODX)EvolutionCMS](https://github.com/evolution-cms/evolution) >= 1.1
* [(MODX)EvolutionCMS.libraries.ddTools](https://code.divandesign.biz/modx/ddtools) >= 0.50
* [(MODX)EvolutionCMS.snippets.ddTypograph](https://code.divandesign.biz/modx/ddtypograph) >= 2.5 (if typography is required)


## Установка


### Вручную


#### 1. Элементы → Сниппеты: Создайте новый сниппет со следующими параметрами

1. Название сниппета: `ddGetMultipleField`.
2. Описание: `<b>3.8</b> Сниппет для обработки, изменения и произвольного вывода структурированных данных (JSON или разделённых через определённые разделители).`.
3. Категория: `Core`.
4. Анализировать DocBlock: `no`.
5. Код сниппета (php): Вставьте содержимое файла `ddGetMultipleField_snippet.php` из архива.


#### 2. Элементы → Управление файлами

1. Создайте новую папку `assets/snippets/ddGetMultipleField/`.
2. Извлеките содержимое архива в неё (кроме файла `ddGetMultipleField_snippet.php`).


### Используя [(MODX)EvolutionCMS.libraries.ddInstaller](https://github.com/DivanDesign/EvolutionCMS.libraries.ddInstaller)

Просто вызовите следующий код в своих исходинках или модуле [Console](https://github.com/vanchelo/MODX-Evolution-Ajax-Console):

```php
//Подключение (MODX)EvolutionCMS.libraries.ddInstaller
require_once(
	$modx->getConfig('base_path') .
	'assets/libs/ddInstaller/require.php'
);

//Установка (MODX)EvolutionCMS.snippets.ddGetMultipleField
\DDInstaller::install([
	'url' => 'https://github.com/DivanDesign/EvolutionCMS.snippets.ddGetMultipleField',
	'type' => 'snippet'
]);
```

* Если `ddGetMultipleField` отсутствует на вашем сайте, `ddInstaller` просто установит его.
* Если `ddGetMultipleField` уже есть на вашем сайте, `ddInstaller` проверит его версию и обновит, если нужно. 


## Описание параметров

Из пары параметров `inputString` / `inputString_docField` необходимо передавать лишь один.

* `inputString`
	* Описание: Исходная строка, содержащая значение.  
		Также поддерживает JSON с любым уровнем вложенности.
	* Допустимые значения:
		* `stringJsonArray` — в виде [JSON](https://ru.wikipedia.org/wiki/JSON) массива
		* `stringJsonObject` — в виде [JSON](https://ru.wikipedia.org/wiki/JSON) объекта
		* `stringSeparated` — разделённая через `inputString_rowDelimiter` и `inputString_colDelimiter`
	* **Обязателен**
	
* `inputString_docField`
	* Описание: Имя поля документа / TV, содержащего значение.  
		В этом случае параметр `inputString` игнорируется, значение получается из поля документа.
	* Допустимые значения: `string`
	* Значение по умолчанию: —
	
* `inputString_docId`
	* Описание: ID документа, значение поля которого нужно получить.
	* Допустимые значения: `integer`
	* Значение по умолчанию: `$modx->documentIdentifier` (ID текущего документа)
	
* `inputString_rowDelimiter`
	* Описание: Разделитель между строками в исходной строке (когда `inputString` не JSON).
	* Допустимые значения:
		* `string`
		* `regexp`
	* Значение по умолчанию: `'||'`
	
* `inputString_colDelimiter`
	* Описание: Разделитель между колонками в исходной строке (когда `inputString` не JSON).
	* Допустимые значения:
		* `string`
		* `regexp`
	* Значение по умолчанию: `'::'`
	
* `startRow`
	* Описание: Номер строки, начиная с которой необходимо возвращать (строки нумеруются с `0`).
	* Допустимые значения: `integer`
	* Значение по умолчанию: `0`
	
* `totalRows`
	* Описание: Количество возвращаемых строк.
	* Допустимые значения:
		* `integer`
		* `'all'` — будут возвращены все имеющиеся строки
	* Значение по умолчанию: `'all'`
	
* `columns`
	* Описание: Номера колонк через запятую, которые нужно вернуть (колонки нумеруются с `0`).
	* Допустимые значения:
		* `stringCommaSeparated`
		* `array`
		* `'all'` — будут возвращены все колонки
	* Значение по умолчанию: `'all'`
	
* `filter`
	* Описание: Фильтр по значениям колонок.  
		* Например, при
			```
			0 == 'a' ||
			0 == 'b' &&
			1 == 'some' &&
			2 != ''
			```
			выведутся только строки, в которых:
			* Значение `0` колонки равно `'a'` **или**
			* Значение `0` колонки равно `'b'` **и**
			* Значение `1` колонки равно `'some'` **и**
			* Значение `2` колонки не равно `''`.
		* Значения в кавычках — опционально, можно и так:
			```
			0 == a ||
			0 == b &&
			1 == some &&
			2 != 
			```
		* Поддерживаются как одинарные кавычки, так и двойные:
			```
			0 == "a" ||
			0 == "b" &&
			1 == "some" &&
			2 != ""
			```
		* Пробелы и переносы строк не обязательны, можно и так: `0==a||0==b&&1==some&&2!= `.
	* Допустимые значения: `stringSeparated`
	* Значение по умолчанию: —
	
* `removeEmptyRows`
	* Описание: Удалять пустые строки?
	* Допустимые значения:
		* `0`
		* `1`
	* Значение по умолчанию: `1`
	
* `removeEmptyCols`
	* Описание: Удалять пустые колонки?
	* Допустимые значения:
		* `0`
		* `1`
	* Значение по умолчанию: `1`
	
* `sortBy`
	* Описание: Номер колонки, по которой необходимо сортировать (нумеруются с `0`).  
		Для множественной сортировки параметры указываются через запятую (например: `'0,1'`).
	* Допустимые значения:
		* `stringCommaSeparated`
		* `array`
	* Значение по умолчанию: `'0'`
	
* `sortDir`
	* Описание: Направление сортировки строк (регистр не имеет значения).
	* Допустимые значения:
		* `'ASC'` — по возрастанию
		* `'DESC'` — по убыванию
		* `'RAND'` — в случайном порядке
		* `'REVERSE'` — в обратном от исходного порядке
		* `''` — без сортировки (как передано)
	* Значение по умолчанию: `''`
	
* `typography`
	* Описание: Номера колонок через запятую, значения которых нужно типографировать (колонки нумеруются с `0`).  
		Если не задано, ничего не типографируется.
	* Допустимые значения:
		* `stringCommaSeparated`
		* `array`
	* Значение по умолчанию: —
	
* `outputFormat`
	* Описание: Формат, в котором возвращать результат (регистр не имеет значения).
	* Допустимые значения:
		* `'html'`
		* `'json'`
		* `'array'`
		* `'htmlarray'`
	* Значение по умолчанию: `'html'`
	
* `rowGlue`
	* Описание: Разделитель (объединитель) между строками при выводе.  
		Может использоваться совместно с шаблоном `rowTpl`.
	* Допустимые значения: `string`
	* Значение по умолчанию: `''`
	
* `colGlue`
	* Описание: Разделитель (объединитель) между колонками при выводе.    
		Может использоваться совместно с шаблоном `colTpl` и `rowTpl`.
	* Допустимые значения: `string`
	* Значение по умолчанию: `''`
	
* `rowTpl`
	* Описание: Шаблон для вывода строк (при `outputFormat` == `'html'`).  
		Доступные плейсхолдеры:
		* `[+rowNumber+]` — номер текущей строки, начинающийся с `1`
		* `[+rowNumber.zeroBased+]` — номер текущей строки, начинающийся с `0`
		* `[+rowKey+]` — ключ текущей строки, полезно для объектов или ассоциативных массивов в `inputString`, для индексировнных массивов плейсхолдер эквивалентен `[+rowNumber.zeroBased+]`
		* `[+total+]` — бщее количество строк
		* `[+resultTotal+]` — количество возвращаемых строк
		* `[+col0+]`, `[+col1+]` и т. п. — значения соответствующих колонок
		* `[+`_columnKey_`+]` — значения колонок, где _columnKey_ — оригинальный ключ колонки (см. примеры ниже)
		* `[+allColumnValues+]` — значения всех колонок, объединённые через `colGlue`
	* Допустимые значения:
		* `stringChunkName`
		* `string` — передавать код напрямую без чанка можно начиная значение с `@CODE:`
	* Значение по умолчанию: —
	
* `colTpl`
	* Описание: Список шаблонов для вывода колонок, через запятую (при `outputFormat` == `'html'`).  
		Если шаблонов меньше, чем колонок, для всех недостающих выставляется последний указанный шаблон.
	* Допустимые значения:
		* `stringCommaSeparated`
		* `array`
	* Значение по умолчанию: —
	
* `colTpl[$i]`
	* Описание: Шаблон для вывода колонки.  
		Доступные плейсхолдеры:
		* `[+val+]` — значение колонки
		* `[+columnIndex+]` — номер колонки, начинающийся с `0`
		* `[+columnKey+]` — ключ колонки, полезно для объектов или ассоциативных массивов в `inputString`, для индексировнных массивов плейсхолдер эквивалентен `[+columnIndex+]`
		* `[+rowNumber+]` — номер строки, начинающийся с `1`
		* `[+rowNumber.zeroBased+]` — номер строки, начинающийся с `0`
		* `[+rowKey+]` — ключ текущей строки, полезно для объектов или ассоциативных массивов в `inputString`, для индексировнных массивов плейсхолдер эквивалентен `[+rowNumber.zeroBased+]`
	* Допустимые значения:
		* `stringChunkName`
		* `string` — передавать код напрямую без чанка можно начиная значение с `@CODE:`
		* `'null'` — вывод без шаблона
	* Значение по умолчанию: —
	
* `outerTpl`
	* Описание: Шаблон внешней обёртки (при `outputFormat` != `'array'`).  
		Доступные плейсхолдеры:
		* `[+result+]` — результат сниппета
		* `[+total+]` — общее количество строк
		* `[+resultTotal+]` — количество возвращаемых строк
		* `[+rowY.colX+]` — значение (где `Y` — номер строки, `X` — номер колонки)
	* Допустимые значения:
		* `stringChunkName`
		* `string` — передавать код напрямую без чанка можно начиная значение с `@CODE:`
	* Значение по умолчанию: —
	
* `placeholders`
	* Описание:
		Дополнительные данные, которые будут переданы в шаблоны `outerTpl`, `rowTpl` и `colTpl`.  
		Вложенные объекты и массивы также поддерживаются:
		* `{"someOne": "1", "someTwo": "test" }` => `[+someOne+], [+someTwo+]`.
		* `{"some": {"a": "one", "b": "two"} }` => `[+some.a+]`, `[+some.b+]`.
		* `{"some": ["one", "two"] }` => `[+some.0+]`, `[+some.1+]`.
	* Допустимые значения:
		* `stringJsonObject` — в виде [JSON](https://ru.wikipedia.org/wiki/JSON)
		* `stringHjsonObject` — в виде [HJSON](https://hjson.github.io/)
		* `stringQueryFormated` — в виде [Query string](https://en.wikipedia.org/wiki/Query_string)
		* Также может быть задан, как нативный PHP объект или массив (например, для вызовов через `$modx->runSnippet`).
			* `arrayAssociative`
			* `object`
	* Значение по умолчанию: —
	
* `urlencode`
	* Описание: Надо URL-кодировать результирующую строку?  
		* При `outputFormat` != `'array'`.
		* Строка кодируется согласно RFC 3986.
	* Допустимые значения:
		* `0`
		* `1`
	* Значение по умолчанию: `0`
	
* `totalRowsToPlaceholder`
	* Описание: Имя внешнего плэйсхолдера (MODX)Evolution, в который нужно вывести общее количество строк.    
		Если параметр не задан — не выводится.
	* Допустимые значения: `string`
	* Значение по умолчанию: —
	
* `resultToPlaceholder`
	* Описание: Имя внешнего плэйсхолдера (MODX)Evolution, в который нужно сохранить результат работы сниппета вместо обычного вывода.  
		Если параметр не задан — сниппет просто возвращает реузльтат.
	* Допустимые значения: `string`
	* Значение по умолчанию: —


## Примеры


### Вывод изображений `images` с описаниями

Исходная строка (пусть находится в TV документа `images`):

```
assets/images/some_img1.jpg::Изображение 1||assets/images/some_img2.jpg::Изображение 2
```

Вызов сниппета в шаблоне документа:

```
[[ddGetMultipleField?
	&inputString=`[*images*]`
	&rowTpl=`images_item`
]]
```

Код чанка  `images_item`:

```html
[+col1+]:
<img src="[+col0+]" alt="[+col1+]" />
```

Вернёт:

```html
Изображение 1:
<img src="assets/images/some_img1.jpg" alt="Изображение 1" />
Изображение 2:
<img src="assets/images/some_img2.jpg" alt="Изображение 2" />
```


### Вывод изображений из JSON используя оригинальные ключи колонок в шаблоне строки

```
[[ddGetMultipleField?
	&inputString=`[
		{
			"src": "assets/images/some_img1.jpg",
			"alt": "Изображение 1"
		},
		{
			"src": "assets/images/some_img2.jpg",
			"alt": "Изображение 2"
		}
	]`
	&rowTpl=`@CODE:<img src="[+src+]" alt="[+alt+]" />`
]]
```

Вернёт:

```html
<img src="assets/images/some_img1.jpg" alt="Изображение 1" />
<img src="assets/images/some_img2.jpg" alt="Изображение 2" />
```


### Вывод строк с разным количеством колонок, используя плейсхолдер `[+allColumnValues+]` и параметры `rowTpl`, `colGlue`

Пусть первая строка содержит 2 колонки, вторая — 3, третья — 1:

```
[[ddGetMultipleField?
	&inputString=`{
		"Первые цены": [
			"100 ₽",
			"120 ₽"
		],
		"Вторые цены": [
			"300 ₽",
			"320 ₽",
			"350 ₽"
		],
		"Третьи цены": [
			"50 ₽"
		]
	}`
	&outerTpl=`@CODE:<ul>[+result+]</ul>`
	&rowTpl=`@CODE:<li>[+rowKey+]: [+allColumnValues+]</li>`
	&colGlue=`, `
]]
```

Вернёт:

```html
<ul>
	<li>Первые цены: 100 ₽, 120 ₽</li>
	<li>Вторые цены: 300 ₽, 320 ₽, 350 ₽</li>
	<li>Вторые цены: 50 ₽</li>
</ul>
```


### Получение и вывод данных из поля (TV) `prices` документа с ID = `25` в виде таблицы, если что-то есть и ничего, если нету

Исходное значение поля:

```
Яблоки вкусные::100::кг||Гвозди обыкновенные::5 000::центнер||Коты::865::шт
```

Вызов сниппета (где угодно):

```
[[ddGetMultipleField?
	&inputString_docField=`prices`
	&inputString_docId=`25`
	&outerTpl=`prices`
	&rowTpl=`prices_item`
]]
```

Код чанка `prices_item`:

```html
<tr>
	<td>[+rowNumber+]</td>
	<td>[+col0+]</td>
	<td>[+col1+]/[+col2+]</td>
</tr>
```

Код чанка `prices`:

```html
<h1>Табличка цен</h1>
<table>
	[+result+]
</table>
```

Вернёт:

```html
<h1>Price table</h1>
<table>
	<tr>
		<td>1</td>
		<td>Яблоки вкусные</td>
		<td>100/кг</td>
	</tr>
	<tr>
		<td>2</td>
		<td>Гвозди обыкновенные</td>
		<td>5 000/центнер</td>
	</tr>
	<tr>
		<td>3</td>
		<td>Коты</td>
		<td>865/шт</td>
	</tr>
</table>
```


### Вывод тегов документа через запятую с использованием регулярного выражения в `inputString_rowDelimiter`

Пусть теги документа у нас хранятся в TV `tags` и к этой TV у нас применён виджет [(MODX)EvolutionCMS.plugins.ManagerManager.mm_widget_tags](https://code.divandesign.biz/modx/mm_widget_tags).
Пользователь заполняет теги через запятую, при этом, может заполняться как с пробелами по краям, так и без них.

Значение TV `tags`:

```
Коты, Собаки,Киты, Медведи ,Слоны
```

Вызов сниппета в шаблоне документа:

```
[[ddGetMultipleField?
	&inputString=`[*tags*]`
	&inputString_rowDelimiter=`/\s*,\s*/`
	&rowGlue=`, `
	&rowTpl=`tags_item`
]]
```

Код чанка `tags_item`:

```html
<a href="[~16~]?tags=[+col0+]">[+col0+]</a>
```

Returns:

```html
<a href="[~16~]?tags=Коты">Коты</a>,
<a href="[~16~]?tags=Собаки">Собаки</a>,
<a href="[~16~]?tags=Киты">Киты</a>,
<a href="[~16~]?tags=Медведи">Медведи</a>,
<a href="[~16~]?tags=Слоны">Слоны</a>
```


### Передача дополнительных данных через параметр `placeholders`

```
[[ddGetMultipleField?
	&inputString=`Серый::8 кг::любит мясо||Рыжий::6 кг::вегетарианец`
	&outerTpl=`cats`
	&rowTpl=`cats_item`
	&colTpl=`cats_item_color,null,null`
	&placeholders=`{
		"kind": "коты",
		"price": "не продаётся",
		"colorTitle": "Шерсть густая, хорошая."
	}`
]]
```

Код чанка `cats` (`[+kind+]` будет заменено на `коты`):

```html
<h1>Наши любимые [+kind+], [+resultTotal+] штуки.</h1>
<div>
	[+result+]
</div>
```

Код чанка `cats_item` (`[+price+]` будет заменено на `не продаётся`):

```html
<p>[+rowNumber+]. [+col0+], [+col1+], [+col2+] — <i>[+price+]</i>.</p>
```

Код чанка `cats_item_color` (`[+colorTitle+]` будет заменено на `Шерсть густая, хорошая.`):

```html
<span title="[+colorTitle+]">[+val+]</span>
```

Вернёт:

```html
<h1>Наши любимые коты, 2 штуки.</h1>
<div>
	<p>1. <span title="Шерсть густая, хорошая.">Серый</span>, 8 кг, любит мясо — <i>не продаётся</i>.</p>
	<p>2. <span title="Шерсть густая, хорошая.">Рыжий</span>, 6 кг, вегетарианец — <i>не продаётся</i>.</p>
</div>
```


### Фильтрация значению колонки (параметр `filter`)

```
[[ddGetMultipleField?
	&inputString=`[
		[
			"John Bon Jovi",
			"musician",
			"Bon Jovi"
		],
		[
			"Steve Jobs",
			"businessman",
			"Apple"
		],
		[
			"Roger Waters",
			"musician",
			"Pink Floyd"
		],
		[
			"Robbie Williams",
			"musician",
			""
		],
		[
			"Hugh Laurie",
			"actor",
			""
		]
	]`
	&filter=`
		1 == 'musician' &&
		2 != '' ||
		0 == 'Hugh Laurie'
	`
	&outputFormat=`json`
]]
```

Вернёт:

```json
[
	[
		"John Bon Jovi",
		"musician",
		"Bon Jovi"
	],
	[
		"Roger Waters",
		"musician",
		"Pink Floyd"
	],
	[
		"Hugh Laurie",
		"actor",
		""
	]
]
```


### Сортировка JSON-объекта по нескольким колонкам (параметры → `sortBy`, `sortDir`)

```
[[ddGetMultipleField?
	&inputString=`{
		"Альберт Эйнштейн": {
			"номер": "18",
			"рождение": "1879.03.14",
			"смерть": "1955.04.18"
		},
		"Алан Тьюринг": {
			"номер": "42",
			"рождение": "1912.06.23",
			"смерть": "1954.06.07"
		},
		"Никола Тесла": {
			"номер": "7",
			"рождение": "1856.07.10",
			"смерть": "1943.01.07"
		},
		"Мария Склодовская-Кюри": {
			"номер": "42",
			"рождение": "1867.11.07",
			"смерть": "1934.07.04"
		},
		"Дмитрий Менделеев": {
			"номер": "7",
			"рождение": "1834.02.08",
			"смерть": "1907.02.02"
		}
	}`
	&sortDir=`ASC`
	&sortBy=`номер,рождение`
	&outputFormat=`json`
]]
```

Returns:

```json
{
	"Дмитрий Менделеев": {
		"номер": "7",
		"рождение": "1834.02.08",
		"смерть": "1907.02.02"
	},
	"Никола Тесла": {
		"номер": "7",
		"рождение": "1856.07.10",
		"смерть": "1943.01.07"
	},
	"Альберт Эйнштейн": {
		"номер": "18",
		"рождение": "1879.03.14",
		"смерть": "1955.04.18"
	},
	"Мария Склодовская-Кюри": {
		"номер": "42",
		"рождение": "1867.11.07",
		"смерть": "1934.07.04"
	},
	"Алан Тьюринг": {
		"номер": "42",
		"рождение": "1912.06.23",
		"смерть": "1954.06.07"
	}
}
```


### Запустить сниппет через `\DDTools\Snippet::runSnippet` без DB и eval

```php
//Подключение (MODX)EvolutionCMS.libraries.ddTools
require_once(
	$modx->getConfig('base_path') .
	'assets/libs/ddTools/modx.ddtools.class.php'
);

//Запуск (MODX)EvolutionCMS.snippets.ddGetMultipleField
\DDTools\Snippet::runSnippet([
	'name' => 'ddGetMultipleField',
	'params' => [
		'inputString' => '[
			[
				"assets/images/example1.png",
				"Пример изображения 1"
			],
			[
				"assets/images/example2.png",
				"Пример изображения 2"
			]
		]',
		'rowTpl' => '@CODE:<img src="[+col0+]" alt="[+col1+]" />'
	]
]);
```


_Примеров здесь можно напридумывать великое множество. Так что, если что не понятно, спрашивайте._


## Ссылки

* [Home page](https://code.divandesign.ru/modx/ddgetmultiplefield)
* [Telegram chat](https://t.me/dd_code)
* [Packagist](https://packagist.org/packages/dd/evolutioncms-snippets-ddgetmultiplefield)


<link rel="stylesheet" type="text/css" href="https://DivanDesign.ru/assets/files/ddMarkdown.css" />