<?php

namespace AdminInterface\Action;

class AlterAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	function getText() {
		$this->messageAction($this->dataTable->alter() ? 'Структура таблицы синхронизирована' : 'Ошибка синхронизации структуры таблицы');
	}
}
