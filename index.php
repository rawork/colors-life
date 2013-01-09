<?php

use Fuga\AdminBundle\AdminInterface;
use Fuga\AdminBundle\Controller\AdminAjaxController;
use Fuga\CMSBundle\Security\Captcha\KCaptcha;
use Fuga\CMSBundle\Controller\PageController;
use Fuga\PublicBundle\Controller\AjaxController;

if (preg_match('/^\/secureimage\//', $_SERVER['REQUEST_URI'])) {
	include_once($_SERVER['DOCUMENT_ROOT'].'/src/Fuga/CMSBundle/Security/Captcha/KCaptcha.php');
	session_start();
	$captcha = new KCaptcha();
	$_SESSION['captchaHash'] = md5($captcha->getKeyString().'FWK');
	exit;
} else {	
	
//	require_once('app_dev.php');
//	exit;
	
	require_once('app/init.php');
	
	if (preg_match('/^\/ajax\//', $_SERVER['REQUEST_URI'])) {
		try {
			$controller = new AjaxController();
			$obj = new \ReflectionClass('Fuga\PublicBundle\Controller\AjaxController');
			$post = $_POST;
			unset($post['method']);
			header("HTTP/1.0 200 OK");
			echo $obj->getMethod($_POST['method'])->invokeArgs($controller, $post);
		} catch (\Exception $e) {
			$container->get('log')->write(json_encode($_POST));
			$container->get('log')->write($e->getMessage());
			$container->get('log')->write('Trace% '.$e->getTraceAsString());
			echo '';
		}	
	} elseif (preg_match('/^\/adminajax\//', $_SERVER['REQUEST_URI'])) {
		try {
			$controller = new AdminAjaxController();
			$obj = new \ReflectionClass('Fuga\AdminBundle\Controller\AdminAjaxController');
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
