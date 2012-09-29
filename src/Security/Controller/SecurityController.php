<?php

namespace Security\Controller;

use Common\AbstractController;
use Common\Mail;

class SecurityController extends AbstractController {
	
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
				$mail = new Mail();
				$mail->From($ADMIN_EMAIL);
				$mail->Subject('Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME']);
				$body = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
				$body .= '------------------------------------------'."\n";
				$body .= 'Вы запросили ваши регистрационные данные.'."\n\n";
				$body .= 'Ваша регистрационная информация:'."\n";
				$body .= 'ID пользователя: '.$user['id']."\n";
				$body .= 'Логин: '.$user['syslogin']."\n\n";
				$body .= 'Для смены пароля перейдите по следующей ссылке:'."\n";
				$body .= 'http://'.$_SERVER['SERVER_NAME'].'/admin/password?key='.$key."\n\n";
				$body .= 'Сообщение сгенерировано автоматически.'."\n";
				$mail->Body($body, 'UTF-8');
				$mail->To($user['email']);
				$mail->Send();
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
				$mail = new Mail();
				$mail->From($ADMIN_EMAIL);
				$mail->Subject('Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME']);
				$message = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
				$message .= '------------------------------------------'."\n";
				$message .= 'Вы запросили ваши регистрационные данные.'."\n";
				$message .= 'Ваша регистрационная информация:'."\n";
				$message .= 'ID пользователя: '.$user['id']."\n";
				$message .= 'Логин: '.$user['syslogin']."\n";
				$message .= 'Пароль: '.$newPassword."\n\n";
				$message .= 'Сообщение сгенерировано автоматически.'."\n";
				$mail->Body($message, 'UTF-8');
				$mail->To($user['email']);
				$mail->Send();
				
			}
		}
		header('location: '.$PRJ_REF.'/admin/');
	}
	
}
