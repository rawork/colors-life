<?php

namespace Fuga\AdminBundle\Action;

class BackupdeleteAction extends Action {
	
	public function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	
	public function getText() {
		$file = $this->get('util')->_getVar('file');
		unlink($GLOBALS['BACKUP_DIR'].'/'.$file);
		header('location: '.$this->fullRef.'/backup');
	}
}
