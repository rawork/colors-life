<?php

namespace AdminInterface\Action;

class TruncateAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$this->messageAction($this->get('container')->truncateClass($this->dataTable->getDBTableName()) ? 'Все записи таблицы удалены' : 'Ошибка удаления записей таблицы');
	}
}
