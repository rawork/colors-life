<?php

namespace AdminInterface\Action;

class BackupdeleteAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$file = $this->get('util')->_getVar('file');
		@unlink($GLOBALS['PRJ_DIR'].$file);
		header('location: '.$this->fullRef.'/backup');
	}
}
