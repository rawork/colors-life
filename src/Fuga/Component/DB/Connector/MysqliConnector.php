<?php

namespace Fuga\Component\DB\Connector;
	
class MysqliConnector extends AbstractConnector{
	protected $stmt;

	public $real_field_types = array (
		'html' => 'text', 'checkbox' => 'tinyint(1)', 'currency' => 'decimal(14,2)', 'select' => 'int(11)',
		'select_tree' => 'int(11)', 'select_list' => 'varchar(255)', 'date' => 'date', 'datetime' => 'timestamp',
		'text' => 'text', 'password' => 'varchar(255)', 'enum' => 'varchar(255)', 'image' => 'varchar(255)',
		'string' => 'varchar(255)', 'file' => 'varchar(255)', 'number' => 'int(11)', 'template' => 'varchar(255)'
	);

	public function __construct($host, $user, $pass, $base) 
	{
		parent::__construct($host, $user, $pass, $base);
	}
	
	public function openConnection() {
		try {
			$connection = new \mysqli($this->host, $this->user, $this->pass, $this->base);
		} catch (\Exception $e) {
			throw new \Exception('Ошибка соединения с базой данных. ('.mysqli_connect_errno().' - '.mysqli_connect_error().')'); 
		}
		$connection->query('SET NAMES utf8');
		$connection->query('set character set utf8');
		return $connection;
	}

	public function closeConnection() {
		$this->freeResults();
		if ($this->connection != null) {
			@mysqli_close($this->connection);
		}
	}

	public function freeResult($name) {
		if (!empty($this->result[$name]) && is_resource($this->result[$name])) {
			$this->result[$name]->free();
			unset($this->result[$name]);
		}
	}

	public function execQuery($name, $query) {
		if ($GLOBALS['DB_DEBUG']) {
			echo $query.'<br>';
		}
		if ($this->getConnection()) {
			$this->freeResult($name);
			/*$this->stmt[$name] = $this->connection->prepare($query);
			$this->stmt[$name]->execute();*/
			if (stristr($query, ';#|#|#')) {
				$query = str_replace('#|#|#', '', $query);
				$this->result[$name] = $this->getConnection()->multi_query($query);
			} else {
				$this->result[$name] = $this->getConnection()->query($query);
			}
			if (isset($this->result[$name]->errno)) {
				echo $this->result[$name]->errno." - ".$this->result[$name]->error;
			}
		}
		return $this->result[$name];
	}

	public function getNextArray($name) {
		return $this->getNumRows($name) ? $this->result[$name]->fetch_assoc() : null;	

	}

	public function getNumRows($name) {
		if (isset($this->result[$name]) && is_object($this->result[$name])) {
			return $this->result[$name]->num_rows;	
		}
		return 0;
	}

	public function lastInsertId() {
		return $this->connection->insert_id;	
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
		return $this->getConnection()->real_escape_string($str);
	}

}
