<?php
    inc_lib('db/DBField/FieldType.php');
    class passwordFieldType extends FieldType {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }
		
		public function getInput($value = '', $name = '') {
            $value = !$value ? $this->dbValue : $value;
            $name = !$name ? $this->getName() : $name;
            return '<input type="password" id="'.$name.'" name="'.$name.'" style="width:100%" value="'.htmlspecialchars($value).'">';
        }
        
        public function getSQLValue($name = '') {
            $text = $this->getValue($name);
			if (!empty($text) && strlen($text) != '32'){
				$text = md5($text);
 			}
            return $text;
        }
    }
?>