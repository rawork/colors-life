<?php
    inc_lib('components/Unit.php');
	if (!isset($_SESSION['cart'])) {
		$_SESSION['cart'] = array();
		$_SESSION['number'] = 0;
		$_SESSION['summa'] = 0;
	}

    class CartUnit extends Unit {
        
		private $_iBaseNumber = 100000;

		public function __construct($aProperties = array()) {
            parent::__construct('cart', $aProperties);
			$this->smarty->assign('sess_name', session_name());
			$this->smarty->assign('sess_id', session_id());
        }
		
        private function _add($id, $amount = 1, $price = '', $price_id = 0) {
            $amount = intval($amount);
            if ($aStuff = $GLOBALS['rtti']->getItem('catalog_stuff', $id)) {
				if ($price_id) {
					$aPriceEntity = $GLOBALS['rtti']->getItem('catalog_prices', $price_id);
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
                $this->smarty->assign('add_name', $aItem['name']);
                $this->smarty->assign('ok', 1);
            }
            return $this->smarty->fetch('service/cart/'.$this->props['lang'].'/add.tpl');
        }
		
		public function getOrderDetail($orderId) {
			$order = $GLOBALS['rtti']->getTable('cart_order')->getItem($orderId);
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
			if (CUtils::_postVar('recalculate')) {
				$this->_recalculateCartItems();
				header('location: /cart/');
			}
//			var_dump($_SESSION);
            $aSessionItems = $_SESSION['cart'];
            $aCartItems = array();
			$aItemIds = array();
            foreach ($aSessionItems as $sGUID => $aItem) {
                $aCartItems[$sGUID] = $aItem;
				$aItemIds[] = $aItem['stuff']['id'];
            }
			$totalPrice = $this->getTotalPrice();
            $this->smarty->assign("list_total_rus", (string)$this->getTotalPriceRus());
			$this->smarty->assign("list_total", (string)$totalPrice);
            $this->smarty->assign('aItems', $aCartItems);
			$gifts = $GLOBALS['rtti']->getItems('catalog_gifts', "stuff_id IN (".implode(',', $aItemIds).")");
			$gifts2 = $GLOBALS['rtti']->getItems('cart_gifts', "publish='on' AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
			$this->smarty->assign('gifts', array_merge($gifts, $gifts2));
			$discount = $GLOBALS['rtti']->getItem('cart_discount', "publish='on' AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
			$this->smarty->assign('discount', $discount);
			if ($editable) {
            	return $this->smarty->fetch('service/cart/'.$this->props['lang'].'/list.tpl');
			} else {

				return $this->smarty->fetch('service/cart/'.$this->props['lang'].'/confirm.tpl');
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
					$aPriceEntity = $GLOBALS['rtti']->getItem('catalog_prices', $_REQUEST['price_'.$id]);
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
            return $this->dbparams['email'];
        }

        private function _addOrder() {
			global $db;
			$aUser = $GLOBALS['uauth']->getUser();
			$aPayType		= $GLOBALS['db']->getItem('pay', 'SELECT name FROM cart_pay_type WHERE id='.CUtils::_sessionVar('payType'));
			$aDeliveryType	= $GLOBALS['db']->getItem('delivery', 'SELECT name FROM cart_delivery_type WHERE id='.CUtils::_sessionVar('deliveryType'));
			$this->smarty->assign('sPayType', $aPayType['name']);
			$this->smarty->assign('sDeliveryType', $aDeliveryType['name']);
			$sOrderText = $this->_getOrderText();
		    $this->tables["order"]->insert(
					"user_id, counter, summa, status, fio, email, phone, phone2, pay_type, delivery_type, address, additions, order_txt, credate",
					($aUser ? $aUser['id'] : 0).
					",'".$_SESSION['number'].
					"','".$_SESSION['summa'].
					"','Новый','".CUtils::_sessionVar('deliveryPerson').
					"','".$GLOBALS['uauth']->getLogin().
					"','".CUtils::_sessionVar('deliveryPhone').
					"','".CUtils::_sessionVar('deliveryPhoneAdd').
					"','".$aPayType['name'].
					"','".$aDeliveryType['name'].
					"','".CUtils::_sessionVar('deliveryAddress').
					"','".CUtils::_postVar('deliveryComment').
					"',"."'".$sOrderText."',NOW()");
			$iLastId = $db->getInsertID();

			$iCurrentOrderNumber = $this->_iBaseNumber + $iLastId;
			$this->smarty->assign('order_number', $iCurrentOrderNumber);
			$sOrderText = $this->smarty->fetch('service/cart/'.$this->props['lang'].'/order.mail.tpl');
            inc_lib('Mail.php');
			$oMail = new Mail();
            $oMail->From($this->getOrderEmail());
            $oMail->Subject('Заказ №'.$iCurrentOrderNumber.' от '.date('d.m.Y H:i'));
            $oMail->Html("Заказ №$iCurrentOrderNumber от ".date('d.m.Y H:i')."<br>".$sOrderText, "UTF-8");
            $oMail->To($this->getOrderEmail());
            $oMail->Send();
			$sEmail = CUtils::_sessionVar('deliveryEmail');
			if ($sEmail) {
				$oMail->Subject('Цвета жизни - заказ №'.$iCurrentOrderNumber.' принят к обработке');
				$oMail->Html($sOrderText, 'UTF-8');
				$oMail->To(array($sEmail));
				$oMail->Send();
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
//          $gifts = $GLOBALS['rtti']->getItems('catalog_gifts', "stuff_id IN (".implode(',', $stuff_ids).")");
//			$this->smarty->assign('gifts', $gifts);
//			$gifts2 = $GLOBALS['rtti']->getItems('cart_gifts', "publish='on' AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
//			$gifts = array_merge($gifts, $gifts2);
//			$discount = $GLOBALS['rtti']->getItem('cart_discount', "publish='on' AND sum_min < ".$totalPrice." AND sum_max > ".$totalPrice);
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
            if (count($_SESSION['cart']) && CUtils::_postVar('submited')) {
                $this->_addOrder();
                $_SESSION['cart'] = array();
    			$_SESSION['number'] = 0;
				$_SESSION['summa'] = $this->getTotalPriceRus();
				unset($_SESSION['deliveryAddress']);
				unset($_SESSION['deliveryEmail']);
				unset($_SESSION['deliveryPhone']);
				unset($_SESSION['deliveryPhoneAdd']);
				unset($_SESSION['deliveryPerson']);
                return $this->getTpl('service/cart/'.$this->props['lang'].'/message');
            } else {
				$aPayType		= $GLOBALS['db']->getItem('pay', 'SELECT name FROM cart_pay_type WHERE id='.CUtils::_sessionVar('payType'));
				$aDeliveryType	= $GLOBALS['db']->getItem('delivery', 'SELECT name FROM cart_delivery_type WHERE id='.CUtils::_sessionVar('deliveryType'));
				$this->smarty->assign('sPayType', $aPayType['name']);
				$this->smarty->assign('sDeliveryType', $aDeliveryType['name']);
                return $this->getList(false);
            }
        }
		
		private function _getAuthorizePage() {
			return $this->smarty->fetch('service/cart/'.$this->props['lang'].'/authorize.tpl');
		}
		
		private function _getDetailPage() {
			if (CUtils::_postVar('processDetail')) {
				$_SESSION['payType'] = CUtils::_postVar('payType');
				$_SESSION['deliveryType'] = CUtils::_postVar('deliveryType');
				$_SESSION['deliveryAddress'] = CUtils::_postVar('deliveryAddress');
				$_SESSION['deliveryPerson'] = CUtils::_postVar('deliveryPerson');
				$_SESSION['deliveryEmail'] = CUtils::_postVar('deliveryEmail');
				$_SESSION['deliveryPhone'] = CUtils::_postVar('deliveryPhone');
				$_SESSION['deliveryPhoneAdd'] = CUtils::_postVar('deliveryPhoneAdd');
				header('location: /cart/confirm.htm');
			}
			$this->smarty->assign('aPayTypes', $GLOBALS['db']->getItems('get_pay', "SELECT id,name FROM cart_pay_type WHERE publish='on' ORDER BY ord"));
			$this->smarty->assign('aDeliveryTypes', $GLOBALS['db']->getItems('get_delivery', "SELECT id,name,description FROM cart_delivery_type WHERE publish='on' ORDER BY ord"));
			if (empty($_SESSION['deliveryEmail'])) {
				$_SESSION['deliveryEmail'] = $GLOBALS['uauth']->user ? 
					$GLOBALS['uauth']->user['email']
					:
					'';
			}
			return $this->smarty->fetch('service/cart/'.$this->props['lang'].'/detail.tpl');
		}
		
        public function getBody() {
			switch ($this->props['method']) {
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

?>