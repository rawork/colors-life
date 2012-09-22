<?php
    
	if (preg_match('/^\/notice\/[\d]{6}$/', $_SERVER['REQUEST_URI'])) {
		include_once('loader.php');
		$smarty->assign('order', str_replace('/notice/', '', $_SERVER['REQUEST_URI']));
		$smarty->display('page.notice.tpl');
	} elseif (preg_match('/^\/(adminnew|_profiler)\//', $_SERVER['REQUEST_URI'])) {
		include('app_dev.php');
	} elseif (preg_match('/^\/ajax\//', $_SERVER['REQUEST_URI'])) {
		include_once('loader.php');
		inc_lib('AjaxListener.php');
		$listener = new AjaxListener();
		$obj = new ReflectionClass('AjaxListener');
		if (isset($_POST['method'])) {
			$post = $_POST;
			unset($post['method']);
			echo $obj->getMethod($_POST['method'])->invokeArgs($listener, $post);
		} else {
			throw new Exception('AJAX call error.');
		}
	} elseif (preg_match('/^\/adminajax\//', $_SERVER['REQUEST_URI'])) {
		include_once('loader.php');
		inc_lib('AdminInterface/AjaxListener.php');
		$listener = new AjaxListener();
		$obj = new ReflectionClass('AjaxListener');
		if (isset($_POST['method'])) {
			$post = $_POST;
			unset($post['method']);
			echo $obj->getMethod($_POST['method'])->invokeArgs($listener, $post);
		} else {
			throw new Exception('AJAX call error.');
		}
	} else {
		include_once('loader.php');
		inc_lib('Page.php');
		$page = new Page($GLOBALS['utree']);
		$page->show();
	}
	
