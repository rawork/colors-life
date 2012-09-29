<?php

namespace AdminInterface\Action;

class CopyAction extends Action {
	
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	
	function getText() {
		set_time_limit(0);
		$this->messageAction($this->get('container')->dublicateItem($this->dataTable->getDBTableName(), $this->get('router')->getParam('id'), $this->get('util')->_getVar('quantity', true, 1)) ? 'Скопировано' : 'Ошибка копирования');
	}

}
