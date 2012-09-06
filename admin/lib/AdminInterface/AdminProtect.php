<?php
	
    class AdminProtect {
        public $user;
		public $operation;
		public $message;
        function __construct() {
        global $AUTH_LOCK_PROJECT;
			$this->user = CUtils::_sessionVar('ukey');
			$this->operation = CUtils::_getVar('operation');
			$this->message = array('type' => '', 'text' => '');
			if (!empty($this->operation)) {
				switch ($this->operation) {
                  case 'logout':
					$this->logout(); break; 
				  case 'forgot':
					$this->forgot(); break;
				  case 'change_password':
					$this->change_password(); break;	
            	}
            } else {
				if (empty($this->user)) {
					$this->checkUser();
				}
				if (($AUTH_LOCK_PROJECT == 'Y' || stristr($_SERVER['REQUEST_URI'], '/admin')) && empty($this->user)) {
					$this->authenticate();
				}	
            }
		}
		
		function checkUser() {
			if (!empty($_COOKIE['userkey'])) {
				if ($_COOKIE['userkey'] == md5(_DEV_PASS.substr(_DEV_USER, 0, 3).$_SERVER['REMOTE_ADDR'])) {
					$user = array('syslogin' => _DEV_USER);
				} else {
					$user = $GLOBALS['db']->getItem('users_users',
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
        
		function logout() {
			unset($_SESSION['user']);
			unset($_SESSION['ukey']);
			setcookie('userkey', '', time() - 3600);
			session_destroy();
			if (empty($_SERVER['HTTP_REFERER']) || stristr($_SERVER['HTTP_REFERER'], 'operation=logout')) {
				$uri = $GLOBALS['PRJ_REF'].'/admin/';
			} else {
				$uri = $_SERVER['HTTP_REFERER'];
			}
			header('Location: '.$uri);
			exit;
		}
		
		function change_password() {
			global $MAIN_EMAIL;
			if (CUtils::_getVar('key')) {
				$user = $GLOBALS['db']->getItem('users_users', "SELECT * FROM users_users WHERE hashkey='".CUtils::_getVar('key')."'");
				if (!empty($user) && !empty($user['email'])) {
					$key = CUtils::genKey();
					$GLOBALS['db']->execQuery('users_users', "UPDATE users_users SET syspassword=MD5('".$key."'), hashkey='' WHERE hashkey='".CUtils::_getVar('key')."'");
					inc_lib('libmail.php');
					$msg = new Mail();
					$msg->From($MAIN_EMAIL);
           			$msg->Subject('Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME']);
					$body = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
					$body .= '------------------------------------------'."\n";
					$body .= 'Вы запросили ваши регистрационные данные.'."\n";
					$body .= 'Ваша регистрационная информация:'."\n";
					$body .= 'ID пользователя: '.$user['id']."\n";
					$body .= 'Логин: '.$user['syslogin']."\n";
					$body .= 'Пароль: '.$key."\n\n";
					$body .= 'Сообщение сгенерировано автоматически.'."\n";
           			$msg->Body($body, 'windows-1251');
           			$msg->To($user['email']);
           			$msg->Send();
				}
			}
		}
		
		function showForgotForm() {
		global $smarty;
			$smarty->assign('message', CUtils::showMsg($this->message));
			$smarty->display('admin/form.forgot.tpl');
		}
		
		function forgot() {
			global $MAIN_EMAIL;
			if (CUtils::_postVar('submited') && (CUtils::_postVar('fuser') || CUtils::_postVar('femail'))) {
				$user = array();
				if (CUtils::_postVar('fuser') != '') {
					$user = $GLOBALS['db']->getItem('users_users', "SELECT * FROM users_users WHERE syslogin='".CUtils::_postVar('fuser')."'");
					if (empty($user)) {
						$this->message['type'] = 'error';
						$this->message['text'] = 'Не найден пользователь с указанным <b>логином</b>!';
					}
				}
				if (CUtils::_postVar('femail') != '' && empty($user)) {
					$user = $GLOBALS['db']->getItem('users_users', "SELECT * FROM users_users WHERE email='".CUtils::_postVar('femail')."'");
					if (empty($user)) {
						$this->message['type'] = 'error';
						$this->message['text'] = 'Не найден пользователь с указанным <b>e-mail</b>!';
					}
				}
				if (!empty($user)) {
					$key = CUtils::genKey(32);
					inc_lib('libmail.php');
					$msg = new Mail();
					$msg->From($MAIN_EMAIL);
           			$msg->Subject('Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME']);
           			$body = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
					$body .= '------------------------------------------'."\n";
					$body .= 'Вы запросили ваши регистрационные данные.'."\n\n";
					$body .= 'Ваша регистрационная информация:'."\n";
					$body .= 'ID пользователя: '.$user['id']."\n";
					$body .= 'Логин: '.$user['syslogin']."\n\n";
					$body .= 'Для смены пароля перейдите по следующей ссылке:'."\n";
					$body .= 'http://'.$_SERVER['SERVER_NAME'].'/admin/?operation=change_password&key='.$key."\n\n";
					$body .= 'Сообщение сгенерировано автоматически.'."\n";
           			$msg->Body($body, 'windows-1251');
           			$msg->To($user['email']);
           			$msg->Send();
					$GLOBALS['db']->execQuery('users_users', "UPDATE users_users SET hashkey='".$key."' WHERE id=".$user['id']);
					$this->message['type'] = 'ok';
					$this->message['text'] = 'Новые параметры авторизации отправлены Вам на <b>e-mail</b>!';
				}
			}
			$this->showForgotForm();
			exit;
		}
		
		function showAuthForm() {
		global $smarty;
			if (CUtils::_getVar('error')) {
				$this->message['type'] = 'error';
				$this->message['text'] = CUtils::_getVar('error');
			}
			if (CUtils::_getVar('ok')) {
				$this->message['type'] = 'ok';
				$this->message['text'] = CUtils::_getVar('ok');
			}
			$smarty->assign('message', CUtils::showMsg($this->message));
			$smarty->display('admin/form.auth.tpl');
			exit;
		}
		
		function authenticate() {
			$cuser = $GLOBALS['db']->escapeStr(CUtils::_postVar('auser'));
			$cpw = $GLOBALS['db']->escapeStr(CUtils::_postVar('apw'));
            if (empty($this->user) && (empty($cuser) || empty($cpw))){
				if (isset($_POST['auser']) && isset($_POST['apw'])) {
					$this->message['type'] = 'error';
					$this->message['text'] = 'Введено пустое значение пользователя или пароля';
				}
				$this->showAuthForm();
            } elseif ($this->isServer() && !empty($cuser) && !empty($cpw)) {
				$cpw = md5($cpw);
				if ($cuser == _DEV_USER && $cpw == _DEV_PASS) {
					$user = array('syslogin' => $cuser);
				} else {
					$user = $GLOBALS['db']->getItem('users_users', "SELECT syslogin FROM users_users WHERE syslogin='$cuser' AND syspassword='".$cpw."' AND is_active='on'");
				}
				if (!empty($user)){
					$_SESSION['user'] = $cuser;
					$_SESSION['ukey'] = md5($cpw.substr($cuser, 0, 3).$_SERVER['REMOTE_ADDR']);
					if (CUtils::_postVar('save')) {
						setcookie('userkey', md5($cpw.substr($cuser, 0, 3).$_SERVER['REMOTE_ADDR']), time()+3600*24*1000);
					}
					header('Location: '.$_SERVER['HTTP_REFERER']);
					exit;
				} else{
					$this->message['type'] = 'error';
					$this->message['text'] = 'Неправильно введен пользователь или пароль';
					$this->showAuthForm();
				}
            }
        }
		
		function isAdmin() {
            return $_SESSION['user'] == 'admin';
        }
		
		function isDeveloper() {
            return $_SESSION['user'] == 'dev';
        }
		
		function isSuperuser() {
			return $this->isAdmin() || $this->isDeveloper();
		}
		
        function isLocal() {
            return empty($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] == gethostbyname($_SERVER['SERVER_NAME']);
        }
		
		function isServer() {
			return isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']);
		}
    }

    $GLOBALS['auth'] = new AdminProtect();
?>
