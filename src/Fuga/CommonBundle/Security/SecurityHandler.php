<?php

namespace Fuga\CommonBundle\Security;
	
class SecurityHandler {
	private $user;
	private $container;

	public function __construct($container) {
		$this->container = $container;
	}
	
	public function isAuthenticated() {
		$this->user = $this->container->get('util')->_sessionVar('ukey');
		if (empty($this->user)) {
			$this->checkUser();
		}
		return !empty($this->user);
	}
	
	public function isSecuredArea() {
		global $AUTH_LOCK_PROJECT;
		return $AUTH_LOCK_PROJECT == 'Y' || 
			(preg_match('/^\/admin\//', $_SERVER['REQUEST_URI']) && !preg_match('/^\/admin\/(logout|forgot|password)/', $_SERVER['REQUEST_URI'])) ||
			(preg_match('/^\/bundles\/admin\/editor\//', $_SERVER['REQUEST_URI']));
	}
	
	public function getCurrentUser() {
		$user = $this->container->get('connection')->getItem('user',
			"SELECT uu.*, ug.rules FROM user_user uu LEFT JOIN user_group ug ON uu.group_id=ug.id WHERE uu.login='".$this->get('util')->_sessionVar('user')."'");
		unset($user['password']);
		return $user;
	}

	private function checkUser() {
		if (!empty($_COOKIE['userkey'])) {
			if ($_COOKIE['userkey'] == md5(_DEV_PASS.substr(_DEV_USER, 0, 3).$_SERVER['REMOTE_ADDR'])) {
				$user = array('login' => _DEV_USER);
			} else {
				$user = $this->container->get('connection')->getItem('user',
					"SELECT login FROM user_user
					WHERE MD5(CONCAT(password, SUBSTRING(login, 1, 3),'".$_SERVER['REMOTE_ADDR']."')) = '".$_COOKIE['userkey']."'"
				);
			}
			if (count($user)) {
				$_SESSION['user'] = $user['login'];
				$this->user = $_SESSION['ukey'] = $_COOKIE['userkey'];
				setcookie('userkey', $_COOKIE['userkey'], time()+3600*24*1000);	
			}
		}
	}

	public function logout() {
		unset($_SESSION['user']);
		unset($_SESSION['ukey']);
		setcookie('userkey', '', time() - 3600);
		session_destroy();
	}

	public function login($inputUser, $inputPass, $isRemember = false ) {
		$inputPass = md5($inputPass);
		if ($inputUser == _DEV_USER && $inputPass == _DEV_PASS) {
			$user = array('login' => $inputUser);
		} else {
			$user = $this->container->get('connection')->getItem('user', "SELECT login FROM user_user WHERE login='$inputUser' AND password='".$inputPass."' AND is_active=1");
		}
		if ($user){
			$_SESSION['user'] = $user['login'];
			$_SESSION['ukey'] = $this->userHash($inputUser, $inputPass);
			if ($isRemember) {
				setcookie('userkey', $this->userHash($inputUser, $inputPass), time()+3600*24*1000);
			}
			header('Location: '.$_SERVER['HTTP_REFERER']);
		} else {
			return false;
		}
		
	}
	
	private function userHash($login, $password) {
		return md5($password.substr($login, 0, 3).$_SERVER['REMOTE_ADDR']);
	}

	public function isAdmin() {
		return $_SESSION['user'] == 'admin';
	}

	public function isDeveloper() {
		return $_SESSION['user'] == 'dev';
	}

	public function isSuperuser() {
		return $this->isAdmin() || $this->isDeveloper();
	}

	public function isLocal() {
		return empty($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] == gethostbyname($_SERVER['SERVER_NAME']);
	}

	public function isServer() {
		return isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']);
	}
	
}
