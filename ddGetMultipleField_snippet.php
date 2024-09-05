<?php
/**
 * ddGetMultipleField
 * @version 3.10 (2024-09-06)
 * 
 * @see README.md
 * 
 * @link https://code.divandesign.ru/modx/ddgetmultiplefield
 * 
 * @copyright 2009–2024 Ronef {@link https://Ronef.me }
 */

// Include (MODX)EvolutionCMS.libraries.ddTools
require_once(
	$modx->getConfig('base_path')
	. 'assets/libs/ddTools/modx.ddtools.class.php'
);

return \DDTools\Snippet::runSnippet([
	'name' => 'ddGetMultipleField',
	'params' => $params,
]);
?>