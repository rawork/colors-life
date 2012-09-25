<?php

namespace Common;

class DBForm extends Form {

	private $_oDBTable;

	public function __construct($oDBTable, $sAction = '.', $aFormFields = array()) {
		parent::__construct($sAction, $aFormFields);
		$this->_oDBTable = $oDBTable;
		$this->addFieldsFromDBTable($this->_oDBTable);
	}

	public function fillValuesById($iId) {
		if ($aValues = $this->_oDBTable->getItem(intval($iId))) {
			$this->fillValues($aValues);
		}
	}

	public function addFieldsFromDBTable($oTable, $sFieldPropertyName = 'form') {
		foreach ($oTable->fields as $aFieldProperties) {
			if (isset($aFieldProperties[$sFieldPropertyName])) {
				$this->addItemFromDBTableField($aFieldProperties);
			}
		}
	}

	public function addItemFromDBTableField($aFieldProperties) {
		$aItem = array (
			'title'		=> $aFieldProperties['title'],
			'name'		=> $aFieldProperties['name'],
			'type'		=> $aFieldProperties['type'],
			'is_check'	=> !empty($aFieldProperties['is_check'])
		);

		$aItem['not_empty'] = !empty($aFieldProperties['not_empty']);

		if (stristr($aItem['type'], 'select')) {
			$aItem['type']			= 'select';
			$aItem['select_table']	= $aFieldProperties['l_table'];
			$aItem['select_name']	= $aFieldProperties['l_field'];
			if (isset($aFieldProperties['l_filter'])) {
				$aItem['select_filter']	= $aFieldProperties['l_filter'];
			}
		}

		if (stristr($aItem['type'], 'enum')) {
			$aItem['type']			= 'enum';
			$aItem['select_values']	= $aFieldProperties['select_values'];
		}

		$this->items[] = $aItem;
	}
}
