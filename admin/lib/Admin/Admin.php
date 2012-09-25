<?php

namespace Admin;

class Admin {
	
	public $tables;
	public $name;
	public $params;

	function __construct($name) {
		$this->name = $name;
		$this->addTables();
		$dbparams = $this->get('connection')->getItems('unit.settings', "SELECT * FROM config_settings WHERE komponent='$name'");
		$this->params = array();
		foreach ($dbparams as $param) {
			$this->params[$param['name']] = ($param['type'] == 'int' ? intval($param['value']) : $param['value']);
		}
	}

	function addTables() {
		$this->tables = $this->get('container')->getTables($this->name);
	}

	/*** for cron */
	function everyMin() { ; }
	function everyHour() { ; }
	function everyDay() { ; }

	public function get($name) {
		global $container;
		if ($name == 'container') {
			return $container;
		} else {
			return $container->get($name);
		}
	}
}
