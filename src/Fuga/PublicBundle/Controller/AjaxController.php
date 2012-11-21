<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CMSBundle\Controller\Controller;
use Fuga\PublicBundle\Controller\CartController;
use Fuga\PublicBundle\Controller\AuthController;
use Fuga\CMSBundle\Model\VoteManager;

class AjaxController extends Controller {	
	
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
		$result['cart_info'] = $cart->widgetAction();
		return json_encode($result);
	}
	
	public function deleteCartItem($productGuid) {
		$result = array();
		$this->get('router')->setParams('/cart/');
		$this->get('container')->register('auth', new AuthController());
		$cart = new CartController();
		$cart->deleteItem($productGuid);
		$result['cart_info'] = $cart->widgetAction();
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
		if (!$this->get('util')->valid_email($email)) {
			$message = array(
				'message' => 'Неправильный E-mail',
				'success' => false
			);
		} else {
			if ($subscribe_type == 2) {
				$message = $this->get('container')->getManager('maillist')->unsubscribe($email);
			} elseif ($subscribe_type == 1) {
				$message = $this->get('container')->getManager('maillist')->subscribe($email, $name, $lastname);
			}
		}
		return json_encode(array('content' => $this->render('service/subscribe/result.tpl', $message)));
	}
	
	public function sendStuffExist($productId, $email) {
		$product = $this->get('connection')->getItem('product', 'SELECT id,name FROM catalog_product WHERE id='.$productId);
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
