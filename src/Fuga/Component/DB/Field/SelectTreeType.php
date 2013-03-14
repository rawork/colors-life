<?php

namespace Fuga\Component\DB\Field;

class SelectTreeType extends LookUpType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
		if (empty($this->params['link_type'])) {
			$this->params['link_type'] = 'one';
		}
	}

	public function getStatic($value = null) {
		$value = $value ?: $this->dbValue;
		if ($value) {
			$sql = 'SELECT id,'.$this->params['l_field'].' FROM '.$this->params['l_table'].' WHERE id='.intval($value);
			$stmt = $this->get('connection1')->prepare($sql);
			$stmt->execute();
			$entity = $stmt->fetch();
			if (!empty($this->params['l_field']) && count($entity)) {
				$ret = '';
				$fields = explode(',', $this->params['l_field']);
				foreach ($fields as $field_name)
					if (!empty($entity[$field_name]))
						$ret .= ($ret ? ' ' : '').$entity[$field_name];
				return $ret.' ('.$entity['id'].')';
			} else {
				return 'Элемент #'.$entity['id'];
			}
		}
		return 'Не выбрано';
	}

	public function getInput($value = '', $name = '', $class = '') {
		return $this->select_tree_getInput($value, $name, $class);
	}

	public function getSearchInput() {
		return $this->select_tree_getInput(parent::getSearchValue(), parent::getSearchName());
	}

	protected function select_tree_getInput($value, $name, $class = '') {
		$name = $name ?: $this->getName();
		$value = empty($value) ? intval($this->dbValue) : $value;
		$table = $this->get('router')->getParam('module').'_'.$this->get('router')->getParam('table');
		$id = empty($this->dbId) ? '-1' : $this->dbId;
		$input_id = strtr($name, '[]', '__');
		$class = $class ? 'class="'.$class.'"' : '';
		$extra = array();
		$extraElements = array();
		if ($this->params['link_type'] == 'many' && $this->dbId) {
			$sql = 'SELECT 
				t1.id as id,t1.'.$this->params['l_field'].' as '.$this->params['l_field'].'
				FROM '.$this->params['link_table'].' t0 
				JOIN '.$this->params['l_table'].' t1 ON t0.'.$this->params['link_mapped'].'=t1.id
				WHERE t0.'.$this->params['link_inversed'].'='.$this->dbId;
			$stmt = $this->get('connection1')->prepare($sql);
			$stmt->execute();
			$entities = $stmt->fetchAll();
			foreach ($entities as $entity) {
				$extraElements[] = '<div>'.$this->getStatic($entity['id']).' <input type="radio" name="'.$input_id.'_default" value="'.$entity['id'].'" onclick="defaultSelect(this, \''.$input_id.'\')">  По умолчанию <a href="javascript:void(0)" onclick="deleteSelect(this, \''.$input_id.'\')"><i class="icon-remove"></i></a></div>';
				$extra[] = $entity['id'];
			}
		}
		$extra = implode(',', $extra);
		$extraElements = implode('', $extraElements);
		$staticValue = $this->getStatic($value);
		$defaultValue = $value ? '  <input type="radio" name="'.$input_id.'_default" value="'.$value.'" onclick="defaultSelect(this, \''.$input_id.'\')" checked> По умолчанию <a href="javascript:void(0)" onclick="deleteSelect(this, \''.$input_id.'\')"><i class="icon-remove"></i></a>' : '';
		$ret = '
<div id="'.$input_id.'_title">
<div>'.$staticValue.$defaultValue.'</div> 
'.$extraElements.'	
</div>
<button class="btn btn-success" href="javascript:void(0)" type="button" onClick="showTreePopup(\''.$input_id.'\',\''.$table.'\',\''.$name.'\', \''.$id.'\', \''.htmlspecialchars($this->getStatic($value)).'\',\''.$value.'\');">Выбрать</button>
<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$input_id.'">
<input type="hidden" name="'.$name.'_extra" value="'.$extra.'" id="'.$input_id.'_extra">	
<input type="hidden" name="'.$name.'_type" value="'.$this->params['link_type'].'" id="'.$input_id.'_type">
';
		return $ret;
	}
}
