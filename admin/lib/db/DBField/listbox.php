<?php
	
	inc_lib('db/DBField/FieldType.php');
    //inc_lib('db/DBField/LookUp.php');
    class listboxFieldType extends FieldType {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }
        
		public function getSQL() {
            return '';
        }
		
        public function listbox_getInput($value, $name) {
		    $where = '';
			if (!empty($this->props['query']))
				$where .= $where ? ' AND ('.$this->props['query'].')' : $this->props['query'];
			if (isset($this->props['dir'])) {
				$module = $GLOBALS['rtti']->getComponent(CUtils::_getVar('unit'));
				$where .= ($where ? ' AND ' : '').'module_id = '.$module['id'];
			}
			$items = $GLOBALS['rtti']->getItems($this->props['l_table'], $where, !empty($this->props['l_sort']) ? $this->props['l_sort'] : $this->props['l_field']);
			$ret = '<select name="'.$name.'" style="width:100%">';
			if (!isset($this->props['dir'])) {
				$ret.= '<option value="0">...</option>';
			}
			$fields = explode(',', $this->props['l_field']);
            foreach ($items as $a) {
				$vname = '';
				foreach ($fields as $fi)
					if (isset($a[$fi]))
						$vname .= ($vname ? ' ' : '').$a[$fi];
                $ret .= '<option '.(($value == $a['id'] || (!empty($this->props['defvalue']) && $this->props['defvalue'] == $a['id'])) ? 'selected ' : '').'value="'.$a['id'].'">'.$vname.' ['.$a['id'].']'.'</option>';
            }
            $ret .= '</select>';
            return $ret;
        }
		
        public function getStatic($value, $name) {
            $ret = '';
			$unit = CUtils::_getVar('unit'); 
			$table = CUtils::_getVar('table');
			$value = empty($value) ? '' : $value;
            $input_id = $this->dbId ? strtr($name, '[]', '__').$this->dbId : strtr($name, '[]', '__');
			$text = '';
            // узнаем им€ категории, дл€ текстового пол€
            if ($id && $items = $GLOBALS['rtti']->getItems($this->props['l_table'], $this->props['l_link']."=".$id)) {
				foreach ($items as $item) {
					$text .= ($text ? ', ' : '').$a[$this->props['l_field']];
				}
				return $ret.'<span id="'.$input_id.'">'.$text.'</span>&nbsp;<input class="butt" type="button" onClick="show_listbox(\''.$input_id.'\',\''.$unit.'\',\''.$table.'\',\''.$input_id.'\', \''.$this->dbId.'\');" value="ѕросмотр">';
			} else {
				return '-';
			}
        }
		
        public function getInput($value = '', $name = '') {
            return $this->getStatic($value, $name);
			//return $this->listbox_getInput($this->dbValue, ($name ? $name : $this->getName()));
        }
		
        public function getSearchInput() {
            return '';
        }
    }

?>