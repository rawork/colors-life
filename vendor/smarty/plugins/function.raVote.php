<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raVote} function plugin
 *
 * Type:     function<br>
 * Name:     raVote<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raVote($params, &$smarty) {
	$controller = new Fuga\CMSBundle\Model\VoteManager();
	return $controller->getForm($params['name']);
}

?>
