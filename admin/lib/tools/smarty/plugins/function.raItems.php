<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raItems} function plugin
 *
 * Type:     function<br>
 * Name:     raItems<br>
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return array
 */

function smarty_function_raItems($params, &$smarty) {
	if (empty($params['table']) && empty($params['nquery'])) {
		$smarty->trigger_error('raItems: �� ������ ��������: table');
	} else {
		if (empty($params['var'])) {
			$smarty->trigger_error('raItems: �� ������ ��������: var');
		} else {
			if (!empty($params['nquery'])) {
				$smarty->assign($params['var'], $GLOBALS['rtti']->getNativeItems($params['nquery']));
			} else {
				$class = $params['table'];
				$condition = !empty($params['query']) ? $params['query'] : '';
				$sort = !empty($params['sort']) ? $params['sort'] : '';
				$limit = !empty($params['limit']) ? $params['limit'] : false;
				$select = !empty($params['select']) ? $params['select'] : '';
				$smarty->assign($params['var'], $GLOBALS['rtti']->getItems($class, $condition, $sort, $limit, $select));
			}
		}
	}
}


?>
