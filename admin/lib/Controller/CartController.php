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

	private function _add($id, $amount = 1, $price = '', $price_id = 0) {
		$amount = intval($amount);
		if ($aStuff = $this->get('container')->getItem('catalog_stuff', $id)) {
			if ($price_id) {
				$aPriceEntity = $this->get('container')->getItem('catalog_prices', $price_id);
			} else {
				$aPriceEntity  = array();
			}
			//var_dump($id, $amount, $price, $price_id, $aPriceEntity);
			if ($price == '0.00') {
				$price = $aStuff['spec_price'] == '0.00' ? $aStuff['price'] : $aStuff['spec_price'];
			}
			$guid = md5($aStuff['id'].$price);
			if (isset($_SESSION["cart"][$guid])) {
				$_SESSION["cart"][$guid]["counter"] = intval($_SESSION["cart"][$guid]["counter"]) + $amount;
			} else {
				$_SESSION["cart"][$guid] = array (
					"stuff" => $aStuff,
					"price" => $price,
					"priceEntity" => $aPriceEntity,
					"counter" => $amount
				);
			}
			$_SESSION['number'] = $_SESSION['number'] + $amount;
			$_SESSION['summa'] = $this->getTotalPriceRus();
			return $aStuff;
		}
	}

	public function addCartItem($iStuffId, $iQuantity = 1, $fPrice, $iPriceId = 0) {
		if ($aItem = $this->_add($iStuffId, $iQuantity, $fPrice, $iPriceId)) {
			$this->get('smarty')->assign('add_name', $aItem['name']);
			$this->get('smarty')->assign('ok', 1);
		}
		return $this->get('smarty')->fetch('service/cart/'.$this->lang.'/add.tpl');
	}

	public function getOrderDetail($orderId) {
		$order = $this->get('container')->getTable('cart_order')->getItem($orderId);
		return nl2br($order['order_txt']);

	}

	function getTotalPrice() {
		$aCartItems = $_SESSION['cart'];
		$fPrice = 0.0;
		foreach ($aCartItems as $aCartItem) {
			$fPrice += $aCartItem["price"] * $aCartItem["counter"];
		}
		return $fPrice;
	}

	function getTotalPriceDiscount($discount = 0) {
		$aCartItems = $_SESSION['cart'];
		$fPrice = 0.0;
		foreach ($aCartItems as $aCartItem) {
			$fPrice += $aCartItem['price'] * $aCartItem["counter"];
		}
		return $fPrice;
	}

	function getTotalPriceRus() {
		return number_format($this->getTotalPrice(), 2, ',', ' ');
	}

	function getList($editable = true) {
		if ($this->get('util')->_postVar('recalculate')) {
			$this->_recalculateCartItems();
			header('location: /cart/');
		}

		$aSessionItems = $_SESSION['cart'];
		$aCartItems = array();
		$aItemIds = array();
		foreach ($aSessionItems as $sGUID => $aItem) {
			$aCartItems[$sGUID] = $aItem;
			$aItemIds[] = $aItem['stuff']['id'];
		}
		$totalPrice = $this->getTotalPrice();
		$this->get('smarty')->assign("list_total_rus", (string)$this->getTotalPriceRus());
		$this->get('smarty')->assign("list_total", (string)$totalPrice);
		$this->get('smarty')->assign('aItems', $aCartItems);
		$gifts = $this->get('container')->getItems('catalog_gifts', "stuff_id IN (".implode(',', $aItemIds).")");
		$gifts2 = $this->get('container')->getItems('cart_gifts', "publish='on' AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
		$this->get('smarty')->assign('gifts', array_merge($gifts, $gifts2));
		$discount = $this->get('container')->getItem('cart_discount', "publish='on' AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
		$this->get('smarty')->assign('discount', $discount);
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
				"user_id, counter, summa, status, fio, email, phone, phone2, pay_type, delivery_type, address, additions, order_txt, credate",
				($aUser ? $aUser['id'] : 0).
				",'".$_SESSION['number'].
				"','".$_SESSION['summa'].
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
