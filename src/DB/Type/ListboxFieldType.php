<?php
	
namespace DB\Type;

class ListboxFieldType extends FieldType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getSQL() {
		return '';
	}

	public function listbox_getInput($value, $name) {
		$where = '';
		if (!empty($this->params['query']))
			$where .= $where ? ' AND ('.$this->params['query'].')' : $this->params['query'];
		if (isset($this->params['dir'])) {
			$module = $this->get('container')->getModule($this->get('router')->getParam('module'));
			$where .= ($where ? ' AND ' : '').'module_id = '.$module['id'];
		}
		$items = $this->get('container')->getItems($this->params['l_table'], $where, !empty($this->params['l_sort']) ? $this->params['l_sort'] : $this->params['l_field']);
		$ret = '<select name="'.$name.'" style="width:100%">';
		if (!isset($this->params['dir'])) {
			$ret.= '<option value="0">...</option>';
		}
		$fields = explode(',', $this->params['l_field']);
		foreach ($items as $a) {
			$vname = '';
			foreach ($fields as $fi)
				if (isset($a[$fi]))
					$vname .= ($vname ? ' ' : '').$a[$fi];
			$ret .= '<option '.(($value == $a['id'] || (!empty($this->params['defvalue']) && $this->params['defvalue'] == $a['id'])) ? 'selected ' : '').'value="'.$a['id'].'">'.$vname.' ['.$a['id'].']'.'</option>';
		}
		$ret .= '</select>';
		return $ret;
	}

	public function getStatic($value, $name) {
		$ret = '';
		$unit = $this->get('router')->getParam('unit'); 
		$table = $this->get('router')->getParam('table');
		$value = empty($value) ? '' : $value;
		$input_id = $this->dbId ? strtr($name, '[]', '__').$this->dbId : strtr($name, '[]', '__');
		$text = '';
		// узнаем имя категории, для текстового поля
		if ($id && $items = $this->get('container')->getItems($this->params['l_table'], $this->params['l_link']."=".$id)) {
			foreach ($items as $item) {
				$text .= ($text ? ', ' : '').$a[$this->params['l_field']];
			}
			return $ret.'<span id="'.$input_id.'">'.$text.'</span>&nbsp;<input class="butt" type="button" onClick="show_listbox(\''.$input_id.'\',\''.$unit.'\',\''.$table.'\',\''.$input_id.'\', \''.$this->dbId.'\');" value="Просмотр">';
		} else {
			return '-';
		}
	}

	public function getInput($value = '', $name = '') {
		return $this->getStatic($value, $name);
		//return $this->listbox_getInput($this->dbValue, ($name ? $name : $this->getName()));
	}

	public function getSearchInput() {
		return '';
	}
}
