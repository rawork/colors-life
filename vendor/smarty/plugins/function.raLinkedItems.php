<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raLinkedItems} function plugin
 *
 * Type:     function<br>
 * Name:     raLinkedItems<br>
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return array
 */

function smarty_function_raLinkedItems($params, &$smarty) {
	if (empty($params['table'])) {
		$smarty->trigger_error('raLinkedItems: Не указан параметр: table');
	} else {
		if (empty($params['var'])) {
			$smarty->trigger_error('raLinkedItems: Не указан параметр: var');
		} else {
			$class = $params['table'];
			$link = $params['value'];
			$condition = !empty($params['query']) ? $params['query'] : '1=1';
			$items = $GLOBALS['container']->get('connection')->getItems('eee', 'SELECT '.$link.' FROM '.$class.' WHERE '.$condition);
			$ids = array();
			foreach ($items as $item) {
				$ids[] = $item[$link];
			}
			$smarty->assign($params['var'], implode(',', $ids));
		}
	}
}


?>
