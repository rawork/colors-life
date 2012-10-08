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
		$settings = $this->get('connection')->getItems('unit.settings', "SELECT * FROM config_settings WHERE komponent='$name'");
		$this->params = array();
		foreach ($settings as $setting) {
			$this->params[$setting['name']] = $setting['type'] == 'int' ? intval($setting['value']) : $setting['value'];
		}
	}

	function addTables() {
		$this->tables = $this->get('container')->getTables($this->name);
	}

	function getMapList($id = 0) {
		$nodes = $this->get('connection')->getItems('get_cats', "SELECT id,name as title,name,p_id FROM catalog_categories WHERE p_id=".$id." ORDER BY ord");
		$block ='_sub';
		if (count($nodes)) {
			foreach ($nodes as &$node) {
				$node['ref'] = '/catalog/index.'.$node['id'].'.htm';
				$node['sub'] .= $this->getMapList($node['id']);
			}
			unset($node);
		}
		return $this->render('service/map.tpl', compact('nodes', 'block'));
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
