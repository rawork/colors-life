<?php
    if (preg_match('/^\/(adminnew|_profiler)\//', $_SERVER['REQUEST_URI'])) {
		require('app_dev.php');
	} elseif (preg_match('/^\/secureimage\//', $_SERVER['REQUEST_URI'])) {
		include_once($_SERVER['DOCUMENT_ROOT'].'/admin/lib/Security/Captcha/KCaptcha.php');
		session_start();
		$captcha = new KCaptcha();
		$_SESSION['captchaHash'] = md5($captcha->getKeyString().'FWK');
	} else {	
		
		require_once('app/init.php');
		
		if (preg_match('/^\/notice\/[\d]{6}$/', $_SERVER['REQUEST_URI'])) {
			$container->get('smarty')->assign('order', str_replace('/notice/', '', $_SERVER['REQUEST_URI']));
			$container->get('smarty')->display('page.notice.tpl');
		} elseif (preg_match('/^\/ajax\//', $_SERVER['REQUEST_URI'])) {
			$controller = new \Controller\AjaxController();
			$obj = new ReflectionClass('\Controller\AjaxController');
			if (isset($_POST['method'])) {
				$post = $_POST;
				unset($post['method']);
				echo $obj->getMethod($_POST['method'])->invokeArgs($controller, $post);
			} else {
				throw new Exception('AJAX call error');
			}
		} elseif (preg_match('/^\/adminajax\//', $_SERVER['REQUEST_URI'])) {
			$controller = new \AdminInterface\AdminAjaxController();
			$obj = new ReflectionClass('\AdminInterface\AdminAjaxController');
			if (isset($_POST['method'])) {
				$post = $_POST;
				unset($post['method']);
				echo $obj->getMethod($_POST['method'])->invokeArgs($controller, $post);
			} else {
				throw new Exception('AJAX call error');
			}
		} elseif (preg_match('/^\/admin\//', $_SERVER['REQUEST_URI'])) {
			$frontcontroller = new \AdminInterface\AdminInterface();
			$frontcontroller->show();
		} else {
			$frontcontroller = new \Common\PageController();
			$frontcontroller->show();
		}
	}
