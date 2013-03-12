<?php

namespace Fuga\CommonBundle\Model;

class AccountManager extends ModelManager {
	
	protected $entityTable = 'account_user';
	private $currentUser;
	
	public function getCurrentUser() {
		if (!$this->currentUser) {
			$user = $this->get('container')->getItem($entityTable, "session_id='".session_id()."'");
			$this->currentUser = $user ?: null;
		}
		return $this->currentUser;
	}
	
	public function getPersonName() {
		$user = $this->getCurrentUser();
		
		return $user ? $user['name'].($user['lastname'] ? ' '.$user['lastname'] : '') : null;
	}

	public function getPhone() {
		$user = $this->getCurrentUser();
		
		return $user ? $user['phone'] : null;
	}

	public function getAddress() {
		$user = $this->getCurrentUser();
		
		return $user ? $user['phone'] : null;
	}

	public function getLogin() {
		$user = $this->getCurrentUser();

		return $user ? $user['email'] : null;
	}
	
}

