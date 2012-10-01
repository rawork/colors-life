<?php

namespace Fuga\CMSBundle\Security\Controller;

use Fuga\CMSBundle\Controller\Controller;

class SecurityController extends Controller {
	
	public function loginAction() {
		$message = null;
		$inputUser = $this->get('connection')->escapeStr($this->get('util')->_postVar('_user'));
		$inputPass = $this->get('connection')->escapeStr($this->get('util')->_postVar('_password'));
		$inputRemember = $this->get('connection')->escapeStr($this->get('util')->_postVar('_remember_me'));
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!$inputUser || !$inputPass){
				$message = array(
					'type' => 'error',
					'text' => 'Неверный Логин или Пароль'
				);
			} elseif ($this->get('security')->isServer()) {
				if (!$this->get('security')->login($inputUser, $inputPass, $inputRemember)) {
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
		global $ADMIN_EMAIL;
		$message = null;
		$user = null;
		$inputUser  = $this->get('connection')->escapeStr($this->get('util')->_postVar('_user'));
		$inputEmail = $this->get('connection')->escapeStr($this->get('util')->_postVar('_email'));
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($inputUser) {
				$user = $this->get('connection')->getItem('users_users', "SELECT * FROM users_users WHERE syslogin='".$inputUser."'");
			} 
			if ($inputEmail && !$user) {
				$user = $this->get('connection')->getItem('users_users', "SELECT * FROM users_users WHERE email='".$inputEmail."'");
			}
			if ($user) {
				$key = $this->get('util')->genKey(32);
				$letterText = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
				$letterText .= '------------------------------------------'."\n";
				$letterText .= 'Вы запросили ваши регистрационные данные.'."\n\n";
				$letterText .= 'Ваша регистрационная информация:'."\n";
				$letterText .= 'ID пользователя: '.$user['id']."\n";
				$letterText .= 'Логин: '.$user['syslogin']."\n\n";
				$letterText .= 'Для смены пароля перейдите по следующей ссылке:'."\n";
				$letterText .= 'http://'.$_SERVER['SERVER_NAME'].'/admin/password?key='.$key."\n\n";
				$letterText .= 'Сообщение сгенерировано автоматически.'."\n";
				$this->get('mailer')->send(
					'Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME'],
					nl2br($letterText),
					$user['email']
				);
				$this->get('connection')->execQuery('users_users', "UPDATE users_users SET hashkey='".$key."' WHERE id=".$user['id']);
				$message = array(
					'type' => 'success',
					'text' => 'Новые параметры авторизации отправлены Вам на <b>Электронную почту</b>!'
				);	
			} else {
				$message = array(
					'type' => 'error',
					'text' => '<b>Логин</b> или <b>Email</b> не найдены.'
				);
			}
		}	
		return $this->render('admin/layout.forgot.tpl', array('message' => $message));
	}
	
	public function logoutAction() {
		global $PRJ_REF;
		$this->get('security')->logout();
		if (empty($_SERVER['HTTP_REFERER']) || preg_match('/^\/admin\/logout/', $_SERVER['HTTP_REFERER'])) {
			$uri = $PRJ_REF.'/admin/';
		} else {
			$uri = $_SERVER['HTTP_REFERER'];
		}
		header('Location: '.$uri);
	}
	
	public function passwordAction() {
		global $ADMIN_EMAIL, $PRJ_REF;
		if ($this->get('util')->_getVar('key')) {
			$user = $this->get('connection')->getItem('users_users', "SELECT id,syslogin,email FROM users_users WHERE hashkey='".$this->get('util')->_getVar('key')."'");
			if (!empty($user) && !empty($user['email'])) {
				$newPassword = $this->get('util')->genKey();
				$this->get('connection')->execQuery('users_users', "UPDATE users_users SET syspassword=MD5('".$newPassword."'), hashkey='' WHERE hashkey='".$this->get('util')->_getVar('key')."'");
				$letterText = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
				$letterText .= '------------------------------------------'."\n";
				$letterText .= 'Вы запросили ваши регистрационные данные.'."\n";
				$letterText .= 'Ваша регистрационная информация:'."\n";
				$letterText .= 'ID пользователя: '.$user['id']."\n";
				$letterText .= 'Логин: '.$user['syslogin']."\n";
				$letterText .= 'Пароль: '.$newPassword."\n\n";
				$letterText .= 'Сообщение сгенерировано автоматически.'."\n";
				$this->get('mailer')->send(
					'Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME'],
					nl2br($letterText),
					$user['email']
				);
			}
		}
		header('location: '.$PRJ_REF.'/admin/');
	}
	
}
