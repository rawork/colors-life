<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raException} function plugin
 *
 * Type:     function<br>
 * Name:     raException<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raException($params, &$smarty) {
	throw new \Exception\NotFoundHttpException('Неcуществующая страница');
}

?>
