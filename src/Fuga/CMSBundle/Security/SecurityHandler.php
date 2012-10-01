<?php

namespace Fuga\CMSBundle\Security;
	
class SecurityHandler {
	public $user;
	public $operation;
	
	public function isAuthenticated() {
		$this->user = $this->get('util')->_sessionVar('ukey');
		if (empty($this->user)) {
			$this->checkUser();
		}
		return !empty($this->user);
	}
	
	public function isSecuredArea() {
		global $AUTH_LOCK_PROJECT;
		return $AUTH_LOCK_PROJECT == 'Y' || (preg_match('/^\/admin\//', $_SERVER['REQUEST_URI']) && !preg_match('/^\/admin\/(logout|forgot|password)/', $_SERVER['REQUEST_URI']));
	}
	
	public function getCurrentUser() {
		$user = $this->get('connection')->getItem('users_users',
			"SELECT uu.*, ug.rules FROM users_users uu LEFT JOIN users_groups ug ON uu.group_id=ug.id WHERE uu.syslogin='".$this->get('util')->_sessionVar('user')."'");
		unset($user['password']);
		return $user;
	}

	private function checkUser() {
		if (!empty($_COOKIE['userkey'])) {
			if ($_COOKIE['userkey'] == md5(_DEV_PASS.substr(_DEV_USER, 0, 3).$_SERVER['REMOTE_ADDR'])) {
				$user = array('syslogin' => _DEV_USER);
			} else {
				$user = $this->get('connection')->getItem('users_users',
					"SELECT syslogin FROM users_users
					WHERE MD5(CONCAT(syspassword, SUBSTRING(syslogin, 1, 3),'".$_SERVER['REMOTE_ADDR']."')) = '".$_COOKIE['userkey']."'"
				);
			}
			if (count($user)) {
				$_SESSION['user'] = $user['syslogin'];
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
			$user = array('syslogin' => $inputUser);
		} else {
			$user = $this->get('connection')->getItem('users_users', "SELECT syslogin FROM users_users WHERE syslogin='$inputUser' AND syspassword='".$inputPass."' AND is_active='on'");
		}
		if ($user){
			$_SESSION['user'] = $user['syslogin'];
			$_SESSION['ukey'] = $this->userHash($inputUser, $inputPass);
			if ($isRemember) {
				setcookie('userkey', $this->userHash($inputUser, $inputPass), time()+3600*24*1000);
			}
			header('Location: '.$_SERVER['HTTP_REFERER']);
		} else {
			return false;
		}
		
	}
	
	private function userHash($user, $pass) {
		return md5($pass.substr($user, 0, 3).$_SERVER['REMOTE_ADDR']);
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
	
	public function get($name) {
		global $container;
		return $container->get($name);
	}
}
