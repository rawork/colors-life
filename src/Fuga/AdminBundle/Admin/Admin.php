<?php

namespace Fuga\AdminBundle\Admin;

class Admin {
	
	public $name;
	public $params = array();

	function __construct($name) {
		$this->name = $name;
		$sql = "SELECT * FROM config_settings WHERE module= :name ";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->bindValue("name", $name);
		$stmt->execute();
		$params = $stmt->fetchAll();
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
