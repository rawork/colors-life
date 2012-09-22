<?php

    inc_lib('components/Unit.php');
	inc_lib('Mail.php');
	if (!isset($_SESSION['deliveryAddress'])) {
		$_SESSION['deliveryAddress'] = '';
		$_SESSION['deliveryPhone'] = '';
		$_SESSION['deliveryPhoneAdd'] = '';
		$_SESSION['deliveryPerson'] = '';
	}
	
    class AuthUnit extends Unit {
        
		public $user;

		private $_aUser;

		private $_aMonths = array (
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

		private $_aInfo = array(
			'send_password' => 'Новый пароль выслан вам на электронный адрес',
			'change_password' => 'Пароль успешно изменен!'
		);
		
		private $_aErrors = array(
			'no_user' => 'С указанным эл. адресом нет зарегистрированных пользователей',
			'db_error' => 'Ошибка обработки запроса. Обратитесь к администратору сайта.',
			'incorrect_password' => 'Неправильный пароль! Для изменения пароля необходимо ввести текущий пароль!',
			'incorrect_securecode' => 'Вы неправильно ввели цифры указанные на картинке'
		);

        function __construct($aProperties = array()) {
            parent::__construct('auth', $aProperties);
            $this->initializeUser();
        }

		public function initializeUser() {
			if ($this->props) {
				$this->user = $GLOBALS['rtti']->getItem('auth_users', "session_id='".session_id()."'");
	            $this->user = count($this->user) ? $this->user : null;
				$this->_aUser = $this->user;
				if (!$_SESSION['deliveryAddress']) {
					$_SESSION['deliveryAddress'] = $this->getAddress();
					$_SESSION['deliveryPhone'] = $this->getPhone();
					$_SESSION['deliveryPerson'] = $this->getPersonName();
				}
			}
		}

		public function getUser() {
			return $this->_aUser;
		}

		public function getPersonName() {
			if ($this->_aUser) {
				return $this->_aUser['name'].($this->_aUser['lastname'] ? ' '.$this->_aUser['lastname'] : '');
			}
			return null;
		}

		public function getPhone() {
			if ($this->_aUser) {
				return $this->_aUser['phone'];
			}
		}

		public function getAddress() {
			if ($this->_aUser) {
				return $this->_aUser['address'];
			}
		}

		public function getLogin() {
			if ($this->_aUser) {
				return $this->_aUser['email'];
			}
		}

        private function _getLogout() {
			if ($GLOBALS['rtti']->getTable('auth_users')->update("session_id='' WHERE login='".$this->user['login']."'")) {
				unset($_SESSION['deliveryAddress']);
				unset($_SESSION['deliveryPhone']);
				unset($_SESSION['deliveryPhoneAdd']);
				unset($_SESSION['deliveryPerson']);
                header('location: /');
            } else {
                return $this->_aErrors['db_error'];
            }
        
        }
		
		private function _getInfoForm() {
			if (CUtils::_postVar('processInfo')) {
				$this->smarty->assign('error_message', $this->_processInfoForm());
			}
			
			$aUser = $this->getUser();
			$aUser['birthday'] = explode('.', $aUser['birthday']);
			
			$this->smarty->assign('cabinetMenu', $this->_getMenu());
			$this->smarty->assign('userInfo', $aUser);
			$this->smarty->assign('Months', $this->_aMonths);

            return $this->getTpl('service/auth/'.$this->props['lang'].'/info.form');;
        }

		private function _processInfoForm() {
            $aErrors = array();

			$aUser = $this->getUser();

			$sLogin = CUtils::_postVar('userEmail');
			$sUserName = CUtils::_postVar('userFName');
			$sUserLName = CUtils::_postVar('userLName');
			$sPhone = CUtils::_postVar('userPhone');
			$sAddress = CUtils::_postVar('userAddress');
			$sGender = CUtils::_postVar('userGender');
			$sDay = CUtils::_postVar('userDay');
			$sMonth = CUtils::_postVar('userMonth');
			$sYear = CUtils::_postVar('userYear');
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
			  $GLOBALS['rtti']->getTable('auth_users')->selectWhere("login='".$sLogin."' OR email='".$sLogin."'") &&
			  $GLOBALS['rtti']->getTable('auth_users')->getNumRows()
			) {
				$this->smarty->assign('login', $sLogin);
				$aErrors[] = $this->getTpl('service/auth/'.$this->props['lang'].'/error.userpresent');
			} else {
				if ($GLOBALS['rtti']->getTable('auth_users')->update($sUpdate.", change_date = NOW() WHERE email='".$aUser['email']."'")) {
					header('location: /cabinet/');
				} else {
					$aErrors[] = $this->_aErrors['db_error'];
				}
			}
            return implode('errors', $aErrors);
        }

		private function _getLoginForm() {
			if (CUtils::_postVar('processLogin')) {
				$this->smarty->assign('error_message', $this->_processLoginForm());
			}
			return $this->getTpl('service/auth/'.$this->props['lang'].'/login.form');
        }

        private function _processLoginForm() {
            $aErrors = array();
			$sFromPage = CUtils::_postVar('fromPage');
			$sLogin = CUtils::_postVar('login');
			$sPassword = CUtils::_postVar('password');
			$t = $GLOBALS['rtti']->getTable('auth_users');
            if ($aUser = $GLOBALS['rtti']->getItem('auth_users', "email='$sLogin' OR login='$sLogin'")) {
				if ($aUser['password'] == $sPassword) {
                    if ($t->update("session_id='".session_id()."', logindate=NOW() WHERE login='$sLogin' OR email='$sLogin'")) {
                        $this->smarty->assign('login', $sLogin);
						header('location: '.($sFromPage ? $sFromPage : '/'));
                    } else {
                        $aErrors[] = $this->_aErrors['db_error'];
                    }
                } else {
                    $aErrors[] = $this->getTpl('service/auth/'.$this->props['lang'].'/error.pass');
                }
            } else {
                $this->smarty->assign('login', $sLogin);
                $aErrors[] = $this->getTpl('service/auth/'.$this->props['lang'].'/error.notreg');
            }
            return implode('<br>', $aErrors);
        }

        private function _getRegistrationForm() {
			if (CUtils::_postVar('processRegistration')) {
				$this->smarty->assign('error_message', $this->_processRegistrationForm());
			}
			return $this->getTpl('service/auth/'.$this->props['lang'].'/registration.form');
        }

        private function _processRegistrationForm() {
            $aErrors = array();
			$sFromPage = CUtils::_postVar('fromPage');
			$sLogin = CUtils::_postVar('newUserEmail');
			$sPassword = CUtils::_postVar('newUserPassword');
			$sPasswordConfirm = CUtils::_postVar('newUserPasswordConfirm');
			$sUserName = CUtils::_postVar('newUserFName');
			$sUserLName = CUtils::_postVar('newUserLName');
			$sPhone = CUtils::_postVar('newUserPhone');
            $t = $GLOBALS['rtti']->getTable('auth_users');
			if(CUtils::_sessionVar('c_sec_code') != md5(CUtils::_postVar('captcha').__CAPTCHA_HASH)){
				$aErrors[] = $this->getTpl('service/auth/'.$this->props['lang'].'/error.securecode');
			} else {
                if (
                  $t->selectWhere("login='".$sLogin."' OR email='".$sLogin."'") &&
                  $t->getNumRows()
                ) {
                    $this->smarty->assign('login', $sLogin);
                    $aErrors[] = $this->getTpl('service/auth/'.$this->props['lang'].'/error.userpresent');
                } else {
					$sUpdate .= "password='$sPassword'";
					$sUpdate .= ",name='$sUserName'";
					$sUpdate .= ",lastname='$sUserLName'";
					$sUpdate .= ",phone='$sPhone'";
                    if (
                      $t->insert('login,email', "'".$sLogin."','".$sLogin."'") &&
                      $t->update($sUpdate.", credate = NOW(), change_date = NOW() WHERE email='".$sLogin."'")
                    ) {
						if ($sLogin) {
							$this->smarty->assign('Name', $sUserName);
							$this->smarty->assign('Lastname', $sUserLName);
							$this->smarty->assign('Login', $sLogin);
							$this->smarty->assign('Password', $sPassword);
							
							$this->_sendMail(
								'Регистрация в магазине Цвета жизни',
								$this->smarty->fetch('service/auth/'.$this->props['lang'].'/registration.mail.tpl'),
								explode(',', $sLogin.','.$this->dbparams['email'])
							);
						}
						if ($t->update("session_id='".session_id()."' WHERE login='$sLogin' OR email='$sLogin'")) {
							header('location: /cabinet/');
						} else {
							$aErrors[] = $this->_aErrors['db_error'];
						}
                    } else {
                        $aErrors = $this->_aErrors['db_error'];
                    }
                }
            }
            return implode('<br>', $aErrors);
        }

		private function _getMenu() {
			return $this->smarty->fetch('service/auth/'.$this->props['lang'].'/menu.tpl');
		}
		
		private function _getOrders() {
			return $this->_getMenu().$this->smarty->fetch('service/auth/'.$this->props['lang'].'/orders.tpl');
		}

		private function _getPasswordForm() {
			if (CUtils::_postVar('processPassword')) {
				$aMessages = $this->_processPasswordForm();
				$this->smarty->assign('error_message', implode('<br>', $aMessages['errors']));
				$this->smarty->assign('info_message', implode('<br>', $aMessages['info']));
			}
			return $this->getTpl('service/auth/'.$this->props['lang'].'/password.form');
		}

		private function _processPasswordForm() {
			$aMessages = array(
				'info' => array(),
				'errors' => array()
			);

			$t = $GLOBALS['rtti']->getTable('auth_users');

			$aUser = $this->getUser();
			$sLogin = $aUser['email'];
			$sOldPassword = CUtils::_postVar('passwd');
			$sNewPassword = CUtils::_postVar('newpasswd');

			if ($aUser['password'] == $sOldPassword) {
				$sUpdate = "password='$sNewPassword'";
				if ($t->update($sUpdate.", change_date = NOW() WHERE email='".$sLogin."'")) {
					$this->smarty->assign('Login', $sLogin);
					$this->smarty->assign('NewPassword', $sNewPassword);
					$this->_sendMail(
						'Новый пароль в магазине Цвета жизни',
						$this->smarty->fetch('service/auth/'.$this->props['lang'].'/password.mail.tpl'),
						array($sLogin)
					);
					$aMessages['info'][] = $this->_aInfo['change_password'];
				}
			} else {
				$aMessages['errors'][] = $this->_aErrors['incorrect_password'];
			}
			
			return $aMessages;
		}

		private function _getForgetForm() {
			if (CUtils::_postVar('processForget')) {
				$aMessages = $this->_processForgetForm();
				$this->smarty->assign('error_message', implode('<br>', $aMessages['errors']));
				$this->smarty->assign('info_message', implode('<br>', $aMessages['info']));
			}
			return $this->getTpl('service/auth/'.$this->props['lang'].'/forget.form');
		}

		private function _processForgetForm() {
			$aMessages = array(
				'info' => array(),
				'errors' => array()
			);
			$t = $GLOBALS['rtti']->getTable('auth_users');
			if (CUtils::_sessionVar('c_sec_code') != md5(CUtils::_postVar('captcha').__CAPTCHA_HASH)) {
				$aMessages['errors'][] = $this->_aErrors['incorrect_securecode'];
			} else {
				$sLogin = CUtils::_postVar('login');
				if ($aUser = $GLOBALS['rtti']->getItem('auth_users', "email='$sLogin'")) {
					$sNewPassword = CUtils::genKey(6);
					$sUpdate = "password='$sNewPassword'";
					if ($t->update($sUpdate.", change_date = NOW() WHERE email='".$sLogin."'")) {
						$this->smarty->assign('Login', $sLogin);
						$this->smarty->assign('NewPassword', $sNewPassword);
						$this->_sendMail(
							'Восстановление пароля в магазине Цвета жизни',
							$this->smarty->fetch('service/auth/'.$this->props['lang'].'/forget.mail.tpl'),
							array($sLogin)
						);
						$aMessages['info'][] = $this->_aInfo['send_password'];
					}
				} else {
					$this->smarty->assign('login', $sLogin);
					$aMessages['errors'][] = $this->_aErrors['no_user'];
				}
			}
			return $aMessages;
		}

		private function _sendMail($sSubject, $sMessage, $aSubscribers) {
			global $ADMIN_EMAIL;
			$msg = new Mail();
			$msg->From($ADMIN_EMAIL);
			$msg->Subject($sSubject);
			$msg->Html($sMessage, 'UTF-8');
			$msg->To($aSubscribers);
			$msg->Send();
		}
		
        public function getBody() {
            if ($this->getUser()) {
				switch ($this->props['method']) {
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
				switch ($this->props['method']) {
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

?>