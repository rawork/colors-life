<?php
    inc_lib('db/Type/FieldType.php');
    class enumFieldType extends FieldType {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }
        
        public function enum_getInput($value, $name) {
		global $db;
			$sel = '';
			if ($this->props['name'] == 'type' && $this->props['cls'] == 'table_attributes') {
				$sel = ' onChange="setFieldType(this)"';
			}
            $ret = '<select'.$sel.' name="'.$name.'" style="width:100%">';
			if (!isset($this->props['dir'])) {
				$ret.= '<option value="0">...</option>';
			}
			if (!empty($this->props['select_values'])) {
				$svalues = explode(';', $this->props['select_values']);
        	    foreach ($svalues as $a) {
					$aa = explode('|', $a);
					if (count($aa) == 2) {
						$ret .= '<option '.($value == $aa[1] ? 'selected ' : '').'value="'.$aa[1].'">'.$aa[0].'</option>';
					} else {
						$ret .= '<option '.($value == $a ? 'selected ' : '').'value="'.$a.'">'.$a.'</option>';
					}
    	            //(!empty($this->props['defvalue']) && $this->props['defvalue'] == $a['id'])) ? 'selected ' : '')
	            }
			}
            $ret .= '</select>';
            return $ret;
        }
		
		public function getStatic() {
			if (!empty($this->props['select_values'])) {
				$svalues = explode(';', $this->props['select_values']);
        	    foreach ($svalues as $a) {
					$aa = explode('|', $a);
					if (count($aa)>1 && $aa[1] == $this->dbValue) {
						return $aa[0];
					}
				}	
			}
			return $this->dbValue;
        }
      
        public function getInput($value = '', $name = '') {
            return $this->enum_getInput($this->dbValue, ($name ? $name : $this->getName()));
        }

        public function getSearchInput() {
            return $this->enum_getInput(parent::getSearchValue(), parent::getSearchName());
        }
    }

?>