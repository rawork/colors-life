<?php

namespace Fuga\Component\DB\Field;

class SelectListType extends Type {

	private $_aEntities = null;

	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getSearchInput($name = '', $value = '', $class = '') {
		$value = $value ?: intval(parent::getSearchValue());
		$name = $name ?: parent::getSearchName();
		$table = $this->get('router')->getParam('module').'_'.$this->get('router')->getParam('table');
		$id = $this->dbId ?: '0';
		$inputId = strtr($name, '[]', '__');
		$class = $class ? 'class="'.$class.'"' : '';
		$content = '
<div class="input-append">
<input '.$class.' id="'.$inputId.'_title"  type="text" value="'.$this->getStatic($value).'" readonly>
<button class="btn" href="javascript:void(0)" type="button" onClick="showSelectPopup(\''.$inputId.'\',\''.$table.'\',\''.$name.'\', \''.$id.'\', \''.$this->getStatic($value).'\');">&hellip;</button>
</div>
<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$inputId.'">
';
		
		return $content;
	}

	public function getSearchSQL() {
		return $this->getSearchValue() ? ' FIND_IN_SET(\''.$this->getSearchValue().'\','.$this->getName().') ' : '';
	}

	public function getStatic($value = null) {
		$value = $value ?: $this->dbValue;
		$content = '';
		$fields = explode(',', $this->params['l_field']);
		$items = $this->get('connection')->getItems('select_list_static',
			'SELECT id,'.$this->params['l_field'].
			' FROM '.$this->params['l_table'].
			' WHERE id IN('.$value.')'.
			($this->params['l_sort'] ? ' ORDER BY '.$this->params['l_sort'] : '')
		);
		if ($items) {
			foreach ($items as $k => $item) {
				$content .= '';
				$content .= (!empty($content) && $k) ? ', ' : '';
				foreach ($fields as $fieldName) {
					if (array_key_exists($fieldName, $item)) {
						$content .= ' '.$item[$fieldName];
					}	
				}
				$content .= ' ('.$item['id'].')';
			}
			
			return $content;
		} else {
		
			return 'Не выбрано';
		}
	}

	public function getInput($value = '', $name = '', $class = 'input-xxlarge') {
		$name = $name ? $name : $this->getName();
		$value = $value ? $value : $this->dbValue;
		$input_id = strtr($name, '[]', '__');
		$table = $this->get('router')->getParam('module').'_'.$this->get('router')->getParam('table');
		$class = $class ? 'class="'.$class.'"' : '';
		$ret = '
<div class="input-append">
<input '.$class.' id="'.$input_id.'_title"  type="text" value="'.$this->getStatic($value).'" readonly>
<button class="btn" type="button" onClick="showListPopup(\''.$input_id.'\',\''.$table.'\',\''.$this->getName().'\', \''.$value.'\');">&hellip;</button>
</div>
<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$input_id.'">
';
		return $ret;
	}
}
