<?php

namespace Fuga\Component\DB\Connector;

class PgsqlConnector extends AbstractConnector{

	protected $real_field_types = array('html' => 'text', 'checkbox' => 'char(2)', 'currency' => 'decimal(14,2)', 'select' => 'int(11)',
		'select_tree' => 'int(11)', 'select_list' => 'varchar(255)', 'date' => 'date', 'datetime' => 'datetime',
		'text' => 'text', 'password' => 'varchar(255)', 'enum' => 'varchar(255)', 'image' => 'varchar(255)',
		'string' => 'varchar(255)', 'file' => 'varchar(255)', 'number' => 'int(11)', 'template' => 'varchar(255)'
	);

	public function __construct($host, $user, $pass, $base) {
		parent::__construct($host, $user, $pass, $base);
	}

	public function openConnection() {
		$ret = 0;
		if (!($ret = pg_connect('host='.$this->host.' user='.$this->user.' dbname='.$this->base.' password='.$this->pass.' options=\'-d 1\''))) {
			throw new \Exception("Error connect pgsql"); 
		}
		return $ret;
	}

	public function closeConnection() {
		$this->freeResults();
		if ($this->connection != null) {
			pg_Close($this->connection);
		}
	}

	public function freeResult($name) {
		if (isset($this->result[$name])) {
			pg_FreeResult($this->result[$name]);
			unset($this->result[$name]);
		}
	}

	public function freeResults() {
		foreach ($this->result as $name => $v){
			$this->freeResult($name);
			unset($this->result[$name]);
		}
	}

	public function execQuery($name, $query) {
		if ($GLOBALS['DB_DEBUG']) {
			echo $query.'<br>';
		}
		if ($this->connection) {
			$this->freeResult($name);
			$this->result[$name] = pg_Exec($this->connection, $query);
		}
		$this->row = 0;
		return $this->result[$name];
	}

	public function getNextArray($name) {
		if (isset($this->result[$name]) && (gettype($this->result[$name]) == 'object' || gettype($this->result[$name]) == 'resource')) {
			return pg_Fetch_Array($this->result[$name], $this->row++);
		}
		return 0;
	}

	public function getNumRows($name) {
		if (isset($this->result[$name]) && (gettype($this->result[$name]) == 'object' || gettype($this->result[$name]) == 'resource')) {
			return pg_NumRows($this->result[$name]);
		}
		return 0;
	}

	public function lastInsertId() {
		return pg_last_oid($this->result[$name]);
	}

	public function getFieldsList($table) {
		$a = array();
		// not implemented
		return $a;
	}

	function escapeStr($str) {
		throw new \Exception('PGSQL escapeStr not implemented');
	}


}
