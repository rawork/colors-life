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
}
