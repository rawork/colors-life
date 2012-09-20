<?php
    inc_lib('db/Type/FieldType.php');
    class colorFieldType extends FieldType {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }
		
        public function getInput($value = '', $name = '') {
            return $this->colorFieldType_getInput(($name ? $name : $this->getName()), $this->dbValue);
        }
        
		public function getSearchInput() {
            $value = '';
            return $this->colorFieldType_getInput(parent::getSearchName(), $value, false);
        }
		
		public function getStatic() {
			$ret = strip_tags(trim($this->dbValue));
            return '<div class="clStatic" style="background-color:'.($ret ? $ret : '#ffffff').'"></div>';
        }
        
		public function colorFieldType_getInput($name, $value = '', $insertValue = true) {
		global $THEME_REF;
			return '<input class="clPicker" type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" size="7"></td>';
        }
        
    }

?>