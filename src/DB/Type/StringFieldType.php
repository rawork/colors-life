<?php

namespace DB\Type;

class stringFieldType extends FieldType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
		$this->dbValue = str_replace("'", '`', $this->dbValue);
	}
}
