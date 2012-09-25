<?php
/*
	BaseConnector by troyanic <rompomtoy@yandex.ru>
	WARNING! tested only with MySQL, MySQLi
*/
namespace DB\Connector;

abstract class BaseConnector {
	protected $row;
	protected $connection;
	protected $result;
	protected $query;

	//parameters
	protected $host;
	protected $base;
	protected $user;
	protected $pass;

	protected $real_field_types = array();

	public function __construct($host, $user, $pass, $base) {
		$this->result = array();
		$this->base = $base;
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
	}
	
	public function getConnection() 
	{
		if (!$this->connection) {
			$this->connection = $this->openConnection();
		}
		
		return $this->connection;
	}

	abstract function openConnection();
	abstract function closeConnection();

	abstract function freeResult($name);
	abstract function execQuery($name, $query);
	abstract function getNextArray($name);
	abstract function getNumRows($name);
	abstract function getInsertID();
	abstract function getFieldsList($table);
	abstract function getTablesList();
	abstract function escapeStr($str);
	abstract function backupDB($filename);

	public function freeResults() {
		foreach ($this->result as $name => $v){
			$this->freeResult($name);
			unset($this->result[$name]);
		}
	}

	public function getNextArrays($name) {
		$ret = array();
		while ($a = $this->getNextArray($name)) {
			$ret[] = $a;
		}
		
		return $ret;
	}

	public function getItems($name, $query) {
		$this->execQuery($name, $query);
		$ret = $this->getNextArrays($name);
		$this->freeResult($name);
		
		return $ret;
	}

	public function getItem($name, $query) {
		$this->execQuery($name, $query);
		$ret = $this->getNextArray($name);
		$this->freeResult($name);
		
		return $ret;
	}

	public function getFieldRealType($fieldtype){
		if (!empty($this->real_field_types[$fieldtype])) {
			return $this->real_field_types[$fieldtype];
		}
		// надо правильней обработать
		return '';
	}
}
