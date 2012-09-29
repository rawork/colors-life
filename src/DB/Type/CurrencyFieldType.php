<?php

namespace DB\Type;

class CurrencyFieldType extends FieldType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getSQL() {
		return $this->getName().' decimal(14,2) NOT NULL default 0.00';
	}

	public function getSQLValue($name = '') {
		return floatval(preg_replace('/\s+/', '', preg_replace('/\,/', '.', $this->getValue($name))));
	}

	public function getInput($value = '', $name = '', $class = '') {
		$name = $name ? $name : $this->getName();
		$value = $value ? $value : $this->dbValue;
		$class = $class ? ' '.$class : '';
		return '<input type="text" class="right'.$class.'" name="'.$name.'" value="'.str_replace('"', '&quot;', $value).'">';
	}
}
