<?php

namespace Controller;

use Common\AbstractController;

class Controller extends AbstractController {
	
	public $name;
	public $tables;
	public $lang;
	public $params;
	
	function __construct($name) {
		$this->name = $name;
		$this->lang = $this->get('router')->getParam('lang');
		$this->addTables();
		$this->get('smarty')->assign('methodName', $this->get('router')->getParam('methodName'));
		$settings = $this->get('connection')->getItems('unit.settings', "SELECT * FROM config_settings WHERE komponent='$name'");
		$this->params = array();
		foreach ($settings as $setting) {
			$this->params[$setting['name']] = $setting['type'] == 'int' ? intval($setting['value']) : $setting['value'];
		}
	}

	function addTables() {
		$this->tables = $this->get('container')->getTables($this->name);
	}

	/*** template methods */
	function getTpl($name) {
		global $PRJ_DIR;
		if (file_exists($PRJ_DIR.'/app/Resources/views/'.$name.'.tpl')) {
			return $this->get('smarty')->fetch($name.'.tpl');
		}
	}

	/*** for cron */
	function everyMin() { ; }
	function everyHour() { ; }
	function everyDay() { ; }

	/*** search */
	function getSearchFieldsArray($tableName) {
		$ret = array();
		foreach ($this->tables[$tableName]->fields as $a)
			if (($a['type'] == 'string' || $a['type'] == 'text' || $a['type'] == 'html') && $a['name'] != 'lang')
				$ret[] = $a['name'];
		return $ret;
	}

	function getSearchResultRef(&$a, $methodName = '') {
		return $this->get('container')->href(!empty($a['dir_id']) ? $a['dir_id_name'] : $this->name, $methodName, array($a['id']));
	}

	function getTableSearchResults($words, $tableName, $where = '', $idName = '', $nName = 'name') {
		$ret = array();
		$fields = $this->getSearchFieldsArray($tableName);
		$fields_text = implode(',',$fields);
		$where = !empty($where) ? ' AND '.$where : '';
		$search_query = '';
		foreach ($fields as $field) {
			if (count($words) > 1) {
				$search_query0 = '';
				foreach ($words as $word) {
					$search_query0 .=  ($search_query0 ? ' AND ' : '').'('.$field." LIKE '%".$word."%')";
				}
				$search_query .=  ($search_query ? ' OR ' : '').($search_query0 ? '('.$search_query0.')' : '');
			} else {
				$search_query .=  ($search_query ? ' OR ' : '').$field." LIKE '%".$words[0]."%'";
			}
		}
		$sql = "SELECT id,".$fields_text." FROM ".$this->tables[$tableName]->getDBTableName()." WHERE (".$search_query.') '.$where.' ORDER BY id';

		$items = $this->get('connection')->getItems('get_search_items', $sql);
		foreach ($items as $a) {
			$ret[] = array (
				'ref' => $this->getSearchResultRef($a, $idName),
				'text' => $this->get('util')->cut_text(strip_tags($a[$nName], 150))
			);
		}
		return $ret;
	}

	function getSearchResults($text) { return array(); }

	function getMapList($id = 0) {
		$a = $this->get('connection')->getItems('get_cats', "SELECT id,name as title,name,p_id FROM catalog_categories WHERE p_id=".$id." ORDER BY ord");
		if (sizeof($a) > 0) {
			foreach ($a as $k => $i) {
				$a[$k]['ref'] = '/catalog/index.'.$i['id'].'.htm';
				$a[$k]['sub'] .= $this->getMapList($i['id']);
			}
		}
		$this->get('smarty')->assign('items', $a);
		$this->get('smarty')->assign('block', '_sub');
		return $this->get('smarty')->fetch('service/map.tpl');
	}

	function getMap() {
		if ($this->name == 'catalog') {
			return $this->getMapList();
		} else {
			return '';
		}
	} 

	function getPathArray() {
		$nodes = array();
		$params = $this->get('router')->getParam('params');
		if ($this->name == 'catalog' && $this->get('router')->getParam('methodName') == 'index') {
			if (isset($params[0])) {
				$path = $this->tables['categories']->getPrev($params[0]);
				foreach ($path as $k => $item) {
					$path[$k]['title'] = $item['name'];
					$path[$k]['ref'] = $this->get('container')->href($this->get('router')->getParam('node'), 'index', array($item['id']));
				}
				$nodes = $path;
			}
		} elseif ($this->name == 'catalog' && $this->get('router')->getParam('methodName') == 'stuff') {
			if (isset($params[0])) {
				$product = $this->get('container')->getItem('catalog_stuff', $params[0]);
				if (isset($product['c_id'])) {
					$path = $this->tables['categories']->getPrev($product['c_id']);
					foreach ($path as $k => $item) {
						$path[$k]['title'] = $item['name'];
						$path[$k]['ref'] = $this->get('container')->href($this->get('router')->getParam('node'), 'index', array($item['id']));
					}
					$nodes = $path;
				}
			}
		}
		return $nodes;
	}
	
	public function getContent() {
		
	}

}
