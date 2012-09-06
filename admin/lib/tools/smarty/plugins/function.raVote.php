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
global $urlprops;
	$urlprops['action'] = 'form';
	$urlprops['frmname'] = empty($params['name']) ? '' : $params['name'];
	inc_u('vote');
	$unit = new VoteUnit($urlprops);
	return $unit->getBody();
}

?>
