<?php

namespace Controller;

use \Common\Controller;

class AjaxController extends Controller{	
	
	private function getCartText() {
		$sCartText = '';
		if ($this->get('util')->_sessionVar('number', true, 0)) {
			$sCartText = '<span>'.$this->get('util')->_sessionVar('number', true, 0).'</span> товара(ов)<br> на сумму <span>'.$this->get('util')->_sessionVar('summa').'</span> руб.';
		} else {
			$sCartText = 'Нет выбранных<br/> товаров';
		}
		return $sCartText;
	}
	
	private function getCartTotalText() {
		$sCartText = 'Всего '.$this->get('util')->_sessionVar('number', true, 0).' товара(ов) на сумму: <span>'.$this->get('util')->_sessionVar('summa').'</span>';
		return $sCartText;
	}
	
	function vote($values) {
		// гемморой с массивами переданными через $.post
		foreach ($values as $element) {
			$_POST[$element['name']] = $element['value'];
		}
		$unit = new \Controller\VoteController();
		return json_encode(array('content' => $unit->getBody()));
	}                                                                       
                                                                                
	function voteResult($voteId) {
		$_POST['vote'] = $voteId;
		$unit = new \Controller\VoteController();
		return json_encode(array('content' => $unit->getBody()));
	}  
	
	public function addCartItem($productId, $quantity = 1, $price = 0, $priceId = 0) {
		$this->get('router')->setParams('/cart/');
		$cart = new \Controller\CartController();
		$result = array();
		$result['popup_content'] = $cart->addCartItem($productId, $quantity, $price, $priceId);
		$result['cart_count'] = $this->getCartText();
		return json_encode($result);
	}
	
	public function deleteCartItem($productGuid) {
		$product = $_SESSION['cart'][$productGuid];
		$quantity	= $_SESSION['number'];
		$sum		= preg_replace('/(\s|,00)+/i', '', $_SESSION['summa']);
		$productQuantity = $product['counter'];
		$productSum = $product['price'] * $productQuantity;
		unset($_SESSION['cart'][$productGuid]);
		$_SESSION['number'] = $quantity - $productQuantity;
		$_SESSION['summa']	= number_format($sum - $productSum, 2, ',', ' ');
		$result = array();
		$result['cart_count'] = $this->getCartText();
		$result['totalSum'] = $this->getCartTotalText();
		return json_encode($result);
	}

	public function showOrderDetail($orderId) {
		$this->get('router')->setParams('/cart/');
		$cart = new \Controller\CartController();
		$result = array();
		$result['popup_content'] = $cart->getOrderDetail($orderId);
		return json_encode($result);
	}
	
	public function showSubscribeResult($formdata) {
		parse_str($formdata);
		$message = 'Не указан e-mail';
		$success = false;
		if (!empty($email) && $email != 'e-mail') {
			if (!$this->get('util')->valid_email($email)) {
				$message = 'Неправильный e-mail';
			} else {
				$a = $this->get('container')->getItem('maillist_users', "email='".$this->get('connection')->escapeStr($email)."'");
				if (is_array($a)) {
					// в базе есть - отписываем
					if ($subscribe_type == 2) {
						if ($this->get('connection')->execQuery('delete_user_maillist', "DELETE FROM maillist_users WHERE email='".$this->get('connection')->escapeStr($email)."'")) {
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
						if ($this->get('container')->addItem('maillist_users', 'lastname,name,email, date, is_active', "'".addslashes($lastname)."','".addslashes($name)."','".addslashes($email)."', NOW(), ''")) {
							$mail = new \Common\Mail();
							$mail->From($GLOBALS['ADMIN_EMAIL']);
							$mail->Subject('Оповещение о подписке на рассылку на сайте '.$_SERVER['SERVER_NAME']);
							$body = "Уважаемый пользователь!\n\n
Вы подписались на рассылку на сайте http://".$_SERVER['SERVER_NAME']."\n
Для подтверждения, пожалуйста, проследуйте по ссылке:\n
http://".$_SERVER['SERVER_NAME']."/subscribe.php?email=".htmlspecialchars($email)."&action=active";
							$mail->Body($body, 'UTF-8');
							$mail->To($email);
							$mail->Send();
							$body = "На e-mail ".$email." оформлена подписка на рассылку на сайте http://".$_SERVER['SERVER_NAME']."\n";
							$mail->Body($body, 'UTF-8');
							$mail->To(array('content@colors-life.ru', 'rawork@yandex.ru'));
							$mail->Send();
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
		$this->get('smarty')->assign('server_name', $_SERVER['SERVER_NAME']);
		$this->get('smarty')->assign('prj_ref', $GLOBALS['PRJ_REF']);
		$this->get('smarty')->assign('success', $success);
		$this->get('smarty')->assign('message', $message);
		return json_encode(array('content' => $this->get('smarty')->fetch('service/ru/subscribe.tpl')));
	}
	
	public function sendStuffExist($productId, $email) {
		$product = $this->get('connection')->getItem('product', 'SELECT id,name FROM catalog_stuff WHERE id='.$productId);
		$name = isset($product['name']) ? $product['name'] : 'Товар не определен';
		$mail = new \Common\Mail();
		$mail->From($GLOBALS['ADMIN_EMAIL']);
		$mail->Subject('Цвета жизни - заявка на оповещение о наличии товара на складе от '.date('d.m.Y H:i'));
		$message = "
			Пользователь с электронной почтой $email просит оповестить о наличии товара на складе.\n\n
			Запрошен товар $name [ID=$productId]
		";
		$mail->Body($message, 'UTF-8');
		$mail->To(array('content@colors-life.ru', 'rawork@yandex.ru'));
		$mail->Send();
		return json_encode(array('status' => 'Ваша заявка принята. Мы будем рады помочь Вам.'));
	}
	
	public function get($name) {
		global $container, $security;
		if ($name == 'container') {
			return $container;
		} elseif ($name == 'security') {
			return $security;
		} else {
			return $container->get($name);
		}
	}
	
}
