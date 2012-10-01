<?php

namespace Fuga\Component\DB\Field;

class EnumType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function enum_getInput($value, $name, $class = '') {
		$value = $value ?: $this->dbValue;
		$class = $class ? 'class="'.$class.'"' : '';
		$sel = '';
		if ($this->params['name'] == 'type' && $this->params['cls'] == 'table_attributes') {
			$sel = ' onChange="setType(this)"';
		}
		$ret = '<select'.$sel.' name="'.$name.'" '.$class.'>';
		if (!isset($this->params['dir'])) {
			$ret.= '<option value="0">...</option>';
		}
		if (!empty($this->params['select_values'])) {
			$svalues = explode(';', $this->params['select_values']);
			foreach ($svalues as $a) {
				$aa = explode('|', $a);
				if (count($aa) == 2) {
					$ret .= '<option '.($value == $aa[1] ? 'selected ' : '').'value="'.$aa[1].'">'.$aa[0].'</option>';
				} else {
					$ret .= '<option '.($value == $a ? 'selected ' : '').'value="'.$a.'">'.$a.'</option>';
				}
			}
		}
		$ret .= '</select>';
		return $ret;
	}

	public function getStatic() {
		if (!empty($this->params['select_values'])) {
			$svalues = explode(';', $this->params['select_values']);
			foreach ($svalues as $a) {
				$aa = explode('|', $a);
				if (count($aa)>1 && $aa[1] == $this->dbValue) {
					return $aa[0];
				}
			}	
		}
		return $this->dbValue;
	}

	public function getInput($value = '', $name = '', $class = '') {
		return $this->enum_getInput($this->dbValue, ($name ? $name : $this->getName()), $class);
	}

	public function getSearchInput() {
		return $this->enum_getInput(parent::getSearchValue(), parent::getSearchName());
	}
}
