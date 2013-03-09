<?php

namespace Fuga\CommonBundle\Model;

use Fuga\Component\Form\FormBuilder;

class FormManager extends ModelManager {
	
	private $params;
	
	public function __construct() {
		$settings = $this->get('connection')->getItems('unit.settings', "SELECT * FROM config_settings WHERE module='form'");
		$this->params = array();
		foreach ($settings as $setting) {
			$this->params[$setting['name']] = $setting['type'] == 'int' ? intval($setting['value']) : $setting['value'];
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
			if($form->defense && $this->get('util')->_sessionVar('captchaHash') != md5($this->get('util')->_postVar('securecode').__CAPTCHA_HASH)){
				$message[0] = 'error';
				$message[1] = $this->params['no_antispam'];
			} else {
				$message = $form->sendMail($this->params);
				if (empty($message[0])){
					$message[0] = 'accept';
					$message[1] = $this->params['text_inserted'];
					if ($tableName)
						$this->get('container')->addGlobalItem($tableName);
				}
			}
			unset($_SESSION['captcha_keystring']);
		}
		return $message;
	}

}
