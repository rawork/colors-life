<?php

    inc_lib('db/Type/FieldType.php');
    class textFieldType extends FieldType {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }
        
		public function getStatic() {
            return CUtils::cut_text(parent::getStatic());
        }
		
		public function getSearchInput() {
            return $this->getInput($this->getSearchValue(), $this->getSearchName(), true);
        }
		
        public function getInput($value = '', $name = '', $search = false) {
        global $PRJ_REF;
			$ret = '';
			if ($search){
				$ret = '<input type="text" id="'.$name.'" name="'.$name.'" style="width:100%" value="'.htmlspecialchars($value).'">';
			} else {
				$value = $value ? $value : $this->dbValue;
				$name = $name ? $name : $this->getName();
				$ret = '<textarea id="'.$name.'" name="'.$name.'" style="width:100%;height:150px" rows="6" cols="40">'.htmlspecialchars($value).'</textarea>';
			}
            return $ret;
        }
		
        public function getSQLValue() {
            return addslashes($this->getValue());

        }
    }

?>