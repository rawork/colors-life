<?php

namespace DB\Connector;
	
class MysqliConnector extends AbstractConnector{
	protected $stmt;

	public $real_field_types = array (
		'html' => 'text', 'checkbox' => 'char(2)', 'currency' => 'decimal(14,2)', 'select' => 'int(11)',
		'select_tree' => 'int(11)', 'select_list' => 'varchar(500)', 'date' => 'date', 'datetime' => 'datetime',
		'text' => 'text', 'password' => 'varchar(500)', 'enum' => 'varchar(500)', 'image' => 'varchar(500)',
		'string' => 'varchar(500)', 'file' => 'varchar(500)', 'number' => 'int(11)', 'template' => 'varchar(500)'
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
		return $this->getNumRows($name) ? $this->result[$name]->fetch_assoc() : 0;	

	}

	public function getNumRows($name) {
		if (isset($this->result[$name]) && is_object($this->result[$name])) {
			return $this->result[$name]->num_rows;	
		}
		return 0;
	}

	public function getInsertID() {
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

	public function getTablesList() {
		$a = array();
		$this->execQuery('list_tables', 'SHOW TABLES FROM '.$this->base);
		while ($row = $this->result['list_tables']->fetch_row()) {
			$a[] = $row;
		}
		return $a;
	}

	public function escapeStr($str) {
		return $this->getConnection()->real_escape_string($str);
	}

	public function backupDB($filename) {
		$f = fopen($filename, "a");
		$tablelist = $this->getTablesList();
		foreach ($tablelist as $tableitem) {
			$fields = $this->getFieldsList($tableitem[0]);
			$fields_text = '';
			$fields_insert = '';
			$primary_text = '';
			foreach ($fields as $field) {
				$fields_insert .= ($fields_insert ? ',' : '').$field['Field'];
				$fields_text .= $fields_text ? ',' : '';
				$fields_text .= "`".$field['Field']."` ".$field['Type'];
				$fields_text .= $field['Null'] != 'YES' ? ' NOT NULL' : '';
				if ($field['Extra'] == '' && $field['Type'] != 'text') {
					switch ($field['Type']) {
						case 'timestamp':
							$def_value = ' '.$field['Default'];
							//$objResponse->alert($field['Field'].'_'.$field['Default']."_".$field['Null']);
							break;
						case 'int(11)':
							$def_value = ' '.$field['Default'];break;
						case 'varchar(255)':
							$def_value = " '".$field['Default']."'";break;
						case 'varchar(100)':
							$def_value = " '".$field['Default']."'";break;
						default: 
							$def_value = '';
					}
					if ($field['Null'] == 'YES' && $field['Type'] != 'timestamp') {
						$def_value = ' NULL';
					}
					$fields_text .= $def_value ? " default".$def_value : '';
				}
				$fields_text .= ' '.$field['Extra'];
				if ($field['Key'] == 'PRI') {
					$primary_text = 'PRIMARY KEY (`'.$field['Field'].'`)';
				}
			}
			$fields_text .= $primary_text ? ', '.$primary_text : '';
			fwrite($f, 'DROP TABLE IF EXISTS `'.$tableitem[0].'`;'."\n");
			fwrite($f, 'CREATE TABLE `'.$tableitem[0].'` ('.$fields_text.') ENGINE=InnoDB DEFAULT CHARSET=utf8;'."\n\n");
			$records = $this->getItems('curr_table', 'SELECT * FROM '.$tableitem[0]);
			foreach ($records as $rec) {
				$values = '';
				foreach ($rec as $field_value) {
					$values .= ($values ? ",'" : "'").$this->escapeStr($field_value)."'";
				}
				fwrite($f, 'INSERT INTO '.$tableitem[0].'('.$fields_insert.') VALUES('.$values.');'."\n");
			}
			fwrite($f, "\n");
		}
		fclose($f);
		return true;
	}

}
