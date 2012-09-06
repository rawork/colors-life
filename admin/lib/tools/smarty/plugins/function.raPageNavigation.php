<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raPages} function plugin
 *
 * Type:     function
 * Name:     raPages
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return object Pages
 */

function smarty_function_raPageNavigation($params, &$smarty) {
	if (!isset($params['table'])) {
		$smarty->trigger_error('raPageNavigation: �� ������ ��������: table');
	} elseif (!isset($params['pref'])) {
		$smarty->trigger_error('raPageNavigation: �� ������ ��������: pref');
	} else {
		if (!isset($params['var'])) {
			$smarty->trigger_error('raItems: �� ������ ��������: var');
		} else {
			inc_lib('CPageNavigation.php');
			$pages = new CPageNavigation(
					$GLOBALS['rtti']->getTable($params['table']), // ������� � �������
					$params['pref'], 							  //������� ������
					$params['query'],					 		  // ������
					empty($params['per_page']) ? 10 : $params['per_page'], //����������� �� ��������
					empty($params['page']) ? 1 : $params['page']  // ��������
			);
			if (!empty($params['tpl']))
				$pages->setTemplate($params['tpl']);
			$smarty->assign($params['var'], $pages);
		}
	}
}


?>
