<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raMethod} function plugin
 *
 * Type:     function<br>
 * Name:     raSubscribe<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raSubscribe($params, &$smarty) {
	if (isset($_SESSION['subscribe_message'])) {
		$smarty->assign('subscribe_message', $_SESSION['subscribe_message']);
		unset($_SESSION['subscribe_message']);
	}
	return '<div id="subscribe_form">'.$smarty->fetch('service/subscribe/form.tpl').'</div>';
}

?>
