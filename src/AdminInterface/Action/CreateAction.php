<?php

namespace AdminInterface\Action;

class CreateAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$this->messageAction($this->dataTable->create() ? 'Таблица создана' : 'Ошибка создания таблицы');
	}
}
