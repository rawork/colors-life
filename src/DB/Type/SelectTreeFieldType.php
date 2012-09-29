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
				return $ret.' ('.$a['id'].')';
			} else {
				return 'Элемент #'.$a['id'];
			}
		}
		return 'Корень';
	}

	public function getInput($value = '', $name = '', $class = '') {
		return $this->select_tree_getInput($value, $name, $class);
	}

	public function getSearchInput() {
		return $this->select_tree_getInput(parent::getSearchValue(), parent::getSearchName());
	}

	protected function select_tree_getInput($value, $name, $class = '') {
		$name = $name ?: $this->getName();
		$value = empty($value) ? intval($this->dbValue) : $value;
		$table = $this->get('router')->getParam('module').'_'.$this->get('router')->getParam('table');
		$id = empty($this->dbId) ? '-1' : $this->dbId;
		$input_id = strtr($name, '[]', '__');
		$class = $class ? 'class="'.$class.'"' : '';
		$ret = '
<div class="input-append">
<input '.$class.' id="'.$input_id.'_title"  type="text" value="'.$this->getStatic($value).'" readonly>
<button class="btn" href="javascript:void(0)" type="button" onClick="showTreePopup(\''.$input_id.'\',\''.$table.'\',\''.$name.'\', \''.$id.'\', \''.$this->getStatic($value).'\',\''.$value.'\');">&hellip;</button>
</div>
<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$input_id.'">
';
		return $ret;
	}
}
