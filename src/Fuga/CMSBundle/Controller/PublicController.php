<?php

namespace Fuga\CMSBundle\Controller;

class PublicController extends Controller {
	
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
		return $this->get('smarty')->fetch($name.'.tpl');
	}

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
