<?php

namespace AdminInterface\Action;

class DuplicateAction extends Action {
	
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	
	function getText() {
		set_time_limit(0);
		$this->messageAction($this->get('container')->dublicateItem($this->dataTable->getDBTableName(), $this->get('router')->getParam('id'), $this->get('util')->_getVar('quantity', true, 5)) ? 'Скопировано' : 'Ошибка копирования');
	}

}
