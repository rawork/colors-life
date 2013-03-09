<?php

namespace Fuga\CommonBundle\Model;

class UserManager extends ModelManager {
	
	protected $entityTable = 'auth_users';
	
	private $currentUser;
	
	public function getCurrentUser() {
		if (!$this->currentUser) {
			$user = $this->get('container')->getItem('auth_users', "session_id='".session_id()."'");
			$this->currentUser = count($user) ? $user : null;
		}
		return $this->currentUser;
	}
	
	public function add($data) {
		
	}
	
	public function update($id, $data) {
		
	}
	
}

