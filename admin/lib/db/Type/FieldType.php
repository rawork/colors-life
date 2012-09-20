<?php
    class FieldType {
        public $props;
        protected $dbValue = null;
        protected $dbId = 0;
        public function __construct(&$aProperties, $aEntity = null) {
            $this->setProperties($aProperties);
			$this->setDBEntity($aEntity);
        }

		public function setProperties($aProperties) {
			$this->props = $aProperties;
			if (!empty($this->props['l_field']) && empty($this->props['l_sort'])) {
				$this->props['l_sort'] = $this->props['l_field'];
			}
		}
		
		public function setDBEntity($aEntity = null) {
            if (is_array($aEntity)) {
                $this->dbId		= (int)$aEntity['id'];
                $this->dbValue	= isset($aEntity[$this->getName()]) ? $aEntity[$this->getName()] : '';
            } elseif (!empty($this->props['defvalue'])) {
				$this->dbValue	= $this->props['defvalue'];
			}
		}
		
		public function getName() {
            return $this->props['name'];
        }
        
		public function getGroupInput() {
            return $this->getInput('', $this->getName().$this->dbId);
        }
        
		public function getGroupSQLValue() {
            return $this->getSQLValue($this->getName().$this->dbId);
        }
        
		/*** these methods must be protected: */
        public function getSearchName($subName = '') {
            return 'search_filter_'.$this->getName().($subName ? '_'.$subName : '');
        }
		
        public function getValue($name = '') {
            $name = $name ? $name : $this->getName();
            $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
            return $value;
        }
		
        public function getSearchValue($subName = '') {
            return $this->getValue($this->getSearchName($subName));
        }
        
        /*** abstract class, these methods must be reimplemented: */
        public function getSQL() {
            return $this->getName().' varchar(500) NULL';
        }
		
        public function getSQLValue($name='') {
            return addslashes($this->getValue($name));
        }
		
        public function getStatic() {
			$ret = strip_tags(trim($this->dbValue));
            return $ret ? $ret : '&nbsp;';
        }
		
        public function getInput($value = '', $name = '') {
			$name = $name ? $name : $this->getName();
			$value = $value ? str_replace('"', '&quot;', $value) : str_replace('"', '&quot;', $this->dbValue);
            return '<input type="text" name="'.$name.'" style="width:100%" value="'.$value.'" size="60">';
        }
		
        public function getSearchInput() {
            return $this->getInput($this->getSearchValue(), $this->getSearchName());
        }
		
        public function getSearchSQL() {
            if ($value = $this->getSearchValue()) {
                return $this->getName()." LIKE '%".$value."%'";
            } else {
                return '';
            }
        }
		
        public function getSearchURL($name = '') {
            if ($value = $this->getSearchValue($name)) {
                return urlencode($this->getSearchName($name)).'='.urlencode($value);
            } else {
                return '';
            }
        }
        
		public function free(){}
  }
?>