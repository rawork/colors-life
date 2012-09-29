<?php

namespace DB\Type;

class PasswordFieldType extends FieldType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getInput($value = '', $name = '') {
		$value = !$value ? $this->dbValue : $value;
		$name = !$name ? $this->getName() : $name;
		return '<input type="password" id="'.$name.'" name="'.$name.'" style="width:100%" value="">';
	}

	public function getSQLValue($name = '') {
		$text = $this->getValue($name);
		if (!empty($text) && strlen($text) != '32'){
			$text = md5($text);
		}
		return $text;
	}
}
