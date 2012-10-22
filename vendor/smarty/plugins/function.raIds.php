<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raIds} function plugin
 *
 * Type:     function<br>
 * Name:     raIds<br>
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return array
 */

function smarty_function_raIds($params, &$smarty) {
	if (empty($params['var'])) {
		$smarty->trigger_error('raIds: Не указан параметр: var');
	} else {
		if (!empty($params['query'])) {
			$smarty->assign($params['var'], implode(',', array_keys($GLOBALS['container']->getNativeItems($params['query']))));
		}
	}
}


?>
