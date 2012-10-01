<?php

namespace Fuga\Component\DB\Field;

class ColorType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getInput($value = '', $name = '') {
		return $this->colorType_getInput($name ?: $this->getName(), $this->dbValue);
	}

	public function getSearchInput() {
		return $this->colorType_getInput(parent::getSearchName(), '');
	}

	public function getStatic() {
		$ret = strip_tags(trim($this->dbValue));
		return '<div class="clStatic" style="background-color:'.($ret ? $ret : '#ffffff').'"></div>';
	}

	public function colorType_getInput($name, $value = '') {
		return '<input class="clPicker" type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" size="7"></td>';
	}

}
