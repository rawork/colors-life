<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CMSBundle\Controller\PublicController;

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

	private function _add($id, $amount = 1, $price = '', $priceId = 0) {
		$amount = intval($amount);
		if ($product = $this->get('container')->getItem('catalog_product', $id)) {
			if ($priceId) {
				$priceData = $this->get('container')->getItem('catalog_price', $priceId);
			} else {
				$priceData  = array();
			}
			if ($price == '0.00') {
				$price = $product['discount_price'] == '0.00' ? $product['price'] : $product['discount_price'];
			}
			$guid = md5($product['id'].$price.$priceId);
			if (isset($_SESSION["cart"][$guid])) {
				$_SESSION["cart"][$guid]["counter"] = intval($_SESSION["cart"][$guid]["counter"]) + $amount;
			} else {
				$_SESSION["cart"][$guid] = array (
					"stuff" => $product,
					"price" => $price,
					"priceEntity" => $priceData,
					"counter" => $amount
				);
			}
			$_SESSION['number'] = $_SESSION['number'] + $amount;
			$_SESSION['summa'] = $this->getTotalPriceRus();
			
			return $product;
		}
	}

	public function addCartItem($productId, $quantity = 1, $price = 0, $priceId = 0) {
		if ($product = $this->_add($productId, $quantity, $price, $priceId)) {
			$productName = $product['name'];
			$ok = 1;
		}
		
		return $this->render('service/cart/add.tpl', compact('productName', 'ok'));
	}
	
	public function deleteItem($guid) {
		$product = $_SESSION['cart'][$guid];
		$quantity	= $_SESSION['number'];
		$sum		= preg_replace('/(\s|,00)+/i', '', $_SESSION['summa']);
		$productQuantity = $product['counter'];
		$productSum = $product['price'] * $productQuantity;
		unset($_SESSION['cart'][$guid]);
		$_SESSION['number'] = $quantity - $productQuantity;
		$_SESSION['summa']	= number_format($sum - $productSum, 2, ',', ' ');
	}

	public function getOrderDetail($orderId) {
		$order = $this->get('container')->getTable('cart_order')->getItem($orderId);
		return nl2br($order['order_txt']);

	}
	
	public function getTotalQuantity() {
		return $this->get('util')->_sessionVar('number', true, 0);
	}

	public function getTotalPrice() {
		$cartItems = $_SESSION['cart'];
		$totalPrice = 0.0;
		foreach ($cartItems as $item) {
			$totalPrice += $item["price"] * $item["counter"];
		}
		return $totalPrice;
	}
	
	public function getDiscount() 
	{
		$totalPrice = $this->getTotalPrice();
		$discount = 0;
		$user = $this->get('auth')->getUser();
		if ($user) {
			$discount = $user['discount'];
		}
		$discountData = $this->get('container')->getItem('cart_discount', "publish=1 AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
		if ($discountData && intval($discountData['discount']) > $discount) {
			$discount = $discountData['discount'];
		}
		
		return $discount;
	}

	function getTotalPriceDiscount() 
	{
		$totalPrice = $this->getTotalPrice();
		$totalPrice -= $totalPrice*$this->getDiscount()/100;

		return number_format($totalPrice, 2, ',', ' ');
	}

	function getTotalPriceRus() {
		return number_format($this->getTotalPrice(), 2, ',', ' ');
	}
	
	function getTermination($quantity) {
		$term = '';
		$quantity2 = substr($quantity, -2);
		$quantity = substr($quantity, -1);
        if($quantity == 1 ) {$term = "";}
        if($quantity > 1 ) {$term = "а";}
		if($quantity2 > 10 && $quantity2 < 15) {$term = "ов";}
        if($quantity > 4 || $quantity == 0) {$term = "ов";}
		return $term;
	}

	function indexAction($editable = true) {
		if ($this->get('util')->_postVar('recalculate')) {
			$this->_recalculateCartItems();
			header('location: /cart/');
		}
		$sessionItems = $_SESSION['cart'];
		$cartItems = array();
		$itemIds = array();
		foreach ($sessionItems as $GUID => $item) {
			$cartItems[$GUID] = $item;
			$itemIds[] = $item['stuff']['id'];
		}
		$totalPrice = $this->getTotalPrice();
		$gifts = $this->get('container')->getItems('catalog_gift', "product_id IN (".implode(',', $itemIds).")");
		$gifts2 = $this->get('container')->getItems('cart_gift', "publish=1 AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
		$this->get('templating')->setParams(array(
			'wordEnd' => $this->getTermination($this->get('util')->_sessionVar('number', true, 0)),
			'items' => $cartItems,
			'gifts' => array_merge($gifts, $gifts2),
			'discount' => $this->getDiscount(),
			'totalPrice' => (string)$totalPrice,
			'totalPriceRus' => (string)$this->getTotalPriceRus(),
			'totalPriceDiscount' => $this->getTotalPriceDiscount(),
			'user' => $this->get('auth')->getUser()
		));
		if ($editable) {
			return $this->render('service/cart/list.tpl');
		} else {
			$payType		= $this->get('connection')->getItem('pay', 'SELECT name FROM cart_pay_type WHERE id='.$this->get('util')->_sessionVar('payType'));
			$deliveryType	= $this->get('connection')->getItem('delivery', 'SELECT name FROM cart_delivery_type WHERE id='.$this->get('util')->_sessionVar('deliveryType'));
			return $this->render('service/cart/confirm.tpl', compact('payType', 'deliveryType'));
		}
	}

	private function _recalculateCartItems() {
		$new_cart = array();
		$number = 0;
		foreach ($_SESSION['cart'] as $id => $stuffitem) {
			if (isset($_POST['amount_'.$id])) {
				$stuffitem['counter'] = $_POST['amount_'.$id];
			}
			if (!empty($_POST['price_'.$id])) {
				$aPriceEntity = $this->get('container')->getItem('catalog_price', $_POST['price_'.$id]);
				$stuffitem['priceEntity'] = $aPriceEntity;
				$stuffitem['price'] = $aPriceEntity['price'];
			}
			$new_guid = md5($stuffitem['stuff']['id'].$stuffitem['price']);
			if ($stuffitem['counter']) {
				if (isset($new_cart[$new_guid])) {
					$new_cart[$new_guid]['counter'] += $stuffitem['counter'];
				} else {
					$new_cart[$new_guid] = $stuffitem;
				}
			}
			$number += $stuffitem['counter'];
		}
		$_SESSION['number'] = $number;
		$_SESSION['cart'] = $new_cart;
		$_SESSION['summa'] = $this->getTotalPriceRus();
	}
	
	public function getOrderEmail() {
		return $this->params['email'];
	}

	private function _addOrder() {
		$user = $this->get('auth')->getUser();
		$payType		= $this->get('connection')->getItem('pay', 'SELECT id,name FROM cart_pay_type WHERE id='.$this->get('util')->_sessionVar('payType'));
		$deliveryType	= $this->get('connection')->getItem('delivery', 'SELECT id,name FROM cart_delivery_type WHERE id='.$this->get('util')->_sessionVar('deliveryType'));
		
		$orderText = $this->_getOrderText();
		$this->get('container')->addItem('cart_order',
				"user_id, counter, summa, discount, status, fio, email, phone, phone2, pay_type, delivery_type, address, additions, order_txt, created",
				($user ? $user['id'] : 0).
				",'".$_SESSION['number'].
				"','".$this->getTotalPriceDiscount().
				"','".$this->getDiscount().
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
			'discount' => $this->getDiscount(),
			'totalPrice' => $this->getTotalPriceRus(),
			'totalPriceDiscount' => $this->getTotalPriceDiscount(),
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
		$orderText = $this->render('service/cart/order.mail.tpl', $params);
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

	private function _getOrderText() {
		$cart = $_SESSION['cart'];
		$itemList = '';
		foreach ($cart as $item) {
			$itemList .= "[".$item['stuff']['id']."] ".
				$item['stuff']['name']." ".
				(!isset($item['priceEntity']['id']) ? '' : "(Вариант исполнения:{$item['priceEntity']['size_id_name']} - {$item['priceEntity']['color_id_name']})").
				(isset($item['stuff']['producer_id_name']) ? " \tПроизводитель: ".$item['stuff']['producer_id_name'] : '').
				"\t".number_format($item['price'], 2, ',', ' ')." руб.\t".$item['counter']."\n";
		}
		return $itemList;
	}

	public function isEmpty() {
		return count($_SESSION["cart"]) <= 0;
	}

	public function confirmAction() {
		if (count($_SESSION['cart']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->_addOrder();
			$_SESSION['cart'] = array();
			$_SESSION['number'] = 0;
			$_SESSION['summa'] = $this->getTotalPriceRus();
			unset($_SESSION['deliveryAddress']);
			unset($_SESSION['deliveryEmail']);
			unset($_SESSION['deliveryPhone']);
			unset($_SESSION['deliveryPhoneAdd']);
			unset($_SESSION['deliveryPerson']);
			return $this->render('service/cart/message.tpl');
		} else {
			return $this->indexAction(false);
		}
	}

	public function authorizeAction() {
		$user = $this->get('auth')->getUser();
		if ($user) {
			header('location: /cart/detail.htm');
		}
		return $this->render('service/cart/authorize.tpl');
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
			header('location: /cart/confirm.htm');
		}
		$user = $this->get('auth')->getUser();
		if (empty($_SESSION['deliveryEmail'])) {
			$_SESSION['deliveryEmail'] = $user ? $user['email'] : '';
		}
		$payTypes = $this->get('connection')->getItems('get_pay', "SELECT id,name FROM cart_pay_type WHERE publish=1 ORDER BY sort");
		$deliveryTypes = $this->get('connection')->getItems('get_delivery', "SELECT id,name,description FROM cart_delivery_type WHERE publish=1 ORDER BY sort");
		return $this->render('service/cart/detail.tpl', compact('payTypes', 'deliveryTypes', 'user'));
	}
	
	public function widgetAction() {
		$wordEnd = $this->getTermination($this->get('util')->_sessionVar('number', true, 0));
		return $this->render('service/cart/widget.tpl', compact('wordEnd'));
	}

	public function getContent() {
		switch ($this->get('router')->getParam('methodName')) {
			case "confirm":
				return $this->confirmAction();
			case "detail":
				return $this->detailAction();
			case "authorize":
				return $this->authorizeAction();
			case "widget":
				return $this->widgetAction();
			default:
				return $this->indexAction();
		}
	}
}
