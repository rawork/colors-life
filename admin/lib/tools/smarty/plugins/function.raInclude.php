<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raInclude} function plugin
 *
 * Type:     function<br>
 * Name:     raInclude<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raInclude($params, &$smarty) {
	if (empty($params['var'])) {
		$smarty->trigger_error('raInclude: Не указан параметр: var');
	} else {
		if ($item = $GLOBALS['rtti']->getItem('tree_blocks',"name='{$params['var']}' AND publish='on'")) {
			return $item['body'];
		} else {
			return '';
		}
	}
}

?>
