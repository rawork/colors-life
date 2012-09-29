<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raForm} function plugin
 *
 * Type:     function<br>
 * Name:     raForm<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raForm($params, &$smarty) {
	if (empty($params['name'])) {
		$smarty->trigger_error('raForm: Не указан параметр: name');
	} else {
		$manager = new \Model\FormManager();
		return $manager->getForm("name='".$params['name']."'", $params);
	}
}

?>
