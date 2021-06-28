# (MODX)EvolutionCMS.snippets.ddGetMultipleField

A snippet for processing, manipulations and custom output structured data (JSON or separated by delimiters strings).
The fields formed by the [mm_ddMultipleFields](https://code.divandesign.biz/modx/mm_ddmultiplefields) widget values output gets more convinient with the snippet.

Features:
* Field value getting of a required document (TV) by its ID. The `inputString_docField` and `inputString_docId` parameters.
* Return of a required values number by values and row number. The `startRow`, `totalRows` and `filter` parameters.
* Return of a required value by columns number. The `columns` parameter.
* Rows sorting (including multiple sorting) by columns values before returning (`'ASC'`, `'DESC'`, `'RAND'`, `'REVERSE'`). The `sortDir` and `sortBy` parameters.
* Output of data being separated by rows and columns delimeters. The `rowGlue` and `colGlue` parameters.
* Empty values remove before output. The `removeEmptyRows` and `removeEmptyCols` parameters.
* Values typography before output (the snippet ddTypograph is used). The `typography` parameter.
* Result URL encode. The `urlencode` parameter.
* Result JSON encode. The `outputFormat` parameter.
* Values returning by the given templates (chunks) of rows and columns (also the `[+rowNumber+]` and `[+rowNumber.zeroBased+]` placeholders with a row number is available in the row and column templates). The `rowTpl` and `colTpl` parameters.
* Return of results in a chunk (the `outerTpl` parameter) sending additional data through placeholders. The `placeholders` parameter.


## Requires

* PHP >= 5.6
* [(MODX)EvolutionCMS](https://github.com/evolution-cms/evolution) >= 1.1
* [(MODX)EvolutionCMS.libraries.ddTools](https://code.divandesign.biz/modx/ddtools) >= 0.50
* [(MODX)EvolutionCMS.snippets.ddTypograph](https://code.divandesign.biz/modx/ddtypograph) >= 2.5 (if typography is required)


## Documentation


### Installation


#### 1. Elements → Snippets: Create a new snippet with the following data

1. Snippet name: `ddGetMultipleField`.
2. Description: `<b>3.5.1</b> A snippet for processing, manipulations and custom output structured data (JSON or separated by delimiters strings).`.
3. Category: `Core`.
4. Parse DocBlock: `no`.
5. Snippet code (php): Insert content of the `ddGetMultipleField_snippet.php` file from the archive.


#### 2. Elements → Manage Files

1. Create a new folder `assets/snippets/ddGetMultipleField/`.
2. Extract the archive to the folder (except `ddGetMultipleField_snippet.php`).



### Parameters description

From the pair of `inputString` / `inputString_docField` parameters one is required.

* `inputString`
	* Desctription: The input string containing values.
	* Valid values:
		* `stringJsonArray` — as [JSON](https://en.wikipedia.org/wiki/JSON) array
		* `stringJsonObject` — as [JSON](https://en.wikipedia.org/wiki/JSON) object
		* `stringSeparated` — separated by `inputString_rowDelimiter` and `inputString_colDelimiter`
	* **Required**
	
* `inputString_docField`
	* Desctription: The name of the document field/TV which value is required to get.  
		If the parameter is passed then the input string will be taken from the field / TV and `inputString` will be ignored.
	* Valid values: `string`
	* Default value: —
	
* `inputString_docId`
	* Desctription: ID of the document which field/TV value is required to get.  
	* Valid values: `integer`
	* Default value: `$modx->documentIdentifier` (the current document ID)
	
* `inputString_rowDelimiter`
	* Desctription: The input string row delimiter (when `inputString` is not JSON).
	* Valid values:
		* `string`
		* `regexp`
	* Default value: `'||'`
	
* `inputString_colDelimiter`
	* Desctription: The input string column delimiter (when `inputString` is not JSON).
	* Valid values:
		* `string`
		* `regexp`
	* Default value: `'::'`
	
* `startRow`
	* Desctription: The index of the initial row (indexes start at `0`).
	* Valid values: `integer`
	* Default value: `0`
	
* `totalRows`
	* Desctription: The maximum number of rows to return.
	* Valid values:
		* `integer`
		* `'all'` — all rows will be returned
	* Default value: `'all'`
	
* `columns`
	* Desctription: The indexes of columns to return (indexes start at `0`).
	* Valid values:
		* `stringCommaSeparated`
		* `array`
		* `'all'` — all columns will be returned
	* Default value: `'all'`
	
* `filter`
	* Desctription: Filter clause for columns.  
		* Thus,
			```
			0 == 'a' ||
			0 =='b' &&
			1 == 'some' &&
			2 != ''
			```
			returns the rows where:
			* `0` column is equal to `'a'` **or**
			* `0` column is equal to `'b'` **and**
			* `1` column is equal to `some` **and**
			* `2` column is not equal to `''`.
		* Quoted values are optional, this is valid too:
			```
			0 == a ||
			0 == b &&
			1 == some &&
			2 != 
			```
		* Double quotes are also supported as single quotes:
			```
			0 == "a" ||
			0 == "b" &&
			1 == "some" &&
			2 != ""
			```
		* Spaces, tabs and line breaks are optional, this is valid too: `0==a||0==b&&1==some&&2!=`.
	* Valid values: `stringSeparated`
	* Default value: —
	
* `removeEmptyRows`
	* Desctription: Is it required to remove empty rows?
	* Valid values:
		* `0`
		* `1`
	* Default value: `1`
	
* `removeEmptyCols`
	* Desctription: Is it required to remove empty columns?
	* Valid values:
		* `0`
		* `1`
	* Default value: `1`
	
* `sortBy`
	* Desctription: The index of the column to sort by (indexes start at `0`).  
		The parameter also takes comma-separated values for multiple sort, e.g. `'0,1'`.
	* Valid values:
		* `stringCommaSeparated`
		* `array`
	* Default value: `'0'`
	
* `sortDir`
	* Desctription: Rows sorting direction (case insensitive).
	* Valid values:
		* `'ASC'` — the rows will be returned in ascending order
		* `'DESC'` — the rows will be returned in descending order
		* `'RAND'` — the rows will be returned in random order
		* `'REVERSE'` — the rows will be returned in reversed order
		* `''` — the rows will be returned without sorting (as set)
	* Default value: `''`
	
* `typography`
	* Desctription: The comma separated indexes of the columns which values have to be corrected (indexes start at `0`).  
		If unset, there will be no correction.
	* Valid values:
		* `stringCommaSeparated`
		* `array`
	* Default value: —
	
* `outputFormat`
	* Desctription: Result output format (case insensitive).
	* Valid values:
		* `'html'`
		* `'json'`
		* `'array'`
		* `'htmlarray'`
	* Default value: `'html'`
	
* `rowGlue`
	* Desctription: The string that combines rows while rendering.  
		It can be used along with `rowTpl`.
	* Valid values: `string`
	* Default value: `''`
	
* `colGlue`
	* Desctription: The string that combines columns while rendering.  
		It can be used along with `colTpl`, but not with `rowTpl` for obvious reasons.
	* Valid values: `string`
	* Default value: `''`
	
* `rowTpl`
	* Desctription: The template for row rendering (`outputFormat` has to be == `'html'`).  
		Available placeholders:
		* `[+rowNumber+]` — index of current row, starts at `1`
		* `[+rowNumber.zeroBased+]` — index of current row, starts at `0`
		* `[+total+]` — total number of rows
		* `[+resultTotal+]` — total number of returned rows
		* `[+col0+]`, `[+col1+]`, etc — column values
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: —
	
* `colTpl`
	* Desctription: The comma-separated list of templates for column rendering (`outputFormat` has to be == `'html'`).  
		If the number of templates is lesser than the number of columns then the last passed template will be used to render the rest of the columns.
	* Valid values:
		* `stringCommaSeparated`
		* `array`
	* Default value: —
	
* `colTpl[i]`
	* Desctription: The template for column rendering.  
		Available placeholders:
		* `[+val+]` — value
		* `[+rowNumber+]` — index of current row, starts at `1`
		* `[+rowNumber.zeroBased+]` — index of current row, starts at `0`
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
		* `'null'` — specifies rendering without a template
	* Default value: —
	
* `outerTpl`
	* Desctription: Wrapper template (`outputFormat` has to be != `'array'`).  
		Available placeholders:
		* `[+result+]` — snippet result
		* `[+total+]` — total number of rows
		* `[+resultTotal+]` — total number of returned rows
		* `[+rowY.colX+]` — value (`Y` — row number, `X` — column number)
	* Valid values:
		* `stringChunkName`
		* `string` — use inline templates starting with `@CODE:`
	* Default value: —
	
* `placeholders`
	* Desctription:
		Additional data has to be passed into the `outerTpl`, `rowTpl` and `colTpl` templates.  
		Nested objects and arrays are supported too:
		* `{"someOne": "1", "someTwo": "test" }` => `[+someOne+], [+someTwo+]`.
		* `{"some": {"a": "one", "b": "two"} }` => `[+some.a+]`, `[+some.b+]`.
		* `{"some": ["one", "two"] }` => `[+some.0+]`, `[+some.1+]`.
	* Valid values:
		* `stringJsonObject` — as [JSON](https://en.wikipedia.org/wiki/JSON)
		* `stringHjsonObject` — as [HJSON](https://hjson.github.io/)
		* `stringQueryFormated` — as [Query string](https://en.wikipedia.org/wiki/Query_string)
		* It can also be set as a native PHP object or array (e. g. for calls through `$modx->runSnippet`):
			* `arrayAssociative`
			* `object`
	* Default value: —
	
* `urlencode`
	* Desctription: Is it required to URL encode the result?  
		* `outputFormat` has to be != `'array'`.
		* URL encoding is used according to RFC 3986.
	* Valid values:
		* `0`
		* `1`
	* Default value: `0`
	
* `totalRowsToPlaceholder`
	* Desctription: The name of the global (MODX)Evolution placeholder that holds the total number of rows.  
		The placeholder won't be set if `totalRowsToPlaceholder` is empty.
	* Valid values: `string`
	* Default value: —
	
* `resultToPlaceholder`
	* Desctription: The name of the global (MODX)Evolution placeholder that holds the snippet result.  
		The result will be returned in a regular manner if the parameter is empty.
	* Valid values: `string`
	* Default value: —


### Examples


#### Output `images` with description

The initial string (locates in `images` TV):

```
assets/images/some_img1.jpg::Image 1||assets/images/some_img2.jpg::Image 2
```

The snippet call in the template of a document:

```
[[ddGetMultipleField?
	&inputString=`[*images*]`
	&rowTpl=`images_item`
]]
```

The `images_item` chunk code:

```html
[+col1+]:
<img src="[+col0+]" alt="[+col1+]" />
```

Returns:

```html
Image 1:
<img src="assets/images/some_img1.jpg" alt="Image 1" />
Image 2:
<img src="assets/images/some_img2.jpg" alt="Image 2" />
```


#### The data getting and output from `prices` TV of the document with ID = `25` in table format if the data is not empty

The initial field value:

```
Tasty apples::100::kg||Usual nails::5 000::centner||Cats::865::pieces
```

The snippet call (wherever):

```
[[ddGetMultipleField?
	&inputString_docField=`prices`
	&inputString_docId=`25`
	&outerTpl=`prices`
	&rowTpl=`prices_item`
]]
```

The `prices_item` chunk code:

```html
<tr>
	<td>[+rowNumber+]</td>
	<td>[+col0+]</td>
	<td>[+col1+]/[+col2+]</td>
</tr>
```

The `prices` chunk code:

```html
<h1>Price table</h1>
<table>
	[+result+]
</table>
```

Returns:

```html
<h1>Price table</h1>
<table>
	<tr>
		<td>1</td>
		<td>Tasty apples</td>
		<td>100/kg</td>
	</tr>
	<tr>
		<td>2</td>
		<td>Usual nails</td>
		<td>5 000/centner</td>
	</tr>
	<tr>
		<td>3</td>
		<td>Cats</td>
		<td>865/pieces</td>
	</tr>
</table>
```


#### Return document tags separated by commas using a regular expression in `inputString_rowDelimiter`

[(MODX)EvolutionCMS.plugins.ManagerManager.mm_widget_tags](https://code.divandesign.biz/modx/mm_widget_tags) is applied to `tags` TV where document tags are stored in `tags`.
User fills in the tags separated by commas, while the field may be filled both with spaces on the sides and without them.

`tags` TV value:

```
Cats, Dogs,Whales , Bears , Elephants
```

The snippet call:

```
[[ddGetMultipleField?
	&inputString=`[*tags*]`
	&inputString_rowDelimiter=`/\s*,\s*/`
	&rowGlue=`, `
	&rowTpl=`tags_item`
]]
```

The `tags_item` chunk contents:

```html
<a href="[~16~]?tags=[+col0+]">[+col0+]</a>
```

Returns:

```html
<a href="[~16~]?tags=Cats">Cats</a>,
<a href="[~16~]?tags=Dogs">Dogs</a>,
<a href="[~16~]?tags=Whales">Whales</a>,
<a href="[~16~]?tags=Bears">Bears</a>,
<a href="[~16~]?tags=Elephants">Elephants</a>
```


#### Passing additional data into templates via `placeholders`

```
[[ddGetMultipleField?
	&inputString=`Grey::8 kg::loves meat||Red::6 kg::vegetarian`
	&outerTpl=`cats`
	&rowTpl=`cats_item`
	&colTpl=`cats_item_color,null,null`
	&placeholders=`{
		"kind": "cats",
		"price": "not for sale",
		"colorTitle": "He has a nice thick coat."
	}`
]]
```

The `cats` chunk code (`[+kind+]` will be replaced to `cats`):

```html
<h1>Our [+resultTotal+] favorite [+kind+].</h1>
<div>
	[+result+]
</div>
```

The `cats_item` chunk code (`[+price+]` will be replaced to `not for sale`):

```html
<p>[+rowNumber+]. [+col0+], [+col1+], [+col2+] — <i>[+price+]</i>.</p>
```

The `cats_item_color` chunk code (`[+colorTitle+]` will be replaced to `He has a nice thick coat.`):

```html
<span title="[+colorTitle+]">[+val+]</span>
```

Returns:

```html
<h1>Our 2 favorite cats.</h1>
<div>
	<p>1. <span title="He has a nice thick coat.">Grey</span>, 8 kg, loves meat — <i>not for sale</i>.</p>
	<p>2. <span title="He has a nice thick coat.">Red</span>, 6 kg, vegetarian — <i>not for sale</i>.</p>
</div>
```


#### Filter by column value (the `filter` parameter)

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

Returns:

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


#### Sort a JSON object by multiple columns (parameters → `sortBy`, `sortDir`)

```
[[ddGetMultipleField?
	&inputString=`{
		"Albert Einstein": {
			"number": "18",
			"born": "1879.03.14",
			"died": "1955.04.18"
		},
		"Alan Turing": {
			"number": "42",
			"born": "1912.06.23",
			"died": "1954.06.07"
		},
		"Nikola Tesla": {
			"number": "7",
			"born": "1856.07.10",
			"died": "1943.01.07"
		},
		"Marie Curie": {
			"number": "42",
			"born": "1867.11.07",
			"died": "1934.07.04"
		},
		"Dmitri Mendeleev": {
			"number": "7",
			"born": "1834.02.08",
			"died": "1907.02.02"
		}
	}`
	&sortDir=`ASC`
	&sortBy=`number,born`
	&outputFormat=`json`
]]
```

Returns:

```json
{
	"Dmitri Mendeleev": {
		"number": "7",
		"born": "1834.02.08",
		"died": "1907.02.02"
	},
	"Nikola Tesla": {
		"number": "7",
		"born": "1856.07.10",
		"died": "1943.01.07"
	},
	"Albert Einstein": {
		"number": "18",
		"born": "1879.03.14",
		"died": "1955.04.18"
	},
	"Marie Curie": {
		"number": "42",
		"born": "1867.11.07",
		"died": "1934.07.04"
	},
	"Alan Turing": {
		"number": "42",
		"born": "1912.06.23",
		"died": "1954.06.07"
	}
}
```


#### Run the snippet through `\DDTools\Snippet::runSnippet` without DB and eval

```php
//Include (MODX)EvolutionCMS.libraries.ddTools
require_once(
	$modx->getConfig('base_path') .
	'assets/libs/ddTools/modx.ddtools.class.php'
);

//Run (MODX)EvolutionCMS.snippets.ddGetMultipleField
\DDTools\Snippet::runSnippet([
	'name' => 'ddGetMultipleField',
	'params' => [
		'inputString' => '[
			[
				"assets/images/example1.png",
				"Example image 1"
			],
			[
				"assets/images/example2.png",
				"Example image 2"
			]
		]',
		'rowTpl' => '@CODE:<img src="[+col0+]" alt="[+col1+]" />'
	]
]);
```


_It is hard to write here all possible examples so if here is something that you do not completely understand, please ask us._


## [Home page →](https://code.divandesign.biz/modx/ddgetmultiplefield)


<link rel="stylesheet" type="text/css" href="https://DivanDesign.ru/assets/files/ddMarkdown.css" />