<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raPath} function plugin
 *
 * Type:     function<br>
 * Name:     raSetVar<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raSetVar($params, &$smarty) {
	if (!isset($params['var'])) {
		$smarty->trigger_error('raSetVar: Не указан параметр: var');
	} elseif (!isset($params['value'])) {
		$smarty->trigger_error('raSetVar: Не указан параметр: value');
	} else {
		$GLOBALS['rtti']->setVar($params['var'], $params['value']);
	}
}

?>
