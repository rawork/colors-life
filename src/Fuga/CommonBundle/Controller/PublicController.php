<?php

namespace Fuga\CommonBundle\Controller;

class PublicController extends Controller {
	
	public $name;
	public $tables;
	public $lang;
	public $params;
	
	function __construct($name) {
		$this->name = $name;
		$this->lang = $this->get('router')->getParam('lang');
		$this->addTables();
		$settings = $this->get('connection')->getItems('unit.settings', "SELECT * FROM config_settings WHERE module='$name'");
		$this->params = array();
		foreach ($settings as $setting) {
			$this->params[$setting['name']] = $setting['type'] == 'int' ? intval($setting['value']) : $setting['value'];
		}
	}
	
	public function getParam($name) {
		return $this->params[$name];
	}

	function addTables() {
		$this->tables = $this->get('container')->getTables($this->name);
	}

	function getMapList($id = 0) {
		$nodes = array();
		$items = $this->get('container')->getItems('catalog_category', "publish=1 AND parent_id=".$id);
		$block ='_sub';
		if (count($items) > 0) {
			foreach ($items as $node) {
				$node['ref'] = $this->get('container')->href('catalog', 'index', array($node['id']));
				$node['sub'] = $this->getMapList($node['id']);
				$nodes[] = $node;
			}
		}
		return $this->render('map.tpl', compact('nodes', 'block'));
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
		if ($this->name == 'catalog' && $this->get('router')->getParam('action') == 'index') {
			if (isset($params[0])) {
				$path = $this->tables['category']->getPrev($params[0]);
				foreach ($path as $k => $item) {
					$path[$k]['title'] = $item['title'];
					$path[$k]['ref'] = $this->get('container')->href($this->get('router')->getParam('node'), 'index', array($item['id']));
				}
				$nodes = $path;
			}
		} elseif ($this->name == 'catalog' && $this->get('router')->getParam('action') == 'stuff') {
			if (isset($params[0])) {
				$product = $this->get('container')->getItem('catalog_product', $params[0]);
				if (isset($product['category_id'])) {
					$path = $this->tables['category']->getPrev($product['category_id']);
					foreach ($path as $k => $item) {
						$path[$k]['title'] = $item['title'];
						$path[$k]['ref'] = $this->get('container')->href($this->get('router')->getParam('node'), 'index', array($item['id']));
					}
					$nodes = $path;
				}
			}
		} elseif ($this->name == 'catalog' && $this->get('router')->getParam('action') == 'brand') {
			if (isset($params[0])) {
				$producer = $this->get('container')->getItem('catalog_producer', $params[0]);
				if ($producer) {
					$nodes[] = array(
						'title' => 'Бренды',
						'ref'   => $this->get('container')->href($this->get('router')->getParam('node'), 'brands', array())
					);
					$nodes[] = array(
						'title' => $producer['name'],
						'ref'   => $this->get('container')->href($this->get('router')->getParam('node'), 'brand', array($producer['id']))
					);
				}
			}
		}
		return $nodes;
	}
	
}
