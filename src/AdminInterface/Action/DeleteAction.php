<?php

namespace AdminInterface\Action;

class DeleteAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$q = 'id='.$this->get('router')->getParam('id');
		$this->messageAction($this->get('container')->deleteItem($this->dataTable->getDBTableName(), $q) ? 'Удалено' : 'Ошибка удаления');
	}
}
