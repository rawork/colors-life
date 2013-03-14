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
		global $PROJECT_LOCKED;
		return $PROJECT_LOCKED == 'Y' || 
			(preg_match('/^\/admin\//', $_SERVER['REQUEST_URI']) && !preg_match('/^\/admin\/(logout|forgot|password)/', $_SERVER['REQUEST_URI'])) ||
			(preg_match('/^\/bundles\/admin\/editor\//', $_SERVER['REQUEST_URI']));
	}
	
	public function getUser($login) {
		$sql = "
			SELECT u.*, g.rules FROM user_user u 
			JOIN user_group g ON u.group_id=g.id 
			WHERE u.login = :login OR u.email = :login LIMIT 1";
		$stmt = $this->container->get('connection1')->prepare($sql);
		$stmt->bindValue("login", $login);
		$stmt->execute();
		$user = $stmt->fetch();
		unset($user['password']);
		return $user;
	}

	private function checkUser() {
		if (!empty($_COOKIE['userkey'])) {
			if ($_COOKIE['userkey'] == md5(_DEV_PASS.substr(_DEV_USER, 0, 3).$_SERVER['REMOTE_ADDR'])) {
				$user = array('login' => _DEV_USER);
			} else {
				$sql = "SELECT login FROM user_user WHERE MD5(CONCAT(password, SUBSTRING(login, 1, 3), :addr )) = :key LIMIT 1";
				$stmt = $this->container->get('connection1')->prepare($sql);
				$stmt->bindValue("addr", $_SERVER['REMOTE_ADDR']);
				$stmt->bindValue("key", $_COOKIE['userkey'].'sff');
				$stmt->execute();
				$user = $stmt->fetch();
			}
			if ($user) {
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

	public function login($login, $password, $isRemember = false ) {
		$password = md5($password);
		if ($login == _DEV_USER && $password == _DEV_PASS) {
			$user = array('login' => $login);
		} else {
			$sql = "SELECT login FROM user_user WHERE login= :login AND password= :password AND is_active=1 LIMIT 1";
			$stmt = $this->container->get('connection1')->prepare($sql);
			$stmt->bindValue("login", $login);
			$stmt->bindValue("password", $password);
			$stmt->execute();
			$user = $stmt->fetch();
		}
		if ($user){
			$_SESSION['user'] = $user['login'];
			$_SESSION['ukey'] = $this->userHash($login, $password);
			if ($isRemember) {
				setcookie('userkey', $this->userHash($login, $password), time()+3600*24*1000);
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
