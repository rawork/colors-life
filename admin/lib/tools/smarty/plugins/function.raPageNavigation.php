<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raPageNavigation} function plugin
 *
 * Type:     function
 * Name:     raPageNavigation
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return object Pages
 */

function smarty_function_raPageNavigation($params, &$smarty) {
	if (!isset($params['table'])) {
		$smarty->trigger_error('raPageNavigation: Не указан параметр: table');
	} elseif (!isset($params['pref'])) {
		$smarty->trigger_error('raPageNavigation: Не указан параметр: pref');
	} else {
		if (!isset($params['var'])) {
			$smarty->trigger_error('raItems: Не указан параметр: var');
		} else {
			inc_lib('CPageNavigation.php');
			$pages = new CPageNavigation(
					$GLOBALS['rtti']->getTable($params['table']), // Таблица с данными
					$params['pref'], 							  //Базовая ссылка
					$params['query'],					 		  // Запрос
					empty($params['per_page']) ? 10 : $params['per_page'], //Результатов на страницу
					empty($params['page']) ? 1 : $params['page']  // Страница
			);
			if (!empty($params['tpl']))
				$pages->setTemplate($params['tpl']);
			$smarty->assign($params['var'], $pages);
		}
	}
}


?>
