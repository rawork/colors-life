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

function smarty_function_raPages($params, &$smarty) {
	if (!isset($params['table'])) {
		$smarty->trigger_error('raPages: Не указан параметр: table');
	} elseif (!isset($params['pref'])) {
		$smarty->trigger_error('raPages: Не указан параметр: pref');
	} else {
		if (!isset($params['var'])) {
			$smarty->trigger_error('raItems: Не указан параметр: var');
		} else {
			inc_lib('Pages.php');
			$pages = new Pages(
					$GLOBALS['rtti']->getTable($params['table']), // Таблица с данными
					$params['pref'], 							  //Базовая ссылка
					array('where' => $params['query']), 		  // Запрос
					empty($params['per_page']) ? 10 : $params['per_page'], //Результатов на страницу
					empty($params['page']) ? 1 : $params['page']  // Страница
			);
			if (!empty($params['tpl']))
				$pages->template = 'service/'.$params['tpl'];
			$smarty->assign($params['var'], $pages);
		}
	}
}


?>
