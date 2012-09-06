<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raMethod} function plugin
 *
 * Type:     function<br>
 * Name:     raMethod<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raMethod($params, &$smarty) {
	if (!isset($params['ref'])) {
		$smarty->trigger_error('raMethod: Не указан параметр: ref');
	} else {
		return $GLOBALS['rtti']->callMethodByURL($params['ref'], false);
	}
}

?>
