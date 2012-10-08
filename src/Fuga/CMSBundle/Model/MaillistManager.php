<?php

namespace Fuga\CMSBundle\Model;

class MaillistManager extends ModelManager {
	
	protected $entityTable = 'maillist_lists';
	protected $subscriberTable = 'maillist_users';

	public function processMessage() {
		$letter = $this->get('container')->getItem($this->entityTable, 'TO_DAYS(date) <= TO_DAYS(NOW())');
		if ($letter) {
			$emails = array();
			$subscribers = $this->get('container')->getItems($this->subscriberTable, "is_active='on'");
			foreach ($subscribers as $subscriber) {
				$emails[] = $subscriber['email'];
			}
			if ($letter['file']) {
				$this->get('mailer')->attach($letter['file']);
			}
			$this->get('mailer')->send(
				$letter['subject'],
				$letter['message'],
				$emails	
			);
			
			$this->get('container')->deleteItem($this->entityTable, 'id='.$letter['id']);
		}
	}
	
	public function subscribe($email, $name, $lastname) {
		$email = trim($email);
		$subscriber = $this->get('container')->getItem('maillist_users', "email='".$this->get('connection')->escapeStr($email)."'");
		if ($subscriber) {
			$message = array(
				'message' => 'Адрес '.htmlspecialchars($email).' уже есть в списке рассылки',
				'success' => false
			);	
		} elseif ($this->get('container')->addItem('maillist_users', 'lastname,name,email, date, is_active', "'".addslashes($lastname)."','".addslashes($name)."','".addslashes($email)."', NOW(), ''")) {
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
			$message = array(
				'message' => 'Адрес '.htmlspecialchars($email).' занесен в список рассылки',
				'success' => true
			);	
		} else {
			$message = array(
				'message' => 'Ошибка базы данных при добавлении',
				'success' => false
			);
		}
		return $message;
	}
	
	public function unsubscribe($email) {
		$email = trim($email);
		$subscriber = $this->get('container')->getItem('maillist_users', "email='".addslashes($email)."'");
		if (!$subscriber) {
			$message = array(
				'message' => 'Адреса '.htmlspecialchars($email).' нет в списке рассылки',
				'success' => false
			);	
		} elseif ($this->get('connection')->execQuery('delete_user_maillist', "DELETE FROM maillist_users WHERE email='".addslashes($email)."'")) {
			$message = array(
				'message' => 'Адрес '.htmlspecialchars($email).' удален из списка рассылки',
				'success' => true
			);	
		} else {
			$message = array(
				'message' => 'Ошибка базы данных при удалении',
				'success' => false
			);	
		}
		return $message;
	}
}
