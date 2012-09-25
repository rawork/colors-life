<?php

namespace AdminInterface\Action;

class SendAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$this->messageAction($this->uai->module->everyMin() ? 'Ошибки при рассылке' : 'Рассылка сделана');
	}
}
