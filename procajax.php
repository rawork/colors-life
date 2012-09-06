<?php
    include_once('config.php');
	inc_lib('tools/xajax/xajax.inc.php');

	$xajax = new xajax('/procajax.php');
//	$xajax->setFlag('debug',true);
	$xajax->registerFunction('vote');
	$xajax->registerFunction('voteResult');
	$xajax->registerFunction('addToCart');
	$xajax->registerFunction('showSubscribeForm');
	$xajax->registerFunction('showSubscribeResult');
	$xajax->registerFunction('showOrderDetail');
	$xajax->registerFunction('deleteCartItem');
	$xajax->registerFunction('sendStuffExist');

	function deleteCartItem($sGUID) {
		$aStuff = $_SESSION['cart'][$sGUID];
		$iQuantity	= $_SESSION['number'];
		$iSum		= preg_replace('/(\s|,00)+/i', '', $_SESSION['summa']);
		$iStuffQuantity = $aStuff['counter'];
		$iStuffSum = $aStuff['price'] * $iStuffQuantity;
		unset($_SESSION['cart'][$sGUID]);
		$_SESSION['number'] = $iQuantity - $iStuffQuantity;
		$_SESSION['summa']	= number_format($iSum - $iStuffSum, 2, ',', ' ');
		$oResponse = new xajaxResponse();
//		$oResponse->alert($iQuantity.'_'.$iStuffQuantity.'_'.$iSum.'_'.$iStuffSum);
		$oResponse->script("$('#stuff_$sGUID').remove();");
		$oResponse->script("$('#delim_$sGUID').remove();");
		if ($sCartText = getCartText()) {
			$oResponse->assign('cart_count', 'innerHTML', $sCartText);
		}
		if ($sCartText = getCartTotalText()) {
			$oResponse->assign('totalSum', 'innerHTML', $sCartText);
		}
		return $oResponse;
	}

	function getCartTotalText() {
		$sCartText = 'Всего '.CUtils::_sessionVar('number', true, 0).' товара(ов) на сумму: <span>'.CUtils::_sessionVar('summa').'</span>';
		return $sCartText;
	}

	function vote($div_id, $fD) {
		foreach ($fD as $k => $f) {
			$_POST[$k] = $f;
		}
		inc_u('vote');
		$unit = new VoteUnit($GLOBALS['urlprops']);
		$objResponse = new xajaxResponse();
		$objResponse->assign($div_id, 'innerHTML', $unit->getBody());
		return $objResponse;
	}
	
	function voteResult($div_id, $vote_id) {
		$_POST['vote'] = $vote_id;
		inc_u('vote');
		$unit = new VoteUnit($GLOBALS['urlprops']);
		$objResponse = new xajaxResponse();
		$objResponse->assign($div_id, 'innerHTML', $unit->getBody());
		return $objResponse;
	}

	function getCartText() {
		$sCartText = '';
		if (CUtils::_sessionVar('number', true, 0)) {
			$sCartText = '<span>'.CUtils::_sessionVar('number', true, 0).'</span> товара(ов)<br> на сумму <span>'.CUtils::_sessionVar('summa').'</span> руб.';
		}
		return $sCartText;
	}
	
	function addToCart($iStuffId, $iQuantity = 1, $fPrice = 0, $iPriceId = 0) {
		$objResponse = new xajaxResponse();
		inc_u('cart');
		$cart = new CartUnit($GLOBALS['urlprops']);
		$text = $cart->addCartItem($iStuffId, $iQuantity, $fPrice, $iPriceId);
		if ($sCartText = getCartText()) {
			$objResponse->assign('cart_count', 'innerHTML', $sCartText);
		}
		$objResponse->assign('popup_content','innerHTML',$text);
		$objResponse->script("popUp('popup')");
		
		return $objResponse;
	}
	
	function showOrderDetail($order_id) {
		inc_u('cart'); 
		$cart = new CartUnit($GLOBALS['urlprops']);
		$text = $cart->getOrderDetail($order_id);
		$objResponse = new xajaxResponse();
		$objResponse->assign('popup_content','innerHTML',$text);
		$objResponse->script("popUp('popup')");
		
		return $objResponse;
	}
	
	function showSubscribeForm() {
		$oResponse = new xajaxResponse();
		$oResponse->assign('popup_content','innerHTML',$GLOBALS['smarty']->fetch('service/ru/subscribe.form.tpl'));
		$oResponse->script("popUp('popup')");
		return $oResponse;
	}

	function sendStuffExist($iStuffId, $sEmail) {
		$oResponse = new xajaxResponse();
		$aStuff = $GLOBALS['db']->getItem('stuff', 'SELECT id,name FROM catalog_stuff WHERE id='.$iStuffId);
		$sName = isset($aStuff['name']) ? $aStuff['name'] : 'Товар не определен';
		inc_lib('libmail.php');
		$oMail = new Mail();
		$oMail->From($GLOBALS['ADMIN_EMAIL']);
		$oMail->Subject('Цвета жизни - заявка на оповещение о наличии товара на складе от '.date('d.m.Y H:i'));
		$sBody = "
			Пользователь с электронной почтой $sEmail просит оповестить о наличии товара на складе.\n\n
			Запрошен товар $sName [ID=$iStuffId]
		";
		$oMail->Body($sBody, 'windows-1251');
		$oMail->To(array('content@colors-life.ru', 'rawork@yandex.ru'));
		$oMail->Send();
		$oResponse->assign('mailblock'.$iStuffId, 'innerHTML', 'Ваша заявка принята. Мы будем рады помочь Вам.');
		return $oResponse;
	}
	
	function showSubscribeResult($fD) {
		$objResponse = new xajaxResponse();
		$email = $fD['email'];
		$name  = $fD['name'];
		$subscribe_type = $fD['subscribe_type'];
		$lastname  = $fD['lastname'];
		$message = 'Не указан e-mail';
		$success = false;
		if (!empty($email) && $email != 'e-mail') {
			if (!CUtils::valid_email($email)) {
				$message = 'Неправильный e-mail';
			} else {
				$a = $GLOBALS['rtti']->getItem('maillist_users', "email='".$GLOBALS['db']->escapeStr($email)."'");
				if (is_array($a)) {
					// в базе есть - отписываем
					if ($subscribe_type == 2) {
						if ($GLOBALS['db']->execQuery('delete_user_maillist', "DELETE FROM maillist_users WHERE email='".$GLOBALS['db']->escapeStr($email)."'")) {
							$message = 'Адрес '.htmlspecialchars($email).' удален из списка рассылки';
							$success = true;
						} else {
							$message = 'Ошибка базы данных при удалении';
						}
					} else {
						$message='Адрес '.htmlspecialchars($email).' уже есть в списке рассылки';
					}
				} else {
					// в базе нет - подписываем
					if ($subscribe_type == 1) {
						$email = substr($email, 0, 100);
						$success = true;
						if ($GLOBALS['rtti']->addItem('maillist_users', 'lastname,name,email, date, is_active', "'".addslashes($lastname)."','".addslashes($name)."','".addslashes($email)."', NOW(), ''")) {
							inc_lib('libmail.php');
							$oMail = new Mail();
							$oMail->From($GLOBALS['ADMIN_EMAIL']);
							$oMail->Subject('Оповещение о подписке на рассылку на сайте '.$_SERVER['SERVER_NAME']);
							$body = "Уважаемый пользователь!\n\n
Вы подписались на рассылку на сайте http://".$_SERVER['SERVER_NAME']."\n
Для подтверждения, пожалуйста, проследуйте по ссылке:\n
http://".$_SERVER['SERVER_NAME']."/subscribe.php?email=".htmlspecialchars($email)."&action=active";
							$oMail->Body($body, 'windows-1251');
							$oMail->To($email);
							$oMail->Send();
							$body = "На e-mail ".$email." оформлена подписка на рассылку на сайте http://".$_SERVER['SERVER_NAME']."\n";
							$oMail->Body($body, 'windows-1251');
							$oMail->To(array('content@colors-life.ru', 'rawork@yandex.ru'));
							$oMail->Send();
							$message='Адрес '.htmlspecialchars($email).' занесен в список рассылки';
						} else {
							$success = false;
						}
						if (!$success) {
							$message = 'Ошибка базы данных при добавлении';
						}
					} else {
						$message='Адреса '.htmlspecialchars($email).' нет в списке рассылки';
					}
				}
			}
		}
		$GLOBALS['smarty']->assign('server_name', $_SERVER['SERVER_NAME']);
		$GLOBALS['smarty']->assign('prj_ref', $GLOBALS['PRJ_REF']);
		$GLOBALS['smarty']->assign('success', $success);
		$GLOBALS['smarty']->assign('message', $message);
		$objResponse->assign('subscribe_form','innerHTML',$GLOBALS['smarty']->fetch('service/ru/subscribe.tpl'));
		//$objResponse->script("popUp('popup')");
		return $objResponse;
	}
	
	$xajax->processRequest();
