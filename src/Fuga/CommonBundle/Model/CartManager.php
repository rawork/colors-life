<?php

namespace Fuga\CommonBundle\Model;

class CartManager extends ModelManager {
	
	public function add($id, $quantity = 1, $price = 0, $priceId = 0) {
		$quantity = intval($quantity);
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
				$_SESSION["cart"][$guid]["counter"] = intval($_SESSION["cart"][$guid]["counter"]) + $quantity;
			} else {
				$_SESSION["cart"][$guid] = array (
					"stuff" => $product,
					"price" => $price,
					"priceEntity" => $priceData,
					"counter" => $quantity
				);
			}
			$_SESSION['number'] = $_SESSION['number'] + $quantity;
			$_SESSION['summa'] = $this->getTotalPriceRus();
			
			return $product;
		}
	}
	
	public function delete($GUID) {
		$product		 = $_SESSION['cart'][$GUID];
		$quantity		 = $_SESSION['number'];
		$sum			 = preg_replace('/(\s|,00)+/i', '', $_SESSION['summa']);
		$productQuantity = $product['counter'];
		$productSum		 = $product['price'] * $productQuantity;
		$_SESSION['number'] = $quantity - $productQuantity;
		$_SESSION['summa']	= number_format($sum - $productSum, 2, ',', ' ');
		unset($_SESSION['cart'][$GUID]);
	}
	
	public function update() {
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
	
	public function isEmpty() {
		return count($_SESSION["cart"]) == 0;
	}
	
	public function getOrderDetail($orderId) {
		$order = $this->get('container')->getTable('cart_order')->getItem($orderId);
		return nl2br($order['order_txt']);

	}
	
	public function getOrderText() {
		$content = '';
		foreach ($_SESSION['cart'] as $item) {
			$content .= "[".$item['stuff']['id']."] ".
				$item['stuff']['name']." ".
				(!isset($item['priceEntity']['id']) ? '' : "(Вариант исполнения:{$item['priceEntity']['size_id_name']} - {$item['priceEntity']['color_id_name']})").
				(isset($item['stuff']['producer_id_name']) ? " \tПроизводитель: ".$item['stuff']['producer_id_name'] : '').
				"\t".number_format((float)$item['price'], 2, ',', ' ')." руб.\t".$item['counter']."\n";
		}
		return $content;
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
		$user = $this->get('container')->getManager('Fuga:Common:Account')->getCurrentUser();
		if ($user) {
			$discount = $user['discount'];
		}
		$discountData = $this->get('container')->getItem('cart_discount', "publish=1 AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
		if ($discountData && intval($discountData['discount']) > $discount) {
			$discount = $discountData['discount'];
		}
		
		return $discount;
	}

	public function getTotalPriceDiscount() 
	{
		$totalPrice = $this->getTotalPrice();
		$totalPrice -= $totalPrice*$this->getDiscount()/100;

		return number_format($totalPrice, 2, ',', ' ');
	}

	public function getTotalPriceRus() {
		return number_format($this->getTotalPrice(), 2, ',', ' ');
	}
	
	public function getTermination($quantity) {
		$term = '';
		$quantity2 = substr($quantity, -2);
		$quantity = substr($quantity, -1);
        if($quantity == 1 ) {$term = "";}
        if($quantity > 1 ) {$term = "а";}
		if($quantity2 > 10 && $quantity2 < 15) {$term = "ов";}
        if($quantity > 4 || $quantity == 0) {$term = "ов";}
		return $term;
	}
	
	public function getGifts() {
		$totalPrice = $this->getTotalPrice();
		$itemIds = array();
		foreach ($_SESSION['cart'] as $item) {
			$itemIds[] = $item['stuff']['id'];
		}
		$gifts = $this->get('container')->getItems('catalog_gift', "product_id IN (".implode(',', $itemIds).")");
		$gifts2 = $this->get('container')->getItems('cart_gift', "publish=1 AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
		return array_merge($gifts, $gifts2);
	}
	
	public function getDelivery() {
		$sql = 'SELECT id,name FROM cart_delivery_type WHERE id= :id ';
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->bindValue('id', $this->get('util')->_sessionVar('deliveryType'));
		$stmt->execute();
		return $stmt->fetch();
	}
	
	public function getPay() {
		$sql = 'SELECT id,name FROM cart_pay_type WHERE id= :id ';
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->bindValue('id', $this->get('util')->_sessionVar('payType'));
		$stmt->execute();
		return $stmt->fetch();
	}
	
	public function getDeliveries() {
		$sql = "SELECT id,name,description FROM cart_delivery_type WHERE publish=1 ORDER BY sort";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}
	
	public function getPays() {
		$sql = "SELECT id,name FROM cart_pay_type WHERE publish=1 ORDER BY sort";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}
	
}

