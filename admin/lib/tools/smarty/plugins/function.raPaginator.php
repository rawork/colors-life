<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raPagiNation} function plugin
 *
 * Type:     function
 * Name:     raPaginator
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return object Pages
 */

function smarty_function_raPaginator($params, &$smarty) {
	if (!isset($params['table'])) {
		$smarty->trigger_error('raPaginator: Не указан параметр: table');
	} elseif (!isset($params['pref'])) {
		$smarty->trigger_error('raPaginator: Не указан параметр: pref');
	} else {
		if (!isset($params['var'])) {
			$smarty->trigger_error('raPaginator: Не указан параметр: var');
		} else {
			$paginator = $GLOBALS['container']->get('paginator');
			$paginator->paginate(
					$GLOBALS['container']->getTable($params['table']), // Таблица с данными
					$params['pref'], 							  //Базовая ссылка
					$params['query'],					 		  // Запрос
					empty($params['per_page']) ? 10 : $params['per_page'], //Результатов на страницу
					empty($params['page']) ? 1 : $params['page']  // Страница
			);
			if (!empty($params['tpl']))
				$paginator->setTemplate($params['tpl']);
			$smarty->assign($params['var'], $paginator);
		}
	}
}


?>
