<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raPath} function plugin
 *
 * Type:     function<br>
 * Name:     raPath<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raPath($params, &$smarty) {
	if (!empty($params['visible'])) {
		$params['delimeter'] = empty($params['delimeter']) ? '/' : $params['delimeter'];
		return $GLOBALS['container']->get('page')->getPath($params['delimeter']);
	} else {
		return '';
	}
}

?>
