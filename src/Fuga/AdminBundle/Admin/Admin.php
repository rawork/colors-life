<?php

namespace Fuga\AdminBundle\Admin;

class Admin {
	
	public $name;
	public $params;

	function __construct($name) {
		$this->name = $name;
		$params = $this->get('connection')->getItems('unit.settings', "SELECT * FROM config_settings WHERE module='$name'");
		$this->params = array();
		foreach ($params as $param) {
			$this->params[$param['name']] = ($param['type'] == 'int' ? intval($param['value']) : $param['value']);
		}
	}

	public function get($name) {
		global $container;
		if ($name == 'container') {
			return $container;
		} else {
			return $container->get($name);
		}
	}
}
