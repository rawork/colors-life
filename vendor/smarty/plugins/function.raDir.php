<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raDir} function plugin
 *
 * Type:     function<br>
 * Name:     raDir<br>
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return array
 */

function smarty_function_raDir($params, &$smarty) {
	if (!isset($params['var'])) {
		$smarty->trigger_error('raDir: Не указан параметр: var');
	} else {
		$smarty->assign(
			$params['var'], 
			$GLOBALS['container']->get('page')->getNodes(
				!empty($params['query']) ? $params['query'] : 0, 
				!empty($params['where']) ? $params['where'] : "publish='on'"
			)
		);
	}
}

?>
