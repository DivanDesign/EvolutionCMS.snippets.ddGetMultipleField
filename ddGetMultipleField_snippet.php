<?php
/**
 * ddGetMultipleField
 * @version 3.8.1 (2022-06-09)
 * 
 * @see README.md
 * 
 * @link https://code.divandesign.biz/modx/ddgetmultiplefield
 * 
 * @copyright 2009–2022 DD Group {@link https://DivanDesign.biz }
 */

//Include (MODX)EvolutionCMS.libraries.ddTools
require_once(
	$modx->getConfig('base_path') .
	'assets/libs/ddTools/modx.ddtools.class.php'
);

return \DDTools\Snippet::runSnippet([
	'name' => 'ddGetMultipleField',
	'params' => $params
]);
?>