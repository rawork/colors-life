<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raCount} function plugin
 *
 * Type:     function<br>
 * Name:     raCount<br>
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return array
 */

function smarty_function_raCount($params, &$smarty) {
	if (!isset($params['table'])) {
		$smarty->trigger_error('raItems: Не указан параметр: table');
	} else {
		if (!isset($params['var'])) {
			$smarty->trigger_error('raItems: Не указан параметр: var');
		} else {
			$smarty->assign($params['var'], $GLOBALS['rtti']->getCount($params['table'], isset($params['query']) ? $params['query'] : ''));
		}
	}
}


?>
