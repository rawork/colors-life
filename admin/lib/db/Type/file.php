<?php

    inc_lib('db/Type/FieldType.php');
    class fileFieldType extends FieldType {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }
        
		protected function afterUpload($fileName) { ; }
		
        public function getSQLValue($name = '') {
            global $PRJ_DIR;
			$name = $name ? $name : $this->getName();
            $ret = $_REQUEST[$name.'_oldValue'];
            if ($ret && CUtils::_postVar($name.'_delete')) {
                @unlink($PRJ_DIR.$ret);
                $ret = '';
            }
            if (is_array($_FILES) && sizeof($_FILES) > 0 && isset($_FILES[$name]) && $_FILES[$name]['name'] != '') {
                if ($ret) {
                    @unlink($PRJ_DIR.$ret);
                }
                $dest = CUtils::getNextFileName('/upload/'.strtolower(CUtils::translitStr($_FILES[$name]['name'])));
                move_uploaded_file($_FILES[$name]['tmp_name'], $PRJ_DIR.$dest);
                $ret = $dest;
                $this->afterUpload($ret);
            }
            return $ret;
        }
		
        public function getStatic() {
        global $PRJ_REF;
			$ret = '';
            if ($this->dbValue)
                $ret = '<a href="'.$PRJ_REF.$this->dbValue.'">'.$this->dbValue.'</a>&nbsp;('.CUtils::getFileSize($this->dbValue).')';
            return $ret;
        }
        
        public function getInput($value = '', $name = '') {
            $name = $name ? $name : $this->getName();
			if ($s = $this->getStatic()) {
                $r = rand(0, getrandmax());
                $s = $s.'<nobr>&nbsp;<input name="'.$name.'_delete" type="checkbox" id="'.$r.'"><label for="'.$r.'" style="position:relative; top:-2px;">удалить</label></nobr><br>';
            }
            return '<input type="hidden" name="'.$name.'_oldValue" value="'.$this->dbValue.'">'.$s.'<input type="file" name="'.$name.'" size="49" style="width:100%">';
        }
		
        public function free() {
            @unlink($GLOBALS['PRJ_DIR'].$this->dbValue);
        }
    }

?>