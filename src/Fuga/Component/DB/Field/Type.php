<?php

namespace Fuga\Component\DB\Field;

class Type {
	public $params;
	protected $dbValue = null;
	protected $dbId = 0;
	public function __construct(&$params, $entity = null) {
		$this->setParams($params);
		$this->setEntity($entity);
	}

	public function setParams($params) {
		$this->params = $params;
		if (!empty($this->params['l_field']) && empty($this->params['l_sort'])) {
			$this->params['l_sort'] = $this->params['l_field'];
		}
	}

	public function setEntity($aEntity = null) {
		if (is_array($aEntity)) {
			$this->dbId		= (int)$aEntity['id'];
			$this->dbValue	= isset($aEntity[$this->getName()]) ? $aEntity[$this->getName()] : '';
		} elseif (!empty($this->params['defvalue'])) {
			$this->dbValue	= $this->params['defvalue'];
		}
	}

	public function getName() {
		return $this->params['name'];
	}

	public function getGroupInput() {
		return $this->getInput('', $this->getName().$this->dbId, 'input-mini');
	}

	public function getGroupSQLValue() {
		return $this->getSQLValue($this->getName().$this->dbId);
	}

	/*** these methods must be protected: */
	public function getSearchName($subName = '') {
		return 'search_filter_'.$this->getName().($subName ? '_'.$subName : '');
	}

	public function getValue($name = '') {
		$name = $name ? $name : $this->getName();
		$value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
		return $value;
	}

	public function getSearchValue($subName = '') {
		return $this->getValue($this->getSearchName($subName));
	}

	/*** abstract class, these methods must be reimplemented: */
	public function getSQL() {
		return $this->getName().' varchar(500) NULL';
	}

	public function getSQLValue($name='') {
		return addslashes($this->getValue($name));
	}

	public function getStatic() {
		$ret = strip_tags(trim($this->dbValue));
		return $ret ? $ret : '&nbsp;';
	}

	public function getInput($value = '', $name = '', $class = '') {
		$name = $name ? $name : $this->getName();
		$value = $value ? str_replace('"', '&quot;', $value) : str_replace('"', '&quot;', $this->dbValue);
		$class = $class ? 'class="'.$class.'"' : '';
		return '<input type="text" '.$class.' name="'.$name.'" value="'.$value.'" >';
	}

	public function getSearchInput() {
		return $this->getInput($this->getSearchValue(), $this->getSearchName());
	}

	public function getSearchSQL() {
		if ($value = $this->getSearchValue()) {
			return $this->getName()." LIKE '%".$value."%'";
		} else {
			return '';
		}
	}

	public function getSearchURL($name = '') {
		if ($value = $this->getSearchValue($name)) {
			return urlencode($this->getSearchName($name)).'='.urlencode($value);
		} else {
			return '';
		}
	}

	public function free(){}

	public function get($name) {
		global $container, $security;
		if ($name == 'container') {
			return $container;
		} elseif ($name == 'security') {
			return $security;
		} else {
			return $container->get($name);
		}
	}
}
