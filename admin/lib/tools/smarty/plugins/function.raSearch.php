<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raSearch} function plugin
 *
 * Type:     function<br>
 * Name:     raSearch<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raSearch($params, &$smarty) {
	inc_lib('components/SearchUnit.php');
	$unit = new SearchUnit($GLOBALS['urlprops']);
	return $unit->getBody();
}

?>
