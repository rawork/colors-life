<?php

namespace DB\Type;

class EnumFieldType extends FieldType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function enum_getInput($value, $name) {
		$sel = '';
		if ($this->params['name'] == 'type' && $this->params['cls'] == 'table_attributes') {
			$sel = ' onChange="setFieldType(this)"';
		}
		$ret = '<select'.$sel.' name="'.$name.'" style="width:100%">';
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

	public function getInput($value = '', $name = '') {
		return $this->enum_getInput($this->dbValue, ($name ? $name : $this->getName()));
	}

	public function getSearchInput() {
		return $this->enum_getInput(parent::getSearchValue(), parent::getSearchName());
	}
}
