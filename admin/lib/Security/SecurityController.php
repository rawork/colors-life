<?php

namespace Security;

use \Common\Controller;

class SecurityController extends Controller {
	
	public function loginAction() {
		global $security;
		$message = null;
		$inputUser = $this->get('connection')->escapeStr($this->get('util')->_postVar('_user'));
		$inputPass = $this->get('connection')->escapeStr($this->get('util')->_postVar('_password'));
		$inputRemember = $this->get('connection')->escapeStr($this->get('util')->_postVar('_remember_me'));
		if (isset($_POST['_user']) && isset($_POST['_password'])) {
			if (!$inputUser || !$inputPass){
				$message['type'] = 'error';
				$message['text'] = 'Введено пустое значение пользователя или пароля';
			} elseif ($security->isServer()) {
				$message = $security->login($inputUser, $inputPass, $inputRemember);
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
		$this->get('smarty')->assign('message', $this->get('util')->showMsg($message));
		return $this->get('smarty')->fetch('admin/form.login.tpl');
	}
	
	public function forgotAction() {
		global $MAIN_EMAIL;
		$message = null;
		$inputUser  = $this->get('connection')->escapeStr($this->get('util')->_postVar('_user'));
		$inputEmail = $this->get('connection')->escapeStr($this->get('util')->_postVar('_email'));
		if ($inputUser || $inputEmail) {
			$user = null;
			if ($inputUser) {
				$user = $this->get('connection')->getItem('users_users', "SELECT * FROM users_users WHERE syslogin='".$inputUser."'");
				if (!$user) {
					$message = array(
						'type' => 'error',
						'text' => 'Не найден пользователь с указанным <b>логином</b>!'
					);
				}
			} 
			if ($inputEmail && !$user) {
				$user = $this->get('connection')->getItem('users_users', "SELECT * FROM users_users WHERE email='".$inputEmail."'");
				if (!$user) {
					$message = array(
						'type' => 'error',
						'text' => 'Не найден пользователь с указанным <b>e-mail</b>!'
					);
				}
			}
			if ($user) {
				$key = $this->get('util')->genKey(32);
				$mail = new \Common\Mail();
				$mail->From($MAIN_EMAIL);
				$mail->Subject('Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME']);
				$message = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
				$message .= '------------------------------------------'."\n";
				$message .= 'Вы запросили ваши регистрационные данные.'."\n\n";
				$message .= 'Ваша регистрационная информация:'."\n";
				$message .= 'ID пользователя: '.$user['id']."\n";
				$message .= 'Логин: '.$user['syslogin']."\n\n";
				$message .= 'Для смены пароля перейдите по следующей ссылке:'."\n";
				$message .= 'http://'.$_SERVER['SERVER_NAME'].'/admin/password?key='.$key."\n\n";
				$message .= 'Сообщение сгенерировано автоматически.'."\n";
				$mail->Body($message, 'UTF-8');
				$mail->To($user['email']);
				$mail->Send();
				$this->get('connection')->execQuery('users_users', "UPDATE users_users SET hashkey='".$key."' WHERE id=".$user['id']);
				$message = array(
					'type' => 'ok',
					'text' => 'Новые параметры авторизации отправлены Вам на <b>e-mail</b>!'
				);	
			}
		}
		$this->get('smarty')->assign('message', $this->get('util')->showMsg($message));
		return $this->get('smarty')->fetch('admin/form.forgot.tpl');
	}
	
	public function logoutAction() {
		global $security, $PRJ_REF;
		$security->logout();
		if (empty($_SERVER['HTTP_REFERER']) || preg_match('/^\/admin\/logout/', $_SERVER['HTTP_REFERER'])) {
			$uri = $PRJ_REF.'/admin/';
		} else {
			$uri = $_SERVER['HTTP_REFERER'];
		}
		header('Location: '.$uri);
	}
	
	public function passwordAction() {
		global $MAIN_EMAIL;
		if ($this->get('util')->_getVar('key')) {
			$user = $this->get('connection')->getItem('users_users', "SELECT id,syslogin,email FROM users_users WHERE hashkey='".$this->get('util')->_getVar('key')."'");
			if (!empty($user) && !empty($user['email'])) {
				$newPassword = $this->get('util')->genKey();
				$this->get('connection')->execQuery('users_users', "UPDATE users_users SET syspassword=MD5('".$newPassword."'), hashkey='' WHERE hashkey='".$this->get('util')->_getVar('key')."'");
				$mail = new \Common\Mail();
				$mail->From($MAIN_EMAIL);
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
		header('location: '.$GLOBALS['PRJ_REF'].'/admin/');
	}
	
}
