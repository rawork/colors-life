<?php

namespace DB\Type;

class SelectFieldType extends LookUpFieldType {

	private $_aEntities = null;

	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	private function _getEntityList() {
		$this->_aEntities = null;
		if ( is_null($this->_aEntities) ) {
			$where = '';
			if (!empty($this->params['query']))
				$where .= $where ? ' AND ('.$this->params['query'].')' : $this->params['query'];
			if (isset($this->params['dir'])) {
				$module = $this->get('container')->getModule($this->get('router')->getParam('module'));
				$where .= ($where ? ' AND ' : '').' module_id = '.$module['id'];
			}
			$this->_aEntities = $this->get('connection')->getItems(
				'select_items',
				"SELECT id,".$this->params['l_field'].
				" FROM ".$this->params['l_table'].
				($where ? " WHERE ".$where : '').' ORDER BY '.$this->params['l_sort']
			);
		}
		return $this->_aEntities;
	}

	private function _getInputSelect($value, $name) {
		$items = $this->_getEntityList();
		$ret = '<select name="'.$name.'" style="width:100%">';
		if (empty($this->params['dir'])) {
			$ret.= '<option value="0">...</option>';
		}
		$fields = explode(',', $this->params['l_field']);
		foreach ($items as $a) {
			$vname = '';
			foreach ($fields as $fi)
				if (!empty($a[$fi]))
					$vname .= ($vname ? ' ' : '').$a[$fi];
			$ret .= '<option '.(($value == $a['id'] || (!empty($this->params['defvalue']) && $this->params['defvalue'] == $a['id'])) ? 'selected ' : '').'value="'.$a['id'].'">'.$vname.' ['.$a['id'].']'.'</option>';
		}
		$ret .= '</select>';
		return $ret;
	}

	public function getStatic() {
		$ret = '';
		if ($a = $this->get('connection')->getItem('select_item', 'SELECT id,'.$this->params['l_field'].' FROM '.$this->params['l_table'].' WHERE id='.$this->dbValue)) {
			$fields = explode(',', $this->params['l_field']);
			foreach ($fields as $fi)
				if (isset($a[$fi]))
					$ret .= ($ret ? ' ' : '').$a[$fi];
			return $ret.' ['.$a['id'].']';
		} else {
			return 'Не выбрано';
		}
	}

	public function getInput($value = '', $name = '') {
		return $this->_getInputSelect($this->dbValue, ($name ? $name : $this->getName()));
	}

	public function getSearchInput() {
		return $this->_getInputSelect( parent::getSearchValue(), parent::getSearchName());
	}
}
