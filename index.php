<?php

use Fuga\AdminBundle\AdminInterface;
use Fuga\CommonBundle\Security\Captcha\KCaptcha;
use Fuga\CommonBundle\Controller\PageController;

if (preg_match('/^\/secureimage\//', $_SERVER['REQUEST_URI'])) {
	include_once($_SERVER['DOCUMENT_ROOT'].'/src/Fuga/CommonBundle/Security/Captcha/KCaptcha.php');
	session_start();
	$captcha = new KCaptcha();
	$_SESSION['captchaHash'] = md5($captcha->getKeyString().'FWK');
	exit;
} else {	
	require_once('app/init.php');
	
	if (preg_match('/^\/ajax\//', $_SERVER['REQUEST_URI'])) {
		try {
			$controller = $GLOBALS['container']->createController('Fuga:Public:Ajax');
			$obj = new \ReflectionClass($GLOBALS['container']->getControllerClass('Fuga:Public:Ajax'));
			$post = $_POST;
			unset($post['method']);
			echo $obj->getMethod($_POST['method'])->invokeArgs($controller, $post);
		} catch (\Exception $e) {
			$container->get('log')->write(json_encode($_POST));
			$container->get('log')->write($e->getMessage());
			$container->get('log')->write('Trace% '.$e->getTraceAsString());
			echo '';
		}	
	} elseif (preg_match('/^\/adminajax\//', $_SERVER['REQUEST_URI'])) {
		try {
			$controller = $GLOBALS['container']->createController('Fuga:Admin:AdminAjax');
			$obj = new \ReflectionClass($GLOBALS['container']->getControllerClass('Fuga:Admin:AdminAjax'));
			$post = $_POST;
			unset($post['method']);
			echo $obj->getMethod($_POST['method'])->invokeArgs($controller, $post);
		} catch (\Exception $e) {
			$container->get('log')->write(json_encode($_POST));
			$container->get('log')->write($e->getMessage());
			$container->get('log')->write('Trace% '.$e->getTraceAsString());
			echo '';
		}
	} elseif ($GLOBALS['container']->get('router')->isAdmin()) {
		$frontcontroller = new AdminInterface();
		$frontcontroller->handle();
	} else {
		$frontcontroller = new PageController();
		$frontcontroller->handle();
	}
}
