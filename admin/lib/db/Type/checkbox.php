<?php

    inc_lib('db/Type/FieldType.php');
    class checkboxFieldType extends FieldType {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }
        
        public function getSQL() {
            return $this->getName().' char(2) NULL';
        }
        
        public function getSQLValue($name = '') {
            return $this->getValue($name) == 'on' ? $this->getValue($name) : '';
        }
        
        public function getStatic() {
            return $this->dbValue ? 'Да' : 'Нет';
        }
        
        public function getInput($value = '', $name = '') {
            return '<input type="checkbox" name="'.($name ? $name : $this->getName()).'" '.(empty($this->dbValue) ? '' : 'checked').'>';
        }
        
        public function getSearchInput() {
            $name = parent::getSearchName();
            $value = parent::getSearchValue();
            $yes = $no = $no_matter = "";
            if ($value == 'on') {
                $yes = 'checked';
            } else if ($value == 'off') {
                $no = 'checked';
            } else {
                $no_matter = 'checked';
            }
            return '<table><tr><td><input type="radio" style="height:13px;" name="'.$name.'" id="'.$name.'_yes" value="on" '.$yes.'><label style="position:relative; top:-2px;" for="'.$name.'_yes">да</label><input type="radio" style="height:13px;" name="'.$name.'" id="'.$name.'_no" value="off" '.$no.'><label style="position:relative; top:-2px;" for="'.$name.'_no">нет</label><input type="radio" style="height:13px;" name="'.$name.'" id="'.$name.'_nomatter" value="" '.$no_matter.'><label style="position:relative; top:-2px;" for="'.$name.'_nomatter">все равно</label></td></tr></table>';
        }
        
        public function getSearchSQL() {
            $value = parent::getSearchValue();
            if (!empty($value) && $value == 'off') {
                return $this->getName()."=''";
            } else {
                return parent::getSearchSQL();
            }
        }
    }

?>