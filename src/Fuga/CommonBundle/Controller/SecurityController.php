<?php

namespace Fuga\CommonBundle\Controller;

class SecurityController extends Controller {
	
	public function loginAction() {
		$message = null;
		$login = $this->get('util')->_postVar('_user');
		$password = $this->get('util')->_postVar('_password');
		$is_remember = $this->get('util')->_postVar('_remember_me');
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!$login || !$password){
				$message = array(
					'type' => 'error',
					'text' => 'Неверный Логин или Пароль'
				);
			} elseif ($this->get('security')->isServer()) {
				if (!$this->get('security')->login($login, $password, $is_remember)) {
					$message = array (
						'type' => 'error',
						'text' => 'Неверный Логин или Пароль'
					);	
				}
			}
		} 
	
		if ($this->get('util')->_getVar('error')) {
			$message = array (
				'type' => 'error',
				'text' => $this->get('util')->_getVar('error')
			);
		}
		if ($this->get('util')->_getVar('ok')) {
			$message = array (
				'type' => 'ok',
				'text' => $this->get('util')->_getVar('ok')
			);
		}
		return $this->render('admin/layout.login.tpl', array('message' => $message));
	}
	
	public function forgotAction() {
		$message = null;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$login  = $this->get('util')->_postVar('_user');
			$sql = "SELECT id,login,email FROM user_user WHERE login= :login OR email = :login ";
			$stmt = $this->get('connection1')->prepare($sql);
			$stmt->bindValue("login", $login);
			$stmt->execute();
			$user = $stmt->fetch();
			if ($user) {
				$key = $this->get('util')->genKey(32);
				$this->get('connection1')->update(
						'user_user', 
						array('hashkey' => $key), 
						array('id' => $user['id'])
				);
				$letterText = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
				$letterText .= '------------------------------------------'."\n";
				$letterText .= 'Вы запросили ваши регистрационные данные.'."\n\n";
				$letterText .= 'Ваша регистрационная информация:'."\n";
				$letterText .= 'ID пользователя: '.$user['id']."\n";
				$letterText .= 'Логин: '.$user['login']."\n\n";
				$letterText .= 'Для смены пароля перейдите по следующей ссылке:'."\n";
				$letterText .= 'http://'.$_SERVER['SERVER_NAME'].'/admin/password?key='.$key."\n\n";
				$letterText .= 'Сообщение сгенерировано автоматически.'."\n";
				$this->get('mailer')->send(
					'Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME'],
					nl2br($letterText),
					$user['email']
				);
				
				$message = array(
					'type' => 'success',
					'text' => 'Новые параметры авторизации отправлены Вам на <b>Электронную почту</b>!'
				);	
			} else {
				$message = array(
					'type' => 'error',
					'text' => 'Пользователь не найден'
				);
			}
		}
		
		return $this->render('admin/layout.forgot.tpl', array('message' => $message));
	}
	
	public function logoutAction() {
		$this->get('security')->logout();
		if (empty($_SERVER['HTTP_REFERER']) || preg_match('/^\/admin\/logout/', $_SERVER['HTTP_REFERER'])) {
			$uri = '/admin/';
		} else {
			$uri = $_SERVER['HTTP_REFERER'];
		}
		header('location: '.$uri);
		exit;
	}
	
	public function passwordAction() {
		$key = $this->get('util')->_getVar('key');
		if ($key) {
			$sql = "SELECT id,login,email FROM user_user WHERE hashkey= :key ";
			$stmt = $this->get('connection1')->prepare($sql);
			$stmt->bindValue("key", $key);
			$stmt->execute();
			$user = $stmt->fetch();
			if ($user && !empty($user['email'])) {
				$password = $this->get('util')->genKey();
				$this->get('connection1')->update(
						'user_user', 
						array('hashkey' => '', 'password' => md5($password)), 
						array('id' => $user['id'])
				);
				$letterText = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
				$letterText .= '------------------------------------------'."\n";
				$letterText .= 'Вы запросили ваши регистрационные данные.'."\n";
				$letterText .= 'Ваша регистрационная информация:'."\n";
				$letterText .= 'ID пользователя: '.$user['id']."\n";
				$letterText .= 'Логин: '.$user['login']."\n";
				$letterText .= 'Пароль: '.$password."\n\n";
				$letterText .= 'Сообщение сгенерировано автоматически.'."\n";
				$this->get('mailer')->send(
					'Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME'],
					nl2br($letterText),
					$user['email']
				);
			}
		}
		header('location: /admin/');
	}
	
}
