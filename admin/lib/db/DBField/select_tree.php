<?php

    inc_lib('db/DBField/LookUp.php');
    class select_treeFieldType extends LookUp {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }
        
		public function getStatic() {
			if (!empty($this->dbValue)) {
				$a = $GLOBALS['db']->getItem('select_tree_item','SELECT id,'.$this->props['l_field'].' FROM '.$this->props['l_table'].' WHERE id='.intval($this->dbValue));
				if (!empty($this->props['l_field']) && count($a)) {
					$ret = '';
					$fields = explode(',', $this->props['l_field']);
					foreach ($fields as $field_name)
						if (!empty($a[$field_name]))
							$ret .= ($ret ? ' ' : '').$a[$field_name];
					return $ret.' ['.$a['id'].']';
				} else {
					return 'Элемент ID:'.$a['id'];
				}
			}
            return 'Корень';
        }
		
        public function getInput($value = '', $name = '') {
            return $this->select_tree_getInput($value, $name);
        }
        
		public function getSearchInput() {
            return $this->select_tree_getInput(parent::getSearchValue(), parent::getSearchName());
        }
		
        protected function select_tree_getInput($value, $name, $zeroTitle = 'Корень') {
			$name = $name ? $name : $this->getName();
			$value = empty($value) ? intval($this->dbValue) : $value;
			$unit = CUtils::_getVar('unit'); 
			$table = CUtils::_getVar('table');
			$id = empty($this->dbId) ? '-1' : $this->dbId;
            $input_id = strtr($name, '[]', '__');
            // узнаем имя категории, для текстового поля
            $ret = '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td width="100%"><input type="text" readonly style="width:100%;" value="'.$this->getStatic().'" size="62" id="'.$input_id.'_title">';
            $ret .= '<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$input_id.'"></td>';
            $ret .= '</td><td><input class="butt" type="button" value="&hellip;" onClick="show_tree_popup(\''.$input_id.'\',\''.$unit.'_'.$table.'\',\''.$name.'\', \''.$id.'\', \''.$zeroTitle.'\',\''.$value.'\');"></td></tr></table>';
            return $ret;
        }
    }

?>