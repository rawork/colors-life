<?php

    inc_lib('db/Type/FieldType.php');
    class select_listFieldType extends FieldType {

		private $_aEntities = null;

        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }

		private function _getEntityList() {
			global $db;
			$this->_aEntities = null;
			if ( is_null($this->_aEntities) ) {
				$this->_aEntities = $db->getItems(
					'select_list_items',
					'SELECT id,'.$this->props['l_field'].' FROM '.$this->props['l_table'].($this->props['query'] ? ' WHERE '.$this->props['query'] : '')
				);
			}
			return $this->_aEntities;
		}
        
		public function getSearchInput() {
			$value = $this->getSearchValue();
			$items = $this->_getEntityList();
			$ret = '<select name="'.$this->getSearchName().'" style="width:100%">';
			$ret.= '<option value="0">...</option>';
			$fields = explode(',', $this->props['l_field']);
            foreach ($items as $a) {
				$vname = '';
				foreach ($fields as $fi)
					if (isset($a[$fi]))
						$vname .= ($vname ? ' ' : '').$a[$fi];
                $ret .= '<option '.($value == $a['id'] ? 'selected ' : '').'value="'.$a['id'].'">'.$vname.'</option>';
            }
            $ret .= '</select>';
			return $ret;
        }
		
		public function getSearchSQL() {
			return $this->getSearchValue() ? ' FIND_IN_SET(\''.$this->getSearchValue().'\','.$this->getName().') ' : '';
		}
        
        public function getStatic() {
            $ret = '';
			$fields = explode(',', $this->props['l_field']);
            $items = $GLOBALS['db']->getItems('select_list_static',
				'SELECT id,'.$this->props['l_field'].
				' FROM '.$this->props['l_table'].
				' WHERE id IN('.parent::getStatic().')'.
				($this->props['l_sort'] ? ' ORDER BY '.$this->props['l_sort'] : '')
			);
            foreach ($items as $k => $a) {
				$ret .= '';
				$ret .= (!empty($ret) && $k) ? ', ' : '';
				foreach ($fields as $fi)
					$ret .= !empty($a[$fi]) ? ' '.$a[$fi] : '';
				$ret .= ' ['.$a['id'].']';
			}
            return $ret;
        }
        
		public function getInput($value = '', $name = '') {
			$name = $name ? $name : $this->getName();
			$value = $value ? $value : $this->dbValue;
			$input_id = strtr($name, '[]', '__');
            $ret = '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td width="100%"><input type="text" readonly style="width:100%;" value="'.$this->getStatic().'" size="62" id="'.$input_id.'_title">';
            $ret .= '<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$input_id.'">';
            $ret .= '</td><td><input class="butt" type="button" value="&hellip;" onClick="show_list_popup(\''.$input_id.'\',\''.CUtils::_getVar('unit').'_'.CUtils::_getVar('table').'\',\''.$this->getName().'\', \''.CUtils::_getVar('id', true, 0).'\',\''.$value.'\');"></td></tr></table>';
            return $ret;
        }
    }
?>