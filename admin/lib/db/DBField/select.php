<?php

    inc_lib('db/DBField/LookUp.php');
    class selectFieldType extends LookUp {
        
		private $_aEntities = null;

		public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }

		private function _getEntityList() {
			global $db;
			$this->_aEntities = null;
			if ( is_null($this->_aEntities) ) {
				$where = '';
				if (!empty($this->props['query']))
					$where .= $where ? ' AND ('.$this->props['query'].')' : $this->props['query'];
				if (isset($this->props['dir'])) {
					$module = $GLOBALS['rtti']->getComponent(CUtils::_getVar('unit'));
					$where .= ($where ? ' AND ' : '').' module_id = '.$module['id'];
				}
				$this->_aEntities = $GLOBALS['db']->getItems(
					'select_items',
					"SELECT id,".$this->props['l_field'].
					" FROM ".$this->props['l_table'].
					($where ? " WHERE ".$where : '').' ORDER BY '.$this->props['l_sort']
				);
			}
			return $this->_aEntities;
		}
		
        private function _getInputSelect($value, $name) {
		    $items = $this->_getEntityList();
			$ret = '<select name="'.$name.'" style="width:100%">';
			if (empty($this->props['dir'])) {
				$ret.= '<option value="0">...</option>';
			}
			$fields = explode(',', $this->props['l_field']);
            foreach ($items as $a) {
				$vname = '';
				foreach ($fields as $fi)
					if (!empty($a[$fi]))
						$vname .= ($vname ? ' ' : '').$a[$fi];
                $ret .= '<option '.(($value == $a['id'] || (!empty($this->props['defvalue']) && $this->props['defvalue'] == $a['id'])) ? 'selected ' : '').'value="'.$a['id'].'">'.$vname.' ['.$a['id'].']'.'</option>';
            }
            $ret .= '</select>';
            return $ret;
        }
		
        public function getStatic() {
            $ret = '';
            if ($a = $GLOBALS['db']->getItem('select_item', 'SELECT id,'.$this->props['l_field'].' FROM '.$this->props['l_table'].' WHERE id='.$this->dbValue)) {
				$fields = explode(',', $this->props['l_field']);
				foreach ($fields as $fi)
					if (isset($a[$fi]))
						$ret .= ($ret ? ' ' : '').$a[$fi];
				return $ret.' ['.$a['id'].']';
			} else {
				return 'Не выбрано';
			}
        }
        
		public function getInput($value = '', $name = '') {
            return $this->_getInputSelect($this->dbValue, ($name ? $name : $this->getName()));
        }
        
		public function getSearchInput() {
			return $this->_getInputSelect( parent::getSearchValue(), parent::getSearchName());
        }
    }

?>