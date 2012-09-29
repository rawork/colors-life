<?php

namespace DB\Type;

class SelectFieldType extends LookUpFieldType {

	private $entities = null;

	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	private function getSelectInput($value, $name, $class = '') {
		$value = $value ?: intval($this->dbValue);
		$name = $name?: $this->getName();
		$table = $this->get('router')->getParam('module').'_'.$this->get('router')->getParam('table');
		$id = $this->dbId ?: '0';
		$input_id = strtr($name, '[]', '__');
		$class = $class ? 'class="'.$class.'"' : '';
		$content = '
<div class="input-append">
<input '.$class.' id="'.$input_id.'_title"  type="text" value="'.$this->getStatic($value).'" readonly>
<button class="btn" href="javascript:void(0)" type="button" onClick="showSelectPopup(\''.$input_id.'\',\''.$table.'\',\''.$name.'\', \''.$id.'\', \''.$this->getStatic($value).'\');">&hellip;</button>
</div>
<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$input_id.'">
';
		
		return $content;
	}

	public function getStatic($value = null) {
		$value = $value ?: intval($this->dbValue);
		$content = '';
		$item = $this->get('container')->getItem($this->params['l_table'], $value);
		if ($item) {
			$fields = explode(',', $this->params['l_field']);
			foreach ($fields as $fieldName)
				if (isset($item[$fieldName]))
					$content .= ($content ? ' ' : '').$item[$fieldName];
			return $content.' ('.$item['id'].')';
		} else {
			return 'Не выбрано';
		}
	}

	public function getInput($value = '', $name = '', $class = '') {
		return $this->getSelectInput($value, $name, $class);
	}

	public function getSearchInput() {
		return $this->getSelectInput( parent::getSearchValue(), parent::getSearchName());
	}
}
