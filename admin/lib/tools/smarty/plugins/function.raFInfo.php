<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raFInfo} function plugin
 *
 * Type:     function<br>
 * Name:     raFInfo<br>
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return array
 */

function smarty_function_raFInfo($params, &$smarty) {
	if (!isset($params['file'])) {
		$smarty->trigger_error('raFInfo: Не указан параметр: file');
	} elseif (!isset($params['var'])) {
		$smarty->trigger_error('raFInfo: Не указан параметр: var');
	} else {
		$ipath = pathinfo($params['file']);
		$smarty->assign($params['var'], array('ext' => $ipath['extension'], 'size' => CUtils::getFileSize($params['file'])));
	}
}
?>
