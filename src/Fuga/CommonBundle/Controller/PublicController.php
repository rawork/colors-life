<?php

namespace Fuga\CommonBundle\Controller;

class PublicController extends Controller {
	
	public $params = array();
	
	function __construct($name) {
		$sql = "SELECT * FROM config_settings WHERE module= :name ";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->bindValue("name", $name);
		$stmt->execute();
		$params = $stmt->fetchAll();
		foreach ($params as $param) {
			$this->params[$param['name']] = $param['type'] == 'int' ? intval($param['value']) : $param['value'];
		}
	}
	
	public function getParam($name) {
		return $this->params[$name];
	}

}
