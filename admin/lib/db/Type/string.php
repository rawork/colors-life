<?php

    inc_lib('db/Type/FieldType.php');
    class stringFieldType extends FieldType {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
            $this->dbValue = str_replace("'", '`', $this->dbValue);
        }
    }

?>