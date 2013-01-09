<?php

namespace Fuga\Component\DB\Field;

class CheckboxType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getSQL() {
		return $this->getName()." TINYINT(1) NULL DEFAULT  '0'";
	}

	public function getSQLValue($name = '') {
		return $this->getValue($name) == '1' ? $this->getValue($name) : 0;
	}

	public function getStatic() {
		return $this->dbValue ? 'Да' : 'Нет';
	}

	public function getInput($value = '', $name = '', $class = '') {
		return '<input type="checkbox" value="1" name="'.($name ? $name : $this->getName()).'" '.(empty($this->dbValue) ? '' : 'checked').'>';
	}

	public function getSearchInput() {
		$name = parent::getSearchName();
		$value = parent::getSearchValue();
		$yes = $no = $no_matter = "";
		if ($value == 1) {
			$yes = 'checked';
		} else if ($value == 0) {
			$no = 'checked';
		} else {
			$no_matter = 'checked';
		}
		return '
<label class="radio inline">
  <input type="radio" name="'.$name.'" id="'.$name.'_yes" value="1" '.$yes.'>
  да
</label>
<label class="radio inline">
  <input type="radio" name="'.$name.'" id="'.$name.'_no" value="0" '.$no.'>
  нет
</label>
<label class="radio inline">
  <input type="radio" name="'.$name.'" id="'.$name.'_nomatter" value="" '.$no_matter.'>
  все равно
</label>';
	}

	public function getSearchSQL() {
		$value = parent::getSearchValue();
		if (!empty($value) && $value == 'off') {
			return $this->getName()."=''";
		} else {
			return parent::getSearchSQL();
		}
	}
}
