<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

if (!isset($_SESSION['deliveryAddress'])) {
	$_SESSION['deliveryAddress'] = '';
	$_SESSION['deliveryPhone'] = '';
	$_SESSION['deliveryPhoneAdd'] = '';
	$_SESSION['deliveryPerson'] = '';
}
	
class AuthController extends PublicController {

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
		'incorrect_securecode' => 'Вы неправильно ввели цифры указанные на картинке',
		'user_present' => 'Пользователь с логином <b>%s</b> уже есть в базе!',
		'wrong_password' => 'Ошибка пароля!',
		'notreg' => 'Пользователь <b>%s</b> не зарегистрирован!',
		'securecode' => 'Вы неправильно ввели цифры указанные на картинке'
	);

	function __construct() {
		parent::__construct('auth');
		$this->initializeUser();
	}

	public function initializeUser() {
		$this->user = $this->get('container')->getManager('Fuga:Common:User')->getCurrentUser();
		$this->currentUser = $this->user;
		if (!$_SESSION['deliveryAddress']) {
			$_SESSION['deliveryAddress'] = $this->getAddress();
			$_SESSION['deliveryPhone'] = $this->getPhone();
			$_SESSION['deliveryPerson'] = $this->getPersonName();
		}
	}

	public function getUser() {
		//unset($this->currentUser['password']);
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

	public function logoutAction() {
		if (!$this->getUser()) {
			header('location: '.$this->get('container')->href('cabinet'));
			exit;
		}
		if ($this->get('container')->getTable('auth_users')->update("session_id='' WHERE login='".$this->user['login']."'")) {
			unset($_SESSION['deliveryAddress']);
			unset($_SESSION['deliveryPhone']);
			unset($_SESSION['deliveryPhoneAdd']);
			unset($_SESSION['deliveryPerson']);
			header('location: /');
			exit;
		} else {
			return $this->errors['db_error'];
		}

	}

	public function infoAction() {
		if (!$this->getUser()) {
			header('location: '.$this->get('container')->href('cabinet'));
			exit;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$error_message = $this->_processInfoForm();
		}

		$user = $this->getUser();
		$user['birthday'] = explode('.', $user['birthday']);

		$cabinetMenu = $this->_getMenu();
		$userInfo = $user;
		$months = $this->months;
		$this->get('container')->setVar('title', 'Личные данные');

		return $this->render('auth/info.form.tpl', compact('error_message', 'cabinetMenu', 'userInfo', 'months'));
	}

	private function _processInfoForm() {
		$errors = array();

		$user = $this->getUser();

		$login = $this->get('util')->_postVar('userEmail');
		$userName = $this->get('util')->_postVar('userFName');
		$userLName = $this->get('util')->_postVar('userLName');
		$phone = $this->get('util')->_postVar('userPhone');
		$address = $this->get('util')->_postVar('userAddress');
		$gender = $this->get('util')->_postVar('userGender');
		$day = $this->get('util')->_postVar('userDay');
		$month = $this->get('util')->_postVar('userMonth');
		$year = $this->get('util')->_postVar('userYear');
		$birthday = '';
		if ($day && $month && $year) {
			$birthday = $day .'.'. $month .'.'. $year;
		}
		$updateQuery = "login='$login'";
		$updateQuery .= ",email='$login'";
		$updateQuery .= ",name='$userName'";
		$updateQuery .= ",lastname='$userLName'";
		$updateQuery .= ",phone='$phone'";
		$updateQuery .= ",address='$address'";
		$updateQuery .= ",gender='$gender'";
		$updateQuery .= ",birthday='$birthday'";

		if (
			($login != $user['email']) &&
			$this->get('container')->getTable('auth_users')->selectWhere("login='".$login."' OR email='".$login."'") &&
			$this->get('container')->getTable('auth_users')->getNumRows()
		) {
			$errors[] = sprintf($this->errors['user_present'], $login);
		} else {
			if ($this->get('container')->getTable('auth_users')->update($updateQuery.", updated = NOW() WHERE email='".$user['email']."'")) {
				header('location: '.$this->get('container')->href('cabinet'));
			} else {
				$errors[] = $this->errors['db_error'];
			}
		}
		return implode('errors', $errors);
	}
	
	public function indexAction() {
		if ($this->getUser()) {
			return $this->infoAction();
		} else {
			return $this->loginAction();
		}
	}

	public function loginAction() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$error_message = $this->_processLoginForm();
		}
		$this->get('container')->setVar('title', 'Вход в личный кабинет');
		
		return $this->render('auth/login.form.tpl', compact('error_message'));
	}

	private function _processLoginForm() {
		$errors = array();
		$fromPage = $this->get('util')->_postVar('fromPage');
		$login = $this->get('util')->_postVar('login');
		$password = $this->get('util')->_postVar('password');
		$t = $this->get('container')->getTable('auth_users');
		if ($user = $this->get('container')->getItem('auth_users', "email='$login' OR login='$login'")) {
			if ($user['password'] == $password) {
				if ($t->update("session_id='".session_id()."', logindate=NOW() WHERE login='$login' OR email='$login'")) {
					header('location: '.($fromPage ? $fromPage : '/'));
				} else {
					$errors[] = $this->errors['db_error'];
				}
			} else {
				$errors[] = $this->errors['wrong_password'];
			}
		} else {
			$errors[] = sprintf($this->errors['notreg'], $login);
		}
		return implode('<br>', $errors);
	}

	public function registrationAction() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$error_message = $this->_processRegistrationForm();
		}
		$this->get('container')->setVar('title', 'Регистрация');
		
		return $this->render('auth/registration.form.tpl', compact('error_message'));
	}

	private function _processRegistrationForm() {
		$errors = array();
		$fromPage = $this->get('util')->_postVar('fromPage');
		$login = $this->get('util')->_postVar('newUserEmail');
		$password = $this->get('util')->_postVar('newUserPassword');
		$passwordConfirm = $this->get('util')->_postVar('newUserPasswordConfirm');
		$userName = $this->get('util')->_postVar('newUserFName');
		$userLName = $this->get('util')->_postVar('newUserLName');
		$phone = $this->get('util')->_postVar('newUserPhone');
		$isSubscribe = $this->get('util')->_postVar('newUserSubscribe');
		$t = $this->get('container')->getTable('auth_users');
		if($this->get('util')->_sessionVar('captchaHash') != md5($this->get('util')->_postVar('captcha').__CAPTCHA_HASH)){
			$errors[] = $this->errors['securecode'];
		} else {
			if (
				$t->selectWhere("login='".$login."' OR email='".$login."'") &&
				$t->getNumRows()
			) {
				$errors[] = sprintf($this->errors['user_present'], $login);
			} else {
				$updateQuery = "password='$password'";
				$updateQuery .= ",name='$userName'";
				$updateQuery .= ",lastname='$userLName'";
				$updateQuery .= ",phone='$phone'";
				if (
					$t->insert('login,email', "'$login','$login'") &&
					$t->update($updateQuery.", created = NOW(), updated = NOW() WHERE email='".$login."'")
				) {
					$letterText = $this->render('auth/registration.mail.tpl', compact('userName', 'userLName', 'login', 'password'));
					$this->get('mailer')->send(
						'Регистрация в магазине Цвета жизни',
						$letterText,
						$login.','.$this->params['email']
					);
					if ($isSubscribe) {
						$this->get('container')->getManager('Fuga:Common:Maillist')->subscribe($login, $userName, $userLName);
					}		
					if ($t->update("session_id='".session_id()."' WHERE login='$login' OR email='$login'")) {
						header('location: '.$this->get('container')->href('cabinet'));
					} else {
						$errors[] = $this->errors['db_error'];
					}
				} else {
					$errors = $this->errors['db_error'];
				}
			}
		}
		return implode('<br>', $errors);
	}

	private function _getMenu() {
		return $this->render('auth/menu.tpl');
	}

	public function ordersAction() {
		if (!$this->getUser()) {
			header('location: '.$this->get('container')->href('cabinet'));
			exit;
		}
		$params = array(
			'user' => $this->getUser(),
			'cabinetMenu' => $this->_getMenu()
		);
		$this->get('container')->setVar('title', 'Заказы');
		
		return $this->render('auth/orders.tpl', $params);
	}

	public function passwordAction() {
		if (!$this->getUser()) {
			header('location: '.$this->get('container')->href('cabinet'));
			exit;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$messages = $this->_processPasswordForm();
			$error_message = implode('<br>', $messages['errors']);
			$info_message = implode('<br>', $messages['info']);
		}
		$cabinetMenu = $this->_getMenu();
		$this->get('container')->setVar('title', 'Изменение пароля');
		
		return $this->render('auth/password.form.tpl', compact('error_message', 'info_message', 'cabinetMenu'));
	}

	private function _processPasswordForm() {
		$messages = array(
			'info' => array(),
			'errors' => array()
		);

		$t = $this->get('container')->getTable('auth_users');

		$user = $this->getUser();
		$login = $user['email'];
		$oldPassword = $this->get('util')->_postVar('passwd');
		$newPassword = $this->get('util')->_postVar('newpasswd');

		if ($user['password'] == $oldPassword) {
			$updateQuery = "password='$newPassword'";
			if ($t->update($updateQuery.", updated = NOW() WHERE email='".$login."'")) {
				$letterText = $this->render('auth/password.mail.tpl', compact('login', 'newPassword'));
				$this->get('mailer')->send(
					'Новый пароль в магазине Цвета жизни',
					$letterText,
					array($login)
				);
				$messages['info'][] = $this->info['change_password'];
			}
		} else {
			$messages['errors'][] = $this->errors['incorrect_password'];
		}

		return $messages;
	}

	public function forgetAction() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$messages = $this->_processForgetForm();
			$error_message = implode('<br>', $messages['errors']);
			$info_message = implode('<br>', $messages['info']);
		}
		$this->get('container')->setVar('title', 'Восстановление пароля');
		
		return $this->render('auth/forget.form.tpl', compact('error_message', 'info_message'));
	}

	private function _processForgetForm() {
		$messages = array(
			'info' => array(),
			'errors' => array()
		);
		$t = $this->get('container')->getTable('auth_users');
		if ($this->get('util')->_sessionVar('captchaHash') != md5($this->get('util')->_postVar('captcha').__CAPTCHA_HASH)) {
			$messages['errors'][] = $this->errors['incorrect_securecode'];
		} else {
			$login = $this->get('util')->_postVar('login');
			if ($user = $this->get('container')->getItem('auth_users', "email='$login'")) {
				$newPassword = $this->get('util')->genKey(6);
				$updateQuery = "password='$newPassword'";
				if ($t->update($updateQuery.", updated = NOW() WHERE email='".$login."'")) {
					$letterText = $this->render('auth/forget.mail.tpl', compact('login', 'newPassword'));
					$this->get('mailer')->send(
						'Восстановление пароля в магазине Цвета жизни',
						$letterText,
						array($login)
					);
					$messages['info'][] = $this->info['send_password'];
				}
			} else {
				$messages['errors'][] = $this->errors['no_user'];
			}
		}
		return $messages;
	}

}
