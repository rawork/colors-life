<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;

class AjaxController extends Controller {	
	
	public function voteProcess($voteName, $formData = null) {
		$elements = null;
		if ($formData) {
			parse_str($formData, $elements);
		}
		return json_encode(array('content' => $this->getManager('Fuga:Common:Vote')->getResult($voteName, $elements)));
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
				$message = $this->getManager('Fuga:Common:Maillist')->unsubscribe($email);
			} elseif ($subscribe_type == 1) {
				$message = $this->getManager('Fuga:Common:Maillist')->subscribe($email, $name, $lastname);
			}
		}
		return json_encode(array('content' => $this->render('subscribe/result.tpl', $message)));
	}
	
	public function sendStuffExist($productId, $email) {
		$sql = 'SELECT id,name FROM catalog_product WHERE id= :id ';
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->bindValue("id", $productId);
		$stmt->execute();
		$product = $stmt->fetch();
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
	
}
