<?php

    inc_lib('db/DBField/LookUp.php');
    class numberFieldType extends LookUp {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }
		
		public function getSQLValue($name='') {
			return intval(preg_replace('/\s+/', '', preg_replace('/\,/', '.', $this->getValue($name))));
		}
    }

?>