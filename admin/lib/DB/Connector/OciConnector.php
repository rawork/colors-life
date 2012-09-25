<?php

namespace DB\Connector;

class OciConnector extends BaseConnector{

	protected $real_field_types = array('html' => 'text', 'checkbox' => 'char(2)', 'currency' => 'decimal(14,2)', 'select' => 'int(11)',
		'select_tree' => 'int(11)', 'select_list' => 'varchar(500)', 'date' => 'date', 'datetime' => 'datetime',
		'text' => 'text', 'password' => 'varchar(500)', 'enum' => 'varchar(500)', 'image' => 'varchar(500)',
		'string' => 'varchar(500)', 'file' => 'varchar(500)', 'number' => 'int(11)', 'template' => 'varchar(500)'
	);

	public function __construct($host, $user, $pass, $base) {
		parent::__construct($host, $user, $pass, $base);
	}

	public function openConnection() {
		$ret = 0;
		if (!($ret = OCILogon($this->user, $this->pass, $this->base))) {
			throw new \Exception("Error connect OCI"); 
		}
		return $ret;
	}

	public function closeConnection() {
		$this->freeResults();
		if ($this->connection != null) {
			OCILogoff($this->connection);
		}
	}

	public function freeResult($name) {
		OCIFreeStatement($this->result[$name]);
		unset($this->result[$name]);
	}

	public function execQuery($name, $query) {
		if ($GLOBALS['DB_DEBUG']) {
			echo $query.'<br>';
		}
		if ($this->connection) {
			$this->freeResult($name);
			$this->result[$name] = OCIParse($this->connection, $query);
			OCIExecute($this->result[$name]);
			return $this->result[$name];
		}
	}

	public function getNextArray($name) {
		OCIFetchInto($this->result[$name], $row, OCI_ASSOC);
		return $row;
	}

	public function getNumRows($name) {
		return OCIRowCount($this->result[$name]);
	}

	public function getInsertID() {
		// not implemented
		return 0;
	}

	public function getFieldsList($table) {
		$a = array();
		// not implemented
		return $a;
	}

	public function getTablesList() {
		$a = array();
		// not implemented
		return $a;
	}

	public function escapeStr($str) {
		//not implemented
		return $str;
	}

	public function backupDB($filename) {
		// not implemented
		return true;
	}

}
