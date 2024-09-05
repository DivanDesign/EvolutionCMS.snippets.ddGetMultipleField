<?php
/**
 * ddGetMultipleField
 * @version 3.9 (2023-01-11)
 * 
 * @see README.md
 * 
 * @link https://code.divandesign.ru/modx/ddgetmultiplefield
 * 
 * @copyright 2009–2023 Ronef {@link https://Ronef.me }
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