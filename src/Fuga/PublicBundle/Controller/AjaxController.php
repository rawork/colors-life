<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CMSBundle\Controller\Controller;
use Fuga\PublicBundle\Controller\CartController;
use Fuga\PublicBundle\Controller\AuthController;
use Fuga\CMSBundle\Model\VoteManager;

class AjaxController extends Controller {	
	
	private function getCartText($quantity, $sum) {
		$widgetText = '';
		if ($quantity) {
			$widgetText = '<span>'.$quantity.'</span> товара(ов)<br> на сумму <span>'.$sum.'</span> руб.';
		} else {
			$widgetText = 'Нет выбранных<br/> товаров';
		}
		return $widgetText;
	}
	
	public function voteProcess($voteName, $formData = null) {
		$elements = null;
		if ($formData) {
			parse_str($formData, $elements);
		}
		$controller = new VoteManager();
		return json_encode(array('content' => $controller->getResult($voteName, $elements)));
	}                                                                       
                                                                                
	public function addCartItem($productId, $quantity = 1, $price = 0, $priceId = 0) {
		$this->get('router')->setParams('/cart/');
		$cart = new CartController();
		$result = array();
		$result['popup_content'] = $cart->addCartItem($productId, $quantity, $price, $priceId);
		$result['cart_count'] = $this->getCartText($cart->getTotalQuantity(), $cart->getTotalPriceRus());
		return json_encode($result);
	}
	
	public function deleteCartItem($productGuid) {
		$result = array();
		$this->get('router')->setParams('/cart/');
		$this->get('container')->register('auth', new AuthController());
		$cart = new CartController();
		$cart->deleteItem($productGuid);
		$result['cart_count'] = $this->getCartText($cart->getTotalQuantity(), $cart->getTotalPriceRus());
		$result['totalQuantity'] = $cart->getTotalQuantity();
		$result['totalSum'] = $cart->getTotalPriceRus().' руб.';
		$result['totalSumDiscount'] = $cart->getTotalPriceDiscount().' руб.';
		$result['discount'] = $cart->getDiscount().'%';
		
		return json_encode($result);
	}

	public function showOrderDetail($orderId) {
		$this->get('router')->setParams('/cart/');
		$cart = new CartController();
		$result = array();
		$result['popup_content'] = '<div class="cart-add">'.$cart->getOrderDetail($orderId).'</div>';
		return json_encode($result);
	}
	
	public function showSubscribeResult($formData) {
		parse_str($formData);
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
							$letterText = "Уважаемый пользователь!\n\n
Вы подписались на рассылку на сайте http://".$_SERVER['SERVER_NAME']."\n
Для подтверждения, пожалуйста, проследуйте по ссылке:\n
http://".$_SERVER['SERVER_NAME']."/subscribe/email=".htmlspecialchars($email)."&action=active";
							$this->get('mailer')->send(
								'Оповещение о подписке на рассылку на сайте '.$_SERVER['SERVER_NAME'],
								nl2br($letterText),
								$email
							);
							$letterText = "На e-mail ".$email." оформлена подписка на рассылку на сайте http://".$_SERVER['SERVER_NAME']."\n";
							$this->get('mailer')->send(
								'Оповещение о подписке на рассылку на сайте '.$_SERVER['SERVER_NAME'],
								nl2br($letterText),
								array('content@colors-life.ru', 'rawork@yandex.ru')
							);
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
		return json_encode(array('content' => $this->render('service/subscribe/result.tpl', compact('success', 'message'))));
	}
	
	public function sendStuffExist($productId, $email) {
		$product = $this->get('connection')->getItem('product', 'SELECT id,name FROM catalog_stuff WHERE id='.$productId);
		$name = isset($product['name']) ? $product['name'] : 'Товар не определен';
		$letterText = "
			Пользователь с электронной почтой $email просит оповестить о наличии товара на складе.\n\n
			Запрошен товар $name [ID=$productId]
		";
		$this->get('mailer')->send(
			'Цвета жизни - заявка на оповещение о наличии товара на складе от '.date('d.m.Y H:i'),
			nl2br($letterText),
			array('content@colors-life.ru', 'rawork@yandex.ru')
		);
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
