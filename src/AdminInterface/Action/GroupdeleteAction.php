<?php

namespace AdminInterface\Action;    

class GroupdeleteAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	function getText() {
		$query = 'id IN('.$this->get('util')->_postVar('ids').') ';
		$this->messageAction($this->get('container')->deleteItem($this->dataTable->getDBTableName(), $query) ? 'Удалено' : 'Ошибка группового удаления');
	}
}
