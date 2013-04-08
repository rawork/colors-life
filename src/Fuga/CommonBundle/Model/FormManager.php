<?php

namespace Fuga\CommonBundle\Model;

use Fuga\Component\Form\FormBuilder;

class FormManager extends ModelManager {
	
	private $params;
	
	public function __construct() {
		$params = $this->get('container')->getManager('Fuga:Common:Param')->findAll('form');
		$this->params = array();
		foreach ($params as $param) {
			$this->params[$param['name']] = $param['type'] == 'int' ? intval($param['value']) : $param['value'];
		}
	}
	
	public function getForm($name){
		$this->params = array_merge($this->params);
		$content = '';
		$formData = $this->get('container')->getItem('form_form', "name='{$name}'");
		$tableName = !empty($params['table']) ? $params['table'] : '';
		if (count($formData)) {
			$formData['fields'] = $this->get('container')->getItems('form_field', 'form_id='.$formData['id']);
			$form = new FormBuilder('', $formData);
			$form->items = $formData['fields'];
			$form->message = $this->processForm($form, $tableName);
			if ($form->message[0] == 'error')
				$form->fillGlobals();
			$content .= $form->getText();
		}
		return $content;
	}

	private function processForm($form, $tableName = null) {
		$message = array('', '');
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if($form->defense && $this->get('util')->_sessionVar('captchaHash') != md5($this->get('util')->_postVar('securecode'))){
				$message[0] = 'error';
				$message[1] = $this->params['no_antispam'];
			} else {
				$message = $form->sendMail($this->params);
				if (empty($message[0])){
					$message[0] = 'accept';
					$message[1] = $this->params['text_inserted'];
					if ($tableName)
						$this->get('container')->addItemGlobal($tableName);
				}
			}
			unset($_SESSION['captcha_keystring']);
		}
		return $message;
	}

}
