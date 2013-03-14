<?php

namespace Fuga\Component\DB\Connector;

class MysqlConnector extends AbstractConnector{

	public $real_field_types = array(
		'html' => 'text', 'checkbox' => 'tinyint(1)', 'currency' => 'decimal(14,2)', 'select' => 'int(11)',
		'select_tree' => 'int(11)', 'select_list' => 'varchar(255)', 'date' => 'date', 'datetime' => 'timestamp',
		'text' => 'text', 'password' => 'varchar(255)', 'enum' => 'varchar(255)', 'image' => 'varchar(255)',
		'string' => 'varchar(255)', 'file' => 'varchar(255)', 'number' => 'int(11)', 'template' => 'varchar(255)'
	);

	public function __construct($host, $user, $pass, $base) {
		parent::__construct($host, $user, $pass, $base);
	}

	public function openConnection() {
		if (!($ret = @mysql_connect($this->host, $this->user, $this->pass))) {
			throw new \Exception('Ошибка соединения. ('.mysql_errno().' - '.mysql_error().')');
		} else {
			if (!mysql_select_db($this->base, $ret))
				throw new \Exception('БД отсутствует');
		}
		mysql_query('SET NAMES utf8', $this->connection);
		mysql_query('set character set utf8', $this->connection);
		return $ret;
	}

	public function closeConnection() {
		$this->freeResults();
		if ($this->connection != null) {
			mysql_close($this->connection);
		}
	}

	public function freeResult($name) {
		if (!empty($this->result[$name]) && is_resource($this->result[$name])) {
			mysql_free_result($this->result[$name]);
			unset($this->result[$name]);
		}
	}

	public function execQuery($name, $query) {
		if ($GLOBALS['DB_DEBUG']) {
			echo $query.'<br>';
		}
		if ($this->connection) {
			$this->freeResult($name);
			$queries = explode(';#|#|#', $query);
			foreach ($queries as $q) {
				$this->result[$name] = mysql_query($q, $this->connection);
			}
		}
		return $this->result[$name];
	}

	public function getNextArray($name) {
		if (isset($this->result[$name]) && (gettype($this->result[$name]) == 'object' || gettype($this->result[$name]) == 'resource')) {
			return $this->getNumRows($name) ? mysql_fetch_assoc($this->result[$name]) : null;
		}
	}

	public function getNumRows($name) {
		return mysql_num_rows($this->result[$name]);
	}

	public function lastInsertId() {
		return mysql_insert_id($this->connection);
	}

	public function getFieldsList($table) {
		$a = array();
		$this->execQuery('table_struct'.$table, 'SHOW COLUMNS FROM '.$table);
		$fields = $this->getNextArrays('table_struct'.$table);
		foreach($fields as $f) {
			$a[$f['Field']] = $f;
		}
		return $a;
	}

	public function escapeStr($str) {
		return mysql_real_escape_string($str);
	}

}
