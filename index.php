<?php
    
	if (preg_match('/^\/notice\/[\d]{6}$/', $_SERVER['REQUEST_URI'])) {
		include_once('loader.php');
		$smarty->assign('order', str_replace('/notice/', '', $_SERVER['REQUEST_URI']));
		$smarty->display('page.notice.tpl');
	} elseif (preg_match('/^\/(adminnew|_profiler)\//', $_SERVER['REQUEST_URI'])) {
		include('app_dev.php');
	} else {
		include_once('loader.php');
		inc_lib('CPage.php');
		$page = new Page($GLOBALS['utree']);
		$page->show();
	}
	
