<?php

use \AdminInterface\AdminInterface;
use \Controller\PageController;
use \Security\Captcha\KCaptcha;

if (preg_match('/^\/secureimage\//', $_SERVER['REQUEST_URI'])) {
	include_once($_SERVER['DOCUMENT_ROOT'].'/src/Security/Captcha/KCaptcha.php');
	session_start();
	$captcha = new KCaptcha();
	$_SESSION['captchaHash'] = md5($captcha->getKeyString().'FWK');
	exit;
} else {	

	require_once('app/init.php');
	
	if (preg_match('/^\/ajax\//', $_SERVER['REQUEST_URI'])) {
		$controller = new \Controller\AjaxController();
		$obj = new ReflectionClass('\Controller\AjaxController');
		if (isset($_POST['method'])) {
			$post = $_POST;
			unset($post['method']);
			echo $obj->getMethod($_POST['method'])->invokeArgs($controller, $post);
		} else {
			throw new \Exception('AJAX call error');
		}
	} elseif (preg_match('/^\/adminajax\//', $_SERVER['REQUEST_URI'])) {
		$controller = new \AdminInterface\AdminAjaxController();
		$obj = new ReflectionClass('\AdminInterface\AdminAjaxController');
		if (isset($_POST['method'])) {
			$post = $_POST;
			unset($post['method']);
			echo $obj->getMethod($_POST['method'])->invokeArgs($controller, $post);
		} else {
			throw new \Exception('AJAX call error');
		}
	} elseif ($GLOBALS['container']->get('router')->isAdmin()) {
		$frontcontroller = new AdminInterface();
		$frontcontroller->show();
	} else {
		$frontcontroller = new PageController();
		$frontcontroller->handle();
	}
}
