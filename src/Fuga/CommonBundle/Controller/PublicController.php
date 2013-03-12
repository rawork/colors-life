<?php

namespace Fuga\CommonBundle\Controller;

class PublicController extends Controller {
	
	public $params;
	
	function __construct($name) {
		$settings = $this->get('connection')->getItems('unit.settings', "SELECT * FROM config_settings WHERE module='$name'");
		$this->params = array();
		foreach ($settings as $setting) {
			$this->params[$setting['name']] = $setting['type'] == 'int' ? intval($setting['value']) : $setting['value'];
		}
	}
	
	public function getParam($name) {
		return $this->params[$name];
	}

}
