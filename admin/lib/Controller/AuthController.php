<?php

namespace Controller;

use Common\Mail;

if (!isset($_SESSION['deliveryAddress'])) {
	$_SESSION['deliveryAddress'] = '';
	$_SESSION['deliveryPhone'] = '';
	$_SESSION['deliveryPhoneAdd'] = '';
	$_SESSION['deliveryPerson'] = '';
}
	
class AuthController extends Controller {

	public $user;

	private $currentUser;

	private $months = array (
		'01' => 'января',
		'02' => 'февраля',
		'03' => 'марта',
		'04' => 'апреля',
		'05' => 'мая',
		'06' => 'июня',
		'07' => 'июля',
		'08' => 'августа',
		'09' => 'сетября',
		'10' => 'октября',
		'11' => 'ноября',
		'12' => 'декабря'
	);

	private $info = array(
		'send_password' => 'Новый пароль выслан вам на электронный адрес',
		'change_password' => 'Пароль успешно изменен!'
	);

	private $errors = array(
		'no_user' => 'С указанным эл. адресом нет зарегистрированных пользователей',
		'db_error' => 'Ошибка обработки запроса. Обратитесь к администратору сайта.',
		'incorrect_password' => 'Неправильный пароль! Для изменения пароля необходимо ввести текущий пароль!',
		'incorrect_securecode' => 'Вы неправильно ввели цифры указанные на картинке'
	);

	function __construct() {
		parent::__construct('auth');
		$this->initializeUser();
	}

	public function initializeUser() {
		$this->user = $this->get('container')->getItem('auth_users', "session_id='".session_id()."'");
		$this->user = count($this->user) ? $this->user : null;
		$this->currentUser = $this->user;
		if (!$_SESSION['deliveryAddress']) {
			$_SESSION['deliveryAddress'] = $this->getAddress();
			$_SESSION['deliveryPhone'] = $this->getPhone();
			$_SESSION['deliveryPerson'] = $this->getPersonName();
		}
	}

	public function getUser() {
		unset($this->currentUser['password']);
		return $this->currentUser;
	}

	public function getPersonName() {
		if ($this->currentUser) {
			return $this->currentUser['name'].($this->currentUser['lastname'] ? ' '.$this->currentUser['lastname'] : '');
		}
		return null;
	}

	public function getPhone() {
		if ($this->currentUser) {
			return $this->currentUser['phone'];
		}
	}

	public function getAddress() {
		if ($this->currentUser) {
			return $this->currentUser['address'];
		}
	}

	public function getLogin() {
		if ($this->currentUser) {
			return $this->currentUser['email'];
		}
	}

	private function _getLogout() {
		if ($this->get('container')->getTable('auth_users')->update("session_id='' WHERE login='".$this->user['login']."'")) {
			unset($_SESSION['deliveryAddress']);
			unset($_SESSION['deliveryPhone']);
			unset($_SESSION['deliveryPhoneAdd']);
			unset($_SESSION['deliveryPerson']);
			header('location: /');
		} else {
			return $this->errors['db_error'];
		}

	}

	private function _getInfoForm() {
		if ($this->get('util')->_postVar('processInfo')) {
			$this->get('smarty')->assign('error_message', $this->_processInfoForm());
		}

		$aUser = $this->getUser();
		$aUser['birthday'] = explode('.', $aUser['birthday']);

		$this->get('smarty')->assign('cabinetMenu', $this->_getMenu());
		$this->get('smarty')->assign('userInfo', $aUser);
		$this->get('smarty')->assign('Months', $this->months);

		return $this->getTpl('service/auth/'.$this->lang.'/info.form');;
	}

	private function _processInfoForm() {
		$aErrors = array();

		$aUser = $this->getUser();

		$sLogin = $this->get('util')->_postVar('userEmail');
		$sUserName = $this->get('util')->_postVar('userFName');
		$sUserLName = $this->get('util')->_postVar('userLName');
		$sPhone = $this->get('util')->_postVar('userPhone');
		$sAddress = $this->get('util')->_postVar('userAddress');
		$sGender = $this->get('util')->_postVar('userGender');
		$sDay = $this->get('util')->_postVar('userDay');
		$sMonth = $this->get('util')->_postVar('userMonth');
		$sYear = $this->get('util')->_postVar('userYear');
		$sBirthday = '';
		if ($sDay && $sMonth && $sYear) {
			$sBirthday = $sDay .'.'. $sMonth .'.'. $sYear;
		}
		$sUpdate = "login='$sLogin'";
		$sUpdate .= ",email='$sLogin'";
		$sUpdate .= ",name='$sUserName'";
		$sUpdate .= ",lastname='$sUserLName'";
		$sUpdate .= ",phone='$sPhone'";
		$sUpdate .= ",address='$sAddress'";
		$sUpdate .= ",gender='$sGender'";
		$sUpdate .= ",birthday='$sBirthday'";

		if (
			($sLogin != $aUser['email']) &&
			$this->get('container')->getTable('auth_users')->selectWhere("login='".$sLogin."' OR email='".$sLogin."'") &&
			$this->get('container')->getTable('auth_users')->getNumRows()
		) {
			$this->get('smarty')->assign('login', $sLogin);
			$aErrors[] = $this->getTpl('service/auth/'.$this->lang.'/error.userpresent');
		} else {
			if ($this->get('container')->getTable('auth_users')->update($sUpdate.", change_date = NOW() WHERE email='".$aUser['email']."'")) {
				header('location: /cabinet/');
			} else {
				$aErrors[] = $this->errors['db_error'];
			}
		}
		return implode('errors', $aErrors);
	}

	private function _getLoginForm() {
		var_dump($this->lang);
		if ($this->get('util')->_postVar('processLogin')) {
			$this->get('smarty')->assign('error_message', $this->_processLoginForm());
		}
		return $this->getTpl('service/auth/'.$this->lang.'/login.form');
	}

	private function _processLoginForm() {
		$aErrors = array();
		$sFromPage = $this->get('util')->_postVar('fromPage');
		$sLogin = $this->get('util')->_postVar('login');
		$sPassword = $this->get('util')->_postVar('password');
		$t = $this->get('container')->getTable('auth_users');
		if ($aUser = $this->get('container')->getItem('auth_users', "email='$sLogin' OR login='$sLogin'")) {
			if ($aUser['password'] == $sPassword) {
				if ($t->update("session_id='".session_id()."', logindate=NOW() WHERE login='$sLogin' OR email='$sLogin'")) {
					$this->get('smarty')->assign('login', $sLogin);
					header('location: '.($sFromPage ? $sFromPage : '/'));
				} else {
					$aErrors[] = $this->errors['db_error'];
				}
			} else {
				$aErrors[] = $this->getTpl('service/auth/'.$this->lang.'/error.pass');
			}
		} else {
			$this->get('smarty')->assign('login', $sLogin);
			$aErrors[] = $this->getTpl('service/auth/'.$this->lang.'/error.notreg');
		}
		return implode('<br>', $aErrors);
	}

	private function _getRegistrationForm() {
		if ($this->get('util')->_postVar('processRegistration')) {
			$this->get('smarty')->assign('error_message', $this->_processRegistrationForm());
		}
		return $this->getTpl('service/auth/'.$this->lang.'/registration.form');
	}

	private function _processRegistrationForm() {
		$aErrors = array();
		$sFromPage = $this->get('util')->_postVar('fromPage');
		$sLogin = $this->get('util')->_postVar('newUserEmail');
		$sPassword = $this->get('util')->_postVar('newUserPassword');
		$sPasswordConfirm = $this->get('util')->_postVar('newUserPasswordConfirm');
		$sUserName = $this->get('util')->_postVar('newUserFName');
		$sUserLName = $this->get('util')->_postVar('newUserLName');
		$sPhone = $this->get('util')->_postVar('newUserPhone');
		$t = $this->get('container')->getTable('auth_users');
		if($this->get('util')->_sessionVar('captchaHash') != md5($this->get('util')->_postVar('captcha').__CAPTCHA_HASH)){
			$aErrors[] = $this->getTpl('service/auth/'.$this->lang.'/error.securecode');
		} else {
			if (
				$t->selectWhere("login='".$sLogin."' OR email='".$sLogin."'") &&
				$t->getNumRows()
			) {
				$this->get('smarty')->assign('login', $sLogin);
				$aErrors[] = $this->getTpl('service/auth/'.$this->lang.'/error.userpresent');
			} else {
				$sUpdate = "password='$sPassword'";
				$sUpdate .= ",name='$sUserName'";
				$sUpdate .= ",lastname='$sUserLName'";
				$sUpdate .= ",phone='$sPhone'";
				if (
					$t->insert('login,email', "'".$sLogin."','".$sLogin."'") &&
					$t->update($sUpdate.", credate = NOW(), change_date = NOW() WHERE email='".$sLogin."'")
				) {
					if ($sLogin) {
						$this->get('smarty')->assign('Name', $sUserName);
						$this->get('smarty')->assign('Lastname', $sUserLName);
						$this->get('smarty')->assign('Login', $sLogin);
						$this->get('smarty')->assign('Password', $sPassword);

						$this->_sendMail(
							'Регистрация в магазине Цвета жизни',
							$this->get('smarty')->fetch('service/auth/'.$this->lang.'/registration.mail.tpl'),
							explode(',', $sLogin.','.$this->params['email'])
						);
					}
					if ($t->update("session_id='".session_id()."' WHERE login='$sLogin' OR email='$sLogin'")) {
						header('location: /cabinet/');
					} else {
						$aErrors[] = $this->errors['db_error'];
					}
				} else {
					$aErrors = $this->errors['db_error'];
				}
			}
		}
		return implode('<br>', $aErrors);
	}

	private function _getMenu() {
		return $this->get('smarty')->fetch('service/auth/'.$this->lang.'/menu.tpl');
	}

	private function _getOrders() {
		$this->get('smarty')->assign('cabinetMenu', $this->_getMenu());
		return $this->get('smarty')->fetch('service/auth/'.$this->lang.'/orders.tpl');
	}

	private function _getPasswordForm() {
		if ($this->get('util')->_postVar('processPassword')) {
			$aMessages = $this->_processPasswordForm();
			$this->get('smarty')->assign('error_message', implode('<br>', $aMessages['errors']));
			$this->get('smarty')->assign('info_message', implode('<br>', $aMessages['info']));
		}
		$this->get('smarty')->assign('cabinetMenu', $this->_getMenu());
		return $this->getTpl('service/auth/'.$this->lang.'/password.form');
	}

	private function _processPasswordForm() {
		$aMessages = array(
			'info' => array(),
			'errors' => array()
		);

		$t = $this->get('container')->getTable('auth_users');

		$aUser = $this->getUser();
		$sLogin = $aUser['email'];
		$sOldPassword = $this->get('util')->_postVar('passwd');
		$sNewPassword = $this->get('util')->_postVar('newpasswd');

		if ($aUser['password'] == $sOldPassword) {
			$sUpdate = "password='$sNewPassword'";
			if ($t->update($sUpdate.", change_date = NOW() WHERE email='".$sLogin."'")) {
				$this->get('smarty')->assign('Login', $sLogin);
				$this->get('smarty')->assign('NewPassword', $sNewPassword);
				$this->_sendMail(
					'Новый пароль в магазине Цвета жизни',
					$this->get('smarty')->fetch('service/auth/'.$this->lang.'/password.mail.tpl'),
					array($sLogin)
				);
				$aMessages['info'][] = $this->info['change_password'];
			}
		} else {
			$aMessages['errors'][] = $this->errors['incorrect_password'];
		}

		return $aMessages;
	}

	private function _getForgetForm() {
		if ($this->get('util')->_postVar('processForget')) {
			$aMessages = $this->_processForgetForm();
			$this->get('smarty')->assign('error_message', implode('<br>', $aMessages['errors']));
			$this->get('smarty')->assign('info_message', implode('<br>', $aMessages['info']));
		}
		return $this->getTpl('service/auth/'.$this->lang.'/forget.form');
	}

	private function _processForgetForm() {
		$aMessages = array(
			'info' => array(),
			'errors' => array()
		);
		$t = $this->get('container')->getTable('auth_users');
		if ($this->get('util')->_sessionVar('captchaHash') != md5($this->get('util')->_postVar('captcha').__CAPTCHA_HASH)) {
			$aMessages['errors'][] = $this->errors['incorrect_securecode'];
		} else {
			$sLogin = $this->get('util')->_postVar('login');
			if ($aUser = $this->get('container')->getItem('auth_users', "email='$sLogin'")) {
				$sNewPassword = $this->get('util')->genKey(6);
				$sUpdate = "password='$sNewPassword'";
				if ($t->update($sUpdate.", change_date = NOW() WHERE email='".$sLogin."'")) {
					$this->get('smarty')->assign('Login', $sLogin);
					$this->get('smarty')->assign('NewPassword', $sNewPassword);
					$this->_sendMail(
						'Восстановление пароля в магазине Цвета жизни',
						$this->get('smarty')->fetch('service/auth/'.$this->lang.'/forget.mail.tpl'),
						array($sLogin)
					);
					$aMessages['info'][] = $this->info['send_password'];
				}
			} else {
				$this->get('smarty')->assign('login', $sLogin);
				$aMessages['errors'][] = $this->errors['no_user'];
			}
		}
		return $aMessages;
	}

	private function _sendMail($subject, $message, $subscribers) {
		global $ADMIN_EMAIL;
		$mail = new Mail();
		$mail->From($ADMIN_EMAIL);
		$mail->Subject($subject);
		$mail->Html($message, 'UTF-8');
		$mail->To($subscribers);
		$mail->Send();
	}

	public function getBody() {
		if ($this->getUser()) {
			switch ($this->get('router')->getParam('methodName')) {
				case 'logout':
					return $this->_getLogout();
				case 'orders':
					return $this->_getOrders();
				case 'orders-history':
					return $this->_getOrders();
				case 'password':
					return $this->_getPasswordForm();
				default:
					return $this->_getInfoForm();
			}
		} else {
			switch ($this->get('router')->getParam('methodName')) {
				case 'forget':
					return $this->_getForgetForm();
				case 'registration':
					return $this->_getRegistrationForm();
				default:
					return $this->_getLoginForm();
			}
		}
	}
}
