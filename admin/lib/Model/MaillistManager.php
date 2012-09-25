<?php

namespace Model;

use Common\Mail;

class MaillistManager extends ModelManager {
	
	protected $entityTable = 'maillist_lists';
	protected $subscriberTable = 'maillist_users';

	function getForm() {
		return $this->get('smarty')->fetch('service/'.$this->get('router')->getParam('lang').'/subscribe.form.tpl');
	}

	function everyMin() {
	global $ADMIN_EMAIL, $PRJ_DIR;
		$this->tables['lists']->selectWhere('TO_DAYS(date) <= TO_DAYS(NOW())');
		if ($letter = $this->get('container')->getItem($this->entityTable)) {

			$mail = new Mail();
			$mail->From($ADMIN_EMAIL);
			$mail->Subject($letter['subj']);
			$mail->SetCharset('UTF-8');
			$mail->Html($letter['body']);
			if (is_file($PRJ_DIR.$letter['file'])) {
				$mail->AttachFile($PRJ_DIR.$letter['file']);
			}

			$subscribers = $this->get('container')->getItems($this->subscriberTable, "is_active='on'");
			foreach ($subscribers as $subscriber) {
				$mail->To(array($subscriber['email']));
				$mail->Send();
			}
			$this->get('container')->deleteItem($this->entityTable, 'id='.$letter['id']);
			
			@unlink($PRJ_DIR.$letter['file']);
		}
	}
}
