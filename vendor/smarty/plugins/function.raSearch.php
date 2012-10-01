<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raSearch} function plugin
 *
 * Type:     function<br>
 * Name:     raSearch<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raSearch($params, &$smarty) {
	$controller = new Fuga\PublicBundle\Controller\SearchController();
	return $controller->getContent();
}

?>
