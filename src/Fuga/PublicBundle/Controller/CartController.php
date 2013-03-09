<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = array();
	$_SESSION['number'] = 0;
	$_SESSION['summa'] = 0;
}

class CartController extends PublicController {

	private $baseNumber = 100000;
	public $lang;

	public function __construct() {
		parent::__construct('cart');
	}

	public function addAction() {
		$id		 = $this->get('util')->_postVar('productId');
		$quantity= $this->get('util')->_postVar('quantity');
		$price   = $this->get('util')->_postVar('price'); 
		$priceId = $this->get('util')->_postVar('priceId');
		$product = $this->get('container')
				->getManager('Fuga:Common:Cart')
				->add($id, $quantity, $price, $priceId);

		$result['popup_content'] = $this->render('cart/add.tpl', compact('product'));
		$result['widget'] = $this->widgetAction();
		return json_encode($result);
	}
	
	public function deleteAction() {
		$manager = $this->get('container')->getManager('Fuga:Common:Cart');
		$GUID = $this->get('util')->_postVar('productGUID');
		$result = array();
		$this->get('container')->getManager('Fuga:Common:Cart')->delete($GUID);
		$result['cart_info'] = $this->widgetAction();
		$result['totalQuantity'] = $manager->getTotalQuantity();
		$result['totalSum'] = $manager->getTotalPriceRus().' руб.';
		$result['totalSumDiscount'] = $manager->getTotalPriceDiscount().' руб.';
		$result['discount'] = $manager->getDiscount().'%';
		
		return json_encode($result);
	}
	
	public function orderdetailAction() {
		$orderId = $this->get('util')->_postVar('orderId');
		$result = array(
			'popup_content' => '<div class="cart-add">'.$this->get('container')->getManager('Fuga:Common:Cart')->getOrderDetail($orderId).'</div>'
		);
		
		return json_encode($result);
	}
	
	function indexAction() {
		$manager = $this->get('container')->getManager('Fuga:Common:Cart');
		if ($this->get('util')->_postVar('recalculate')) {
			$manager->update();
			header('location: '.$this->get('container')->href('cart'));
			exit;
		}
		$params = array(
			'wordEnd' => $manager->getTermination($this->get('util')->_sessionVar('number', true, 0)),
			'items' => $_SESSION['cart'],
			'gifts' => $manager->getGifts(),
			'discount' => $manager->getDiscount(),
			'totalPrice' => $manager->getTotalPrice(),
			'totalPriceRus' => $manager->getTotalPriceRus(),
			'totalPriceDiscount' => $manager->getTotalPriceDiscount(),
			'user' => $this->get('auth')->getUser()
		);
		$this->get('container')->setVar('title', 'Уточнение заказа');
		return $this->render('cart/list.tpl', $params);
	}

	public function getOrderEmail() {
		return $this->params['email'];
	}

	private function _addOrder() {
		$manager = $this->get('container')->getManager('Fuga:Common:Cart');
		$user = $this->get('auth')->getUser();
		$payType		= $this->get('connection')->getItem('pay', 'SELECT id,name FROM cart_pay_type WHERE id='.$this->get('util')->_sessionVar('payType'));
		$deliveryType	= $this->get('connection')->getItem('delivery', 'SELECT id,name FROM cart_delivery_type WHERE id='.$this->get('util')->_sessionVar('deliveryType'));
		
		$orderText = $manager->getOrderText();
		$this->get('container')->addItem('cart_order',
				"user_id, counter, summa, discount, status, fio, email, phone, phone2, pay_type, delivery_type, address, additions, order_txt, created",
				($user ? $user['id'] : 0).
				",'".$_SESSION['number'].
				"','".$manager->getTotalPriceDiscount().
				"','".$manager->getDiscount().
				"','Новый','".$this->get('util')->_sessionVar('deliveryPerson').
				"','".$this->get('auth')->getLogin().
				"','".$this->get('util')->_sessionVar('deliveryPhone').
				"','".$this->get('util')->_sessionVar('deliveryPhoneAdd').
				"','".$payType['name'].
				"','".$deliveryType['name'].
				"','".$this->get('util')->_sessionVar('deliveryAddress').
				"','".$this->get('util')->_postVar('deliveryComment').
				"',"."'".$orderText."',NOW()");
		$lastId = $this->get('connection')->getInsertID();

		$orderNumber = $this->baseNumber + $lastId;
		$cart = $this->get('util')->_sessionVar('cart');
		foreach($cart as &$cartItem) {
			$cartItem['price'] = number_format($cartItem['price'], 2, ',', ' ');
		}
		unset($cartItem);
		$params = array(
			'discount' => $manager->getDiscount(),
			'totalPrice' => $manager->getTotalPriceRus(),
			'totalPriceDiscount' => $manager->getTotalPriceDiscount(),
			'deliveryPhone' => $this->get('util')->_sessionVar('deliveryPhone'),
			'deliveryEmail' => $this->get('util')->_sessionVar('deliveryEmail'),
			'deliveryComment' => $this->get('util')->_postVar('deliveryComment'),
			'deliveryPhoneAdd' => $this->get('util')->_sessionVar('deliveryPhoneAdd'),
			'deliveryAddress' => $this->get('util')->_sessionVar('deliveryAddress'),
			'deliveryPerson' => $this->get('util')->_sessionVar('deliveryPerson'),
			'cart' => $cart,
			'payType' => $payType,
			'deliveryType' => $deliveryType,
			'orderNumber' => $orderNumber,
			'user' => $user
		);
		$orderText = $this->render('cart/order.mail.tpl', $params);
		$this->get('mailer')->send(
			'Заказ №'.$orderNumber.' от '.date('d.m.Y H:i'),
			"Заказ №$orderNumber от ".date('d.m.Y H:i')."<br>".$orderText,
			$this->getOrderEmail()
		);		
		$buyerEmail = $this->get('util')->_sessionVar('deliveryEmail');
		if ($buyerEmail) {
			$this->get('mailer')->send(
				'Цвета жизни - заказ №'.$orderNumber.' принят к обработке',
				$orderText,
				$buyerEmail
			);
		}
	}

	public function confirmAction() {
		if (count($_SESSION['cart']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->_addOrder();
			$_SESSION['cart'] = array();
			$_SESSION['number'] = 0;
			$_SESSION['summa'] = 0;
			unset($_SESSION['deliveryAddress']);
			unset($_SESSION['deliveryEmail']);
			unset($_SESSION['deliveryPhone']);
			unset($_SESSION['deliveryPhoneAdd']);
			unset($_SESSION['deliveryPerson']);
			$this->get('container')->setVar('title', 'Подтверждение заказа');
			return $this->render('cart/message.tpl');
		}
		$manager = $this->get('container')->getManager('Fuga:Common:Cart');
		$params = array(
			'wordEnd' => $manager->getTermination($this->get('util')->_sessionVar('number', true, 0)),
			'items' => $_SESSION['cart'],
			'gifts' => $manager->getGifts(),
			'discount' => $manager->getDiscount(),
			'totalPrice' => $manager->getTotalPrice(),
			'totalPriceRus' => $manager->getTotalPriceRus(),
			'totalPriceDiscount' => $manager->getTotalPriceDiscount(),
			'user' => $this->get('auth')->getUser(),
			'payType' => $this->get('connection')->getItem('pay', 'SELECT name FROM cart_pay_type WHERE id='.$this->get('util')->_sessionVar('payType')),
			'deliveryType' => $this->get('connection')->getItem('delivery', 'SELECT name FROM cart_delivery_type WHERE id='.$this->get('util')->_sessionVar('deliveryType'))
		);
		$this->get('container')->setVar('title', 'Подтверждение заказа');
		
		return $this->render('cart/confirm.tpl', $params);
	}

	public function authorizeAction() {
		$user = $this->get('auth')->getUser();
		if ($user) {
			header('location: '.$this->get('container')->href('cart', 'detail'));
			exit;
		}
		$this->get('container')->setVar('title', 'Авторизация');
		return $this->render('cart/authorize.tpl');
	}

	public function detailAction() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$_SESSION['payType'] = $this->get('util')->_postVar('payType');
			$_SESSION['deliveryType'] = $this->get('util')->_postVar('deliveryType');
			$_SESSION['deliveryAddress'] = $this->get('util')->_postVar('deliveryAddress');
			$_SESSION['deliveryPerson'] = $this->get('util')->_postVar('deliveryPerson');
			$_SESSION['deliveryEmail'] = $this->get('util')->_postVar('deliveryEmail');
			$_SESSION['deliveryPhone'] = $this->get('util')->_postVar('deliveryPhone');
			$_SESSION['deliveryPhoneAdd'] = $this->get('util')->_postVar('deliveryPhoneAdd');
			header('location: '.$this->get('container')->href('cart', 'confirm'));
		}
		$user = $this->get('auth')->getUser();
		if (empty($_SESSION['deliveryEmail'])) {
			$_SESSION['deliveryEmail'] = $user ? $user['email'] : '';
		}
		$payTypes = $this->get('connection')->getItems('get_pay', "SELECT id,name FROM cart_pay_type WHERE publish=1 ORDER BY sort");
		$deliveryTypes = $this->get('connection')->getItems('get_delivery', "SELECT id,name,description FROM cart_delivery_type WHERE publish=1 ORDER BY sort");
		$this->get('container')->setVar('title', 'Оплата и доставка');
		return $this->render('cart/detail.tpl', compact('payTypes', 'deliveryTypes', 'user'));
	}
	
	public function widgetAction() {
		$wordEnd = $this->get('container')->getManager('Fuga:Common:Cart')
				->getTermination($this->get('util')->_sessionVar('number', true, 0));
		return $this->render('cart/widget.tpl', compact('wordEnd'));
	}

}
