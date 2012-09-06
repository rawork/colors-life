<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raMap} function plugin
 *
 * Type:     function<br>
 * Name:     raMap<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raMap($params, &$smarty) {
	return $GLOBALS['utree']->getMap();
}

?>
