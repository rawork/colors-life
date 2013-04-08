<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

if (!isset($_SESSION['deliveryAddress'])) {
	$_SESSION['deliveryAddress'] = '';
	$_SESSION['deliveryPhone'] = '';
	$_SESSION['deliveryPhoneAdd'] = '';
	$_SESSION['deliveryPerson'] = '';
}
	
class AccountController extends PublicController {

	public $user;

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
		'incorrect_password' => 'Неправильный текущий пароль! Для изменения пароля необходимо ввести текущий пароль!',
		'incorrect_securecode' => 'Вы неправильно ввели цифры указанные на картинке',
		'user_present' => 'Логин <b>%s</b> уже занят',
		'wrong_password' => 'Неправильный логин или пароль',
		'notreg' => 'Пользователь <b>%s</b> не зарегистрирован',
		'securecode' => 'Вы неправильно ввели цифры указанные на картинке'
	);

	function __construct() {
		parent::__construct('account');
		if (!$_SESSION['deliveryAddress']) {
			$_SESSION['deliveryAddress'] = $this->getManager('Fuga:Common:Account')->getAddress();
			$_SESSION['deliveryPhone'] = $this->getManager('Fuga:Common:Account')->getPhone();
			$_SESSION['deliveryPerson'] = $this->getManager('Fuga:Common:Account')->getPersonName();
		}
	}

	public function logoutAction() {
		if (!$this->getManager('Fuga:Common:Account')->getCurrentUser()) {
			header('location: '.$this->get('container')->href('cabinet'));
			exit;
		}
		if ($this->get('container')->updateItem('account_user',
				array('session_id' => ''),
				array('login' => $this->getManager('Fuga:Common:Account')->getLogin())
		)) {
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
		if (!$this->getManager('Fuga:Common:Account')->getCurrentUser()) {
			header('location: '.$this->get('container')->href('cabinet'));
			exit;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$error_message = $this->_processInfoForm();
		}

		$user = $this->getManager('Fuga:Common:Account')->getCurrentUser();
		$user['birthday'] = explode('.', $user['birthday']);

		$cabinetMenu = $this->_getMenu();
		$userInfo = $user;
		$months = $this->months;
		$this->get('container')->setVar('title', 'Личные данные');
		$this->get('container')->setVar('h1', 'Личные данные');

		return $this->render('account/info.tpl', compact('error_message', 'cabinetMenu', 'userInfo', 'months'));
	}

	private function _processInfoForm() {
		$errors = array();
		$user = $this->getManager('Fuga:Common:Account')->getCurrentUser();
		$login = $this->get('util')->_postVar('userEmail');
		$name = $this->get('util')->_postVar('userFName');
		$lastname = $this->get('util')->_postVar('userLName');
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
		if (
			$login != $user['email'] &&
			$user = $this->get('container')->getItem('account_user', "login='".$login."' OR email='".$login."'")
		) {
			$errors[] = sprintf($this->errors['user_present'], $login);
		} else {
			$values = array(
				"login"		=> $login,
				"email"		=> $login,
				"name"		=> $name,
				"lastname"	=> $lastname,
				"phone"		=> $phone,
				"address"	=> $address,
				"gender"	=> $gender,
				"birthday"	=> $birthday,
				"updated"	=> date('Y-m-d H:i:s')
			);
			if ($this->get('container')->update('account_user', 
					$values,
					array('id' => $user['id'])
				)) {
				header('location: '.$this->get('container')->href('cabinet'));
			} else {
				$errors[] = $this->errors['db_error'];
			}
		}
		return implode('<br>', $errors);
	}
	
	public function indexAction() {
		if ($this->getManager('Fuga:Common:Account')->getCurrentUser()) {
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
		$this->get('container')->setVar('h1', 'Вход в личный кабинет');
		
		return $this->render('account/login.tpl', compact('error_message'));
	}

	private function _processLoginForm() {
		$errors = array();
		$fromPage = $this->get('util')->_postVar('fromPage');
		$login = $this->get('util')->_postVar('login');
		$password = $this->get('util')->_postVar('password');
		$user = $this->get('container')->getItem('account_user', "email='$login' OR login='$login'");
		if ($user) {
			if ($user['password'] == $password) {
				if ($this->get('container')->updateItem(
					'account_user', 
					array('session_id' => session_id(), 'logindate' => date('Y-m-d H:i:s')), 
					array('email' => $user['email'])
				)) {
					header('location: '.($fromPage ? $fromPage : '/'));
					exit;
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
		$this->get('container')->setVar('h1', 'Регистрация');
		$sessionName = session_name();
		$sessionId = session_id();
		
		return $this->render('account/registration.tpl', compact('error_message', 'sessionName', 'sessionId'));
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
		$t = $this->get('container')->getTable('account_user');
		$this->get('log')->write($this->get('util')->_sessionVar('captchaHash').' <> '.md5($this->get('util')->_postVar('captcha')));
		if($this->get('util')->_sessionVar('captchaHash') != md5($this->get('util')->_postVar('captcha'))){
			$errors[] = $this->errors['securecode'];
		} else {
			if ($user = $t->getItem("login='".$login."' OR email='".$login."'")) {
				$errors[] = sprintf($this->errors['user_present'], $login);
			} else {
				$values = array(
					"password"	=> $password,
					"name"		=> $userName,
					"lastname"	=> $userLName,
					"phone"		=> $phone,
					"created"	=> date('Y-m-d H:i:s')
				);
				if (
					$t->insert(array('login' => $login, 'email' => $login)) &&
					$t->update($values, array("email" => $login))
				) {
					$letterText = $this->render('account/registration.mail.tpl', compact('userName', 'userLName', 'login', 'password'));
					$this->get('mailer')->send(
						'Регистрация в магазине Цвета жизни',
						$letterText,
						$login.','.$this->params['email']
					);
					if ($isSubscribe) {
						$this->getManager('Fuga:Common:Maillist')->subscribe($login, $userName, $userLName);
					}		
					if ($t->update(array("session_id" => session_id()), array('email' => $login))) {
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
		return $this->render('account/menu.tpl');
	}

	public function ordersAction() {
		if (!$this->getManager('Fuga:Common:Account')->getCurrentUser()) {
			header('location: '.$this->get('container')->href('cabinet'));
			exit;
		}
		$user = $this->getManager('Fuga:Common:Account')->getCurrentUser();
		$items = $this->get('container')->getItems('cart_order', "user_id=".$user['id']);
		foreach ($items as &$item) {
			$products = explode("\n", $item['order_txt']);
			$item['products'] = array();
			foreach ($products as $product) {
				if (!$product) {
					continue;
				}
				$product = explode("\t", $product);
				$id = 0;
				$name = '';
				if (preg_match('/^\[([0-9]+)\]/', $product[0], $matches)) {
					$id = $matches[1];
					$name = preg_replace('/^\[([0-9]+)\]/', '', $product[0]);
				}
				$item['products'][] = array(
					'id'		=> $id,
					'name'		=> $name,
					'quantity'	=> $product[3],
					'price'		=> $product[2]
				);
			}
		}
		unset($item);
		$params = array(
			'cabinetMenu' => $this->_getMenu(),
			'user' => $user,
			'items' => $items
		);
		$this->get('container')->setVar('title', 'Заказы');
		$this->get('container')->setVar('h1', 'Заказы');
		
		
		return $this->render('account/orders.tpl', $params);
	}

	public function passwordAction() {
		if (!$this->getManager('Fuga:Common:Account')->getCurrentUser()) {
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
		$this->get('container')->setVar('h1', 'Изменение пароля');
		
		return $this->render('account/password.tpl', compact('error_message', 'info_message', 'cabinetMenu'));
	}

	private function _processPasswordForm() {
		$messages = array(
			'info' => array(),
			'errors' => array()
		);

		$t = $this->get('container')->getTable('account_user');

		$user = $this->getManager('Fuga:Common:Account')->getCurrentUser();
		$login = $user['email'];
		$oldPassword = $this->get('util')->_postVar('passwd');
		$newPassword = $this->get('util')->_postVar('newpasswd');

		if ($user['password'] == $oldPassword) {
			if ($t->update(array("password" => $newPassword, "updated" => date('Y-m-d H:i:s')), array('email' => $login))) {
				$letterText = $this->render('account/password.mail.tpl', compact('login', 'newPassword'));
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
		$this->get('container')->setVar('h1', 'Восстановление пароля');
		$sessionName = session_name();
		$sessionId = session_id();
		
		return $this->render('account/forget.tpl', compact('error_message', 'info_message', 'sessionName', 'sessionId'));
	}

	private function _processForgetForm() {
		$messages = array(
			'info' => array(),
			'errors' => array()
		);
		$t = $this->get('container')->getTable('account_user');
		if ($this->get('util')->_sessionVar('captchaHash') != md5($this->get('util')->_postVar('captcha'))) {
			$messages['errors'][] = $this->errors['incorrect_securecode'];
		} else {
			$login = $this->get('util')->_postVar('login');
			if ($user = $this->get('container')->getItem('account_user', "email='$login'")) {
				$newPassword = $this->get('util')->genKey(6);
				$updateQuery = "password='$newPassword'";
				if ($t->update($updateQuery.", updated = NOW() WHERE email='".$login."'")) {
					$letterText = $this->render('account/forget.mail.tpl', compact('login', 'newPassword'));
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
	
	public function widgetAction() {
		$user = $this->getManager('Fuga:Common:Account')->getCurrentUser();
		return $this->render('account/widget.tpl', compact('user'));     
	}

}
