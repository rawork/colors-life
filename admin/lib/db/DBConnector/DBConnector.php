<?php
    /*
        DBConnector by troyanic <rompomtoy@yandex.ru>
        WARNING! tested only with MySQL, MySQLi
    */
    abstract class DBConnector {
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
            $this->connection = null;
            $this->result = array();
            $this->base = $base;
			$this->host = $host;
			$this->user = $user;
			$this->pass = $pass;
			try {
				$this->connection = $this->openConnection();
			} catch (Exception $e) {
				CUtils::raiseError($e->getMessage(), ERROR_DIE); 
			}
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
	
?>
