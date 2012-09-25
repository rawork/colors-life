<?php

namespace Controller;

class TreeController extends Controller {
	
	private $controller;
	private $isService;

	function __construct() {
		global $LIB_DIR;
		parent::__construct('tree');
		$controller = $this->get('router')->getParam('controller');
		if ($controller != 'tree') {
			if (file_exists($LIB_DIR.'/Controller/'.ucfirst($controller).'Controller.php')) {
				$className = '\\Controller\\'.ucfirst($this->get('router')->getParam('controller')).'Controller';
				$this->controller = new $className();
				$this->isService = true;
			} else {
				$this->controller = new \Controller\Controller($controller);
				$this->isService = false;
			}	
		}
	}

	public function getNodes($uri = 0, $where = "publish='on'", $limit = false) {
		$ret = $this->get('container')->getNativeItems(
			'SELECT t1.*, t3.name as module_id_name FROM tree_tree as t1 '.
			'LEFT JOIN tree_tree as t2 ON t1.p_id=t2.id '.
			'LEFT JOIN config_modules as t3 ON t1.module_id=t3.id '.
			"WHERE t1.publish='on' AND t1.lang='".$this->get('router')->getParam('lang')."' AND ".(is_numeric($uri) ? ($uri == 0 ? ' t1.p_id=0 ' : 't2.id='.$uri.' ') : "t2.name='".$uri."' ").
			'ORDER BY t1.ord,t1.name '.
			'LIMIT '.($limit ? $limit : '0,1000')
		);
		foreach ($ret as $k => $v) {
			if ($ret[$k]['h1_img']) {
				if (is_array($i = @GetImageSize($GLOBALS['PRJ_DIR'].$ret[$k]['h1_img']))) {
					$ret[$k]['h1_img_width'] = $i[0];
					$ret[$k]['h1_img_height'] = $i[1];
				}
			}
			$ret[$k]['ref'] = $this->getUrl($v);
		}
		return $ret;	
	}

	function getUrl($a) {
		global $PRJ_REF;
		if (trim($a['url']) != '') {
			return stristr($a['url'], 'http://') ? $a['url'] : $PRJ_REF.$a['url'];
		} else {	
			$lang = $this->get('router')->getParam('lang') == 'ru' ? '' : $this->get('router')->getParam('lang').'/';
			$alias = $a['name'] == '/' ? $PRJ_REF.$a['name'].$lang : $PRJ_REF.'/'.$lang.$a['name'].(!empty($a['module_id']) ? '/' : '.htm');
			return $alias;
		}
	}

	function getPathArray() {
		$nodes = array();
		if ($this->get('router')->hasParam('nodeId'))
			$nodes = $this->tables['tree']->getPrev($this->get('router')->getParam('nodeId'));
		if ($this->controller instanceof Controller) {
			$nodes = array_merge($nodes, $this->controller->getPathArray());
		}
		return $nodes;
	}

	function getPath($delimeter = '&gt;') {
		global $PATH_MAINPAGE_TITLE;
		if (sizeof($path = $this->getPathArray()) > 0) {
			if (__PATH_MAINPAGE_VISIBLE && $path[0]['name'] != '/')
				$path = array_merge(array(array('name' => '/', 'title' => $PATH_MAINPAGE_TITLE[$this->lang])), $path);
			if (!__PATH_MAINPAGE_VISIBLE && $path[0]['name'] == '/') {
				$path[0] = array();
			}
			foreach ($path as $k => $v) {
				if (isset($v['name']) && empty($v['ref'])) 
					$path[$k]['ref'] = $this->getUrl($v);
				if (isset($v['name']) && $v['name'] == '/')
					$path[$k]['title'] = $PATH_MAINPAGE_TITLE[$this->lang];
			}
			$this->get('smarty')->assign('pathitems', $path);
			$this->get('smarty')->assign('methodName', $this->get('router')->getParam('methodName'));
			$this->get('smarty')->assign('delimeter', $delimeter);
			return $this->get('smarty')->fetch('service/breadscrumb.tpl');
		}
	}

	function getTitle() {
		$ret = '';
		$node = $this->get('router')->getParam('node');
		if ($node)
			$ret = $node['title'];
		if ($this->controller) {
			$ret = $this->controller->getTitle();
		}
		return $ret;
	}

	function getModuleBody() {
		$content = '';
		if ($this->get('router')->getParam('controller') != 'tree') {
			if ($this->controller && $this->isService) {
				$content = $this->controller->getBody();
			} elseif ($this->controller) {
				$content = $this->get('container')->callMethod(
						$this->get('router')->getParam('controller'), 
						$this->get('router')->getParam('methodName'), 
						$this->get('router')->getParam('params')
				);
			}

		}
		return $content;
	}

	function getBody() {
		$content = '';
		$params = $this->get('router')->getParam('params');
		if ($this->get('router')->getParam('controller') == 'tree') {
			$content .= $this->get('container')->callMethod('tree', 'index', $params);
		} elseif ($this->get('router')->getParam('methodName') == 'index') {
			$content .= $this->get('container')->callMethod('tree', 'index', array($this->get('router')->getParam('nodeName')));
		}
		$content .= $this->getModuleBody();
		return $content;
	}

	/* search */
	function getSearchResultRef(&$a) {
		return $this->getUrl($a);
	} 

	function getSearchResults($text) {
		return $this->getTableSearchResults($text, 'tree', "publish='on'", '', 'title');
	}

	/* Map */
	function getMapList($uri = 0) {
		$a = $this->getNodes($uri);
		$block = strval($uri) == '0' ? '' :  '_sub';
		if (count($a)) {
			foreach ($a as $k => $i) {
				$a[$k]['sub'] = '';
				if (isset($i['module_id_name'])) {
					$unit = new \Controller\Controller($i['module_id_name']);
					$a[$k]['sub'] = $unit->getMap();
				}
				$a[$k]['sub'] .= $this->getMapList($i['id']);
			}
		}
		$this->get('smarty')->assign('items', $a);
		$this->get('smarty')->assign('block', $block);
		return $this->get('smarty')->fetch('service/map.tpl');
	}

	function getMap() {
		return $this->getMapList();
	}
}
