<?php

namespace DB\Type;

class SelectTreeFieldType extends LookUpFieldType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getStatic($value = null) {
		$value = $value ?: $this->dbValue;
		if ($value) {
			$a = $this->get('connection')->getItem('select_tree_item','SELECT id,'.$this->params['l_field'].' FROM '.$this->params['l_table'].' WHERE id='.intval($value));
			if (!empty($this->params['l_field']) && count($a)) {
				$ret = '';
				$fields = explode(',', $this->params['l_field']);
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
		$name = $name ?: $this->getName();
		$value = empty($value) ? intval($this->dbValue) : $value;
		$unit = $this->get('router')->getParam('module'); 
		$table = $this->get('router')->getParam('table');
		$id = empty($this->dbId) ? '-1' : $this->dbId;
		$input_id = strtr($name, '[]', '__');
		// узнаем имя категории, для текстового поля
		$ret = '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td width="100%"><input type="text" readonly style="width:100%;" value="'.$this->getStatic($value).'" size="62" id="'.$input_id.'_title">';
		$ret .= '<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$input_id.'"></td>';
		$ret .= '</td><td><input class="butt" type="button" value="&hellip;" onClick="showTreePopup(\''.$input_id.'\',\''.$unit.'_'.$table.'\',\''.$name.'\', \''.$id.'\', \''.$zeroTitle.'\',\''.$value.'\');"></td></tr></table>';
		return $ret;
	}
}
