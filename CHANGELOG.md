# (MODX)EvolutionCMS.snippets.ddGetMultipleField changelog


## Version 3.6 (2021-06-28)
* \* Attention! PHP >= 5.6 is required.
* \* Attention! (MODX)EvolutionCMS.libraries.ddTools >= 0.50 is required.
* \* Attention! (MODX)EvolutionCMS.snippets.ddTypograph >= 2.5 is required.
* \+ Parameters:
	* \+ `placeholders`: Can also be set as [HJSON](https://hjson.github.io/) or as a native PHP object or array (e. g. for calls through `$modx->runSnippet`).
	* \+ `columns`, `sortBy`, `typography`, `colTpl`: Can also be set as a native PHP array (e. g. for calls through `$modx->runSnippet`).
* \+ You can just call `\DDTools\Snippet::runSnippet` to run the snippet without DB and eval (see README → Examples).
* \+ `\ddGetMultipleField\Snippet`: The new class. All snippet code was moved here.
* \* `\DDTools\Snippet::runSnippet` is used instead of `$modx->runSnippet` to run (MODX)EvolutionCMS.snippets.ddTypograph without DB and eval.
* \- Removed compatibility with ancient versions of (MODX)EvolutionCMS.libraries.ddTools.
* \+ README:
	* \+ Documentation → Installation → Using (MODX)EvolutionCMS.libraries.ddInstaller.
	* \+ Links.
* \+ Composer.json:
	* \+ `support`.
	* \+ `authors`: Added missed homepages.


## Version 3.5.1 (2020-06-22)
* \* Improved compatibility with new versions of (MODX)EvolutionCMS.libraries.ddTools.


## Version 3.5 (2020-05-25)
* \+ Parameters → `inputString`: Can also be a JSON object, not just an array.
* \* Parameters → `filter`:
	* \* “Is equal” operator changed to `==` from `::` (with backward compatibility).
	* \+ Added “is not equal” operator (`!=`).
	* \+ Added “and” condition (`&&`).
	* \+ Values can be quoted.
	* \+ Spaces, tabs and line breaks are allowed.
* \+ Composer.json.
* \+ README.
* \+ README_ru.
* \+ CHANGELOG.
* \+ CHANGELOG_ru.


## Version 3.4 (2018-11-14)
* \+ Parameters → `placeholders`:
	* \+ Added arrays support.
	* \+ Added JSON format support.
* \+ Parameters → `inputString`: Added JSON format support.
* \* The following parameters were renamed (the snippet works with the old names but they are deprecated):
	* \* `rowDelimiter` → `inputString_rowDelimiter`.
	* \* `colDelimiter` → `inputString_colDelimiter`.


## Version 3.3 (2016-06-06)
* \+ Parameters → `outerTpl`, `rowTpl`, `colTpl`: Added the ability to use inline templates in snippet call, using `'@CODE:'` prefix.
* \+ Parameters → `rowTpl`, `colTpl`: Additional data from the `placeholders` parameter are now also will be passed into row and column templates
* \* Parameters → `placeholders`: Additional data has to be passed through the parameter must be a Query string (the old format is still supported but deprecated).
* \* The following parameters were renamed (the snippet works with the old names but they are deprecated):
	* \* `string` → `inputString`.
	* \* `docField` → `inputString_docField`.
	* \* `docId` → `inputString_docId`.
* \* Refactoring: The snippet result will be returned in anyway (empty string for empty result).


## Version 3.2 (2015-06-23)
* \+ Parameters → `colTpl:` The `[+rowNumber+]` placeholder is now also available within column templates.
* \+ Parameters → `rowTpl`, `colTpl`: The new placeholder `[+rowNumber.zeroBased+]` (index of the current row, starts at 0) was added to row and column templates. It’s very useful sometimes.
* \* Refactoring:
	* \* Column templates are processed only if they are used.
	* \* The `sortBy` and `sortDir` parameters are processed only if they are used.
	* \* The `startRow` and `totalRows` parameters are processed only if they are used.
	* \* Some internal variables have been renamed.


## Version 3.1 (2014-07-03)
* \+ Parameters → `outputFormat`: The new output format type `htmlarray` has been added. It is a one-dimensional array, which elements are completely processed rows. As with `array`, it makes sense to use the parameter only with `resultToPlaceholder`.


## Version 3.0b (2014-03-02)
* \* Attention! (MODX)EvolutionCMS.libraries.ddTools >= 0.11 is required.
* \* The `\ddTools:sort2dArray` method is used for sorting instead of the local function.
* \* The `\ddTools:getTemplateVarOutput` method is used for getting field value instead of (MODX)EvolutionCMS.snippets.ddGetDocumentField.
* \* The following parameters have been renamed, also their description and order were changed:
	* \* `field` → `string`.
	* \* `getField` → `docField`.
	* \* `getId` → `docId`.
	* \* `splY` → `rowDelimiter`.
	* \* `splX` → `colDelimiter`.
	* \* `num` → `startRow`.
	* \* `count` → `totalRows`.
	* \* `colNum` → `columns`.
	* \* `vals` → `filter`.
	* \* `typographing` → `typography`.
	* \* `format` → `outputFormat`.
	* \* `glueY` → `rowGlue`.
	* \* `glueX` → `colGlue`.
	* \* `tplY` → `rowTpl`.
	* \* `tplX` → `colTpl`.
	* \* `tplWrap` → `outerTpl`.
	* \* `totalPlaceholder` → `totalRowsToPlaceholder`.
* \* Parameters → `typography`:
	* \* Correction is no longer performed to the final result but to values individually.
	* \* Now takes a comma-separated list of column indexes specifying the columns to correct.
* \* Parameters → `rowTpl`:
	* \* The placeholder `[+row_number+]` has been renamed as `[+rowNumber+]`.
	* \* The placeholders of the form `[+valX+]` have been renamed as `[+colX+]`.
* \* Parameters → `outerTpl`:
	* \* The placeholder `[+wrapper+]` has been renamed as `[+result+]`.
	* \* The placeholders of the form `[+rowY.valX+]` have been renamed as `[+rowY.colX+]`.
* \* Parameters → `resultToPlaceholder`: Now takes a placeholder name instead of a boolean value.
* \* Minor code style and other changes.


## Version 2.18 (2013-11-11)
* \* Attention! (MODX)EvolutionCMS.libraries.ddTools >= 0.10 is required.
* \+ Parameters → `tplWrap`, `tplY`: The new placeholder `[+resultTotal+]` being used in the chunks holds the total number of **RETURNED** elements.
* \* Parameters → `tplWrap`: 
	* \* The error because of which the placeholder `[+total+]` used to be parsed correctly inside of a wrapping chunk only if `placeholders` had been set has been eliminated.
	* \+ The placeholders of the form `[+rowY.valX+]` (where `Y` — row number, `X` — column number) that contains all values are available.
* \* Please note the placeholder `[+total+]` that currently holds the total number of **ALL** elements.
* \* Minor code changes.


## Version 2.17 (2013-09-18)
* \+ Parameters → `vals`: Filtration of all columns is now available.


## Version 2.16.2 (2013-07-11)
* \* Values contained in the integer type columns are being compared like integers while sorting.


## Version 2.16.1 (2013-06-13)
* \* Bugfix: Undeclared variables in PHP >= 5.3.


## Version 2.16 (2013-03-28)
* \* Attention! (MODX)EvolutionCMS.snippets.ddGetDocumentField >= 2.4 is required.
* \+ Parameters → `tplWrap`: The `[+total+]` placeholder is now available.
* \+ Parameters → `splY`, `splX`: Can process regular expressions.
* \+ Parameters → `resultToPlaceholder`: The snippet result can be added to a placeholder.
* \+ Parameters → `format`: The snippet can return result in array format. It is useful when the snippet is run by `$modx->runSnippet` or the snippet returns result to a placeholder.
* \- Parameters → `getPublished`: The parameter has been deleted. `published` is no longer affects the result.


## Version 2.15 (2013-02-11)
* \+ Parameters → `totalPlaceholder`. The new parameter. The outputting of the total number of rows into an external placeholder has been added.


## Version 2.14 (2013-01-10)
* \+ Parameters → `tplY`: The placeholder `[+total+]` that is the number of all rows has been added into the chunk.


## Version 2.13 (2012-09-03)
* \+ Parameters → `sortDir`: The `'REVERSE'` value of the parameter has been added. Values would be returned in reverse order if the parameter equaled `'REVERSE'`.


<link rel="stylesheet" type="text/css" href="https://DivanDesign.ru/assets/files/ddMarkdown.css" />
<style>ul{list-style:none;}</style>