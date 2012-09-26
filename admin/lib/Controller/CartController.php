<?php

namespace Controller;

use Common\Mail;

if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = array();
	$_SESSION['number'] = 0;
	$_SESSION['summa'] = 0;
}

class CartController extends Controller {

	private $_iBaseNumber = 100000;
	public $lang;

	public function __construct() {
		parent::__construct('cart');
	}

	private function _add($id, $amount = 1, $price = '', $priceId = 0) {
		$amount = intval($amount);
		if ($product = $this->get('container')->getItem('catalog_stuff', $id)) {
			if ($priceId) {
				$priceData = $this->get('container')->getItem('catalog_prices', $priceId);
			} else {
				$priceData  = array();
			}
			//var_dump($id, $amount, $price, $price_id, $aPriceEntity);
			if ($price == '0.00') {
				$price = $product['spec_price'] == '0.00' ? $product['price'] : $product['spec_price'];
			}
			$guid = md5($product['id'].$price);
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
			$this->get('smarty')->assign('add_name', $product['name']);
			$this->get('smarty')->assign('ok', 1);
		}
		return $this->get('smarty')->fetch('service/cart/'.$this->lang.'/add.tpl');
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
		$discountData = $this->get('container')->getItem('cart_discount', "publish='on' AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
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

	function getList($editable = true) {
		if ($this->get('util')->_postVar('recalculate')) {
			$this->_recalculateCartItems();
			header('location: /cart/');
		}

		$sessionItems = $_SESSION['cart'];
		$cartItems = array();
		$itemIds = array();
		foreach ($sessionItems as $sGUID => $item) {
			$cartItems[$sGUID] = $item;
			$itemIds[] = $item['stuff']['id'];
		}
		$totalPrice = $this->getTotalPrice();
		$this->get('smarty')->assign("list_total_rus", (string)$this->getTotalPriceRus());
		$this->get('smarty')->assign("list_total", (string)$totalPrice);
		$this->get('smarty')->assign('aItems', $cartItems);
		$gifts = $this->get('container')->getItems('catalog_gifts', "stuff_id IN (".implode(',', $itemIds).")");
		$gifts2 = $this->get('container')->getItems('cart_gifts', "publish='on' AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
		$this->get('smarty')->assign('gifts', array_merge($gifts, $gifts2));
		$this->get('smarty')->assign('discount', $this->getDiscount());
		$this->get('smarty')->assign('totalPriceDiscount', $this->getTotalPriceDiscount());
		if ($editable) {
			return $this->get('smarty')->fetch('service/cart/'.$this->lang.'/list.tpl');
		} else {

			return $this->get('smarty')->fetch('service/cart/'.$this->lang.'/confirm.tpl');
		}
	}

	private function _recalculateCartItems() {
		$new_cart = array();
		$number = 0;
		foreach ($_SESSION['cart'] as $id => $stuffitem) {
			if (isset($_REQUEST['amount_'.$id])) {
				$stuffitem['counter'] = $_REQUEST['amount_'.$id];
			}
			if (!empty($_REQUEST['price_'.$id])) {
				$aPriceEntity = $this->get('container')->getItem('catalog_prices', $_REQUEST['price_'.$id]);
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
		$aUser = $this->get('auth')->getUser();
		$aPayType		= $this->get('connection')->getItem('pay', 'SELECT name FROM cart_pay_type WHERE id='.$this->get('util')->_sessionVar('payType'));
		$aDeliveryType	= $this->get('connection')->getItem('delivery', 'SELECT name FROM cart_delivery_type WHERE id='.$this->get('util')->_sessionVar('deliveryType'));
		$this->get('smarty')->assign('sPayType', $aPayType['name']);
		$this->get('smarty')->assign('sDeliveryType', $aDeliveryType['name']);
		$sOrderText = $this->_getOrderText();
		$this->tables["order"]->insert(
				"user_id, counter, summa, discount, status, fio, email, phone, phone2, pay_type, delivery_type, address, additions, order_txt, credate",
				($aUser ? $aUser['id'] : 0).
				",'".$_SESSION['number'].
				"','".$this->getTotalPriceDiscount().
				"','".$this->getDiscount().
				"','Новый','".$this->get('util')->_sessionVar('deliveryPerson').
				"','".$this->get('auth')->getLogin().
				"','".$this->get('util')->_sessionVar('deliveryPhone').
				"','".$this->get('util')->_sessionVar('deliveryPhoneAdd').
				"','".$aPayType['name'].
				"','".$aDeliveryType['name'].
				"','".$this->get('util')->_sessionVar('deliveryAddress').
				"','".$this->get('util')->_postVar('deliveryComment').
				"',"."'".$sOrderText."',NOW()");
		$iLastId = $this->get('connection')->getInsertID();

		$iCurrentOrderNumber = $this->_iBaseNumber + $iLastId;
		$this->get('smarty')->assign('order_number', $iCurrentOrderNumber);
		$sOrderText = $this->get('smarty')->fetch('service/cart/'.$this->lang.'/order.mail.tpl');
		$mail = new Mail();
		$mail->From($this->getOrderEmail());
		$mail->Subject('Заказ №'.$iCurrentOrderNumber.' от '.date('d.m.Y H:i'));
		$mail->Html("Заказ №$iCurrentOrderNumber от ".date('d.m.Y H:i')."<br>".$sOrderText, "UTF-8");
		$mail->To($this->getOrderEmail());
		$mail->Send();
		$sEmail = $this->get('util')->_sessionVar('deliveryEmail');
		if ($sEmail) {
			$mail->Subject('Цвета жизни - заказ №'.$iCurrentOrderNumber.' принят к обработке');
			$mail->Html($sOrderText, 'UTF-8');
			$mail->To(array($sEmail));
			$mail->Send();
		}
	}

	private function _getOrderText() {
		$cart = $_SESSION['cart'];
		$stuff_ids = array();
		$sItemList = '';
		foreach ($cart as $i) {
			$sItemList .= "[".$i['stuff']['id']."] ".$i['stuff']['name']." ".(!isset($i['priceEntity']['id']) ? '' : "(Вариант исполнения:{$i['priceEntity']['size_id_name']} - {$i['priceEntity']['color_id_name']})")." \tПроизводитель: ".$i['stuff']['producer_id_name']."\t".number_format($i['price'], 2, ',', ' ')." руб.\t".$i['counter']."\n";
			$stuff_ids[] = $i['stuff']['id'];
		}
//          $gifts = $this->get('container')->getItems('catalog_gifts', "stuff_id IN (".implode(',', $stuff_ids).")");
//			$this->smarty->assign('gifts', $gifts);
//			$gifts2 = $this->get('container')->getItems('cart_gifts', "publish='on' AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
//			$gifts = array_merge($gifts, $gifts2);
//			$discount = $this->get('container')->getItem('cart_discount', "publish='on' AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
//			$this->smarty->assign('discount', $discount);
//			$ret .= "Подарки:\n";
//			foreach ($gifts as $gift) {
//                $ret .= $gift['gift_id_name']."\n";
//          }
		return $sItemList;
	}

	public function isEmpty() {
		return count($_SESSION["cart"]) <= 0;
	}

	private function _getConfirmPage() {
		if (count($_SESSION['cart']) && $this->get('util')->_postVar('submited')) {
			$this->_addOrder();
			$_SESSION['cart'] = array();
			$_SESSION['number'] = 0;
			$_SESSION['summa'] = $this->getTotalPriceRus();
			unset($_SESSION['deliveryAddress']);
			unset($_SESSION['deliveryEmail']);
			unset($_SESSION['deliveryPhone']);
			unset($_SESSION['deliveryPhoneAdd']);
			unset($_SESSION['deliveryPerson']);
			return $this->getTpl('service/cart/'.$this->lang.'/message');
		} else {
			$aPayType		= $this->get('connection')->getItem('pay', 'SELECT name FROM cart_pay_type WHERE id='.$this->get('util')->_sessionVar('payType'));
			$aDeliveryType	= $this->get('connection')->getItem('delivery', 'SELECT name FROM cart_delivery_type WHERE id='.$this->get('util')->_sessionVar('deliveryType'));
			$this->get('smarty')->assign('sPayType', $aPayType['name']);
			$this->get('smarty')->assign('sDeliveryType', $aDeliveryType['name']);
			return $this->getList(false);
		}
	}

	private function _getAuthorizePage() {
		return $this->get('smarty')->fetch('service/cart/'.$this->lang.'/authorize.tpl');
	}

	private function _getDetailPage() {
		if ($this->get('util')->_postVar('processDetail')) {
			$_SESSION['payType'] = $this->get('util')->_postVar('payType');
			$_SESSION['deliveryType'] = $this->get('util')->_postVar('deliveryType');
			$_SESSION['deliveryAddress'] = $this->get('util')->_postVar('deliveryAddress');
			$_SESSION['deliveryPerson'] = $this->get('util')->_postVar('deliveryPerson');
			$_SESSION['deliveryEmail'] = $this->get('util')->_postVar('deliveryEmail');
			$_SESSION['deliveryPhone'] = $this->get('util')->_postVar('deliveryPhone');
			$_SESSION['deliveryPhoneAdd'] = $this->get('util')->_postVar('deliveryPhoneAdd');
			header('location: /cart/confirm.htm');
		}
		$this->get('smarty')->assign('aPayTypes', $this->get('connection')->getItems('get_pay', "SELECT id,name FROM cart_pay_type WHERE publish='on' ORDER BY ord"));
		$this->get('smarty')->assign('aDeliveryTypes', $this->get('connection')->getItems('get_delivery', "SELECT id,name,description FROM cart_delivery_type WHERE publish='on' ORDER BY ord"));
		if (empty($_SESSION['deliveryEmail'])) {
			$_SESSION['deliveryEmail'] = $this->get('auth')->user ? 
				$this->get('auth')->user['email']
				:
				'';
		}
		return $this->get('smarty')->fetch('service/cart/'.$this->lang.'/detail.tpl');
	}

	public function getBody() {
		switch ($this->get('router')->getParam('methodName')) {
			case "confirm":
				return $this->_getConfirmPage();
			case "detail":
				return $this->_getDetailPage();
			case "authorize":
				return $this->_getAuthorizePage();
			default:
				return $this->getList();
		}
	}
}
