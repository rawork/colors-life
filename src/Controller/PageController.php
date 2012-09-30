<?php

namespace Controller;

use Common\AbstractController;
use Controller\AuthController;
use Model\MetaManager;
use Model\TemplateManager;

class PageController extends AbstractController {
	
	private $controller;
	private $node;
	private $nodeEntity;
	private $isService;

	public function getNodes($uri = 0, $where = "publish='on'", $limit = false) {
		$ret = $this->get('container')->getNativeItems(
			'SELECT t1.*, t3.name as module_id_name FROM tree_tree as t1 '.
			'LEFT JOIN tree_tree as t2 ON t1.p_id=t2.id '.
			'LEFT JOIN config_modules as t3 ON t1.module_id=t3.id '.
			"WHERE t1.publish='on' AND t1.lang='".$this->get('router')->getParam('lang')."' AND ".(is_numeric($uri) ? ($uri == 0 ? ' t1.p_id=0 ' : 't2.id='.$uri.' ') : "t2.name='".$uri."' ").
			'ORDER BY t1.ord,t1.name '.
			'LIMIT '.($limit ? $limit : '0,100')
		);
		foreach ($ret as $k => $v) {
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
		if (isset($this->nodeEntity))
			$nodes = $this->get('container')->getTable('tree_tree')->getPrev($this->nodeEntity['id']);
		if ($this->controller instanceof Controller) {
			$nodes = array_merge($nodes, $this->controller->getPathArray());
		}

		return $nodes;
	}

	function getPath($delimeter = '&gt;') {
		global $PATH_MAINPAGE_TITLE;
		if (count($nodes = $this->getPathArray())) {
			
			if ($nodes[0]['name'] != '/')
				$nodes = array_merge(array(array('name' => '/', 'title' => $PATH_MAINPAGE_TITLE[$this->get('router')->getParam('lang')])), $nodes);
			foreach ($nodes as $k => $v) {
				if (isset($v['name']) && empty($v['ref'])) 
					$nodes[$k]['ref'] = $this->getUrl($v);
				if (isset($v['name']) && $v['name'] == '/')
					$nodes[$k]['title'] = $PATH_MAINPAGE_TITLE[$this->get('router')->getParam('lang')];
			}
			$this->get('smarty')->assign('pathitems', $nodes);
			$this->get('smarty')->assign('methodName', $this->get('router')->getParam('methodName'));
			$this->get('smarty')->assign('delimeter', $delimeter);
			return $this->get('smarty')->fetch('service/breadscrumb.tpl');
		}
	}

	public function getTitle() {
		return $this->nodeEntity['title'];
	}
	
	public function getH1() {
		return $this->getTitle();
	}

	private function getModuleContent() {
		$content = '';
		if ($this->controller && $this->isService) {
			$content = $this->controller->getContent();
		} elseif ($this->controller) {
			try {
				$content = $this->get('container')->callMethod(
						$this->controller->name, 
						$this->get('router')->getParam('methodName'), 
						$this->get('router')->getParam('params')
				);
			} catch (\Exception $e) {
				throw $this->createNotFoundException('Неcуществующая страница');
			}
		}
		return $content;
	}

	public function getContent() {
		$content = '';
		if ($this->get('router')->getParam('methodName') == 'index') {
			$content = $this->render('static.content.tpl', array('node' => $this->nodeEntity));
		}
		$content .= $this->getModuleContent();
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

	public function getMap() {
		return $this->getMapList();
	}
	
	public function indexAction() {
		
		$this->get('container')->register('tree', $this);
		$this->get('container')->register('auth', new \Controller\AuthController());
		$this->get('container')->register('meta', new \Model\MetaManager());
		
		$title = $this->get('meta')->getTitle();
		if (!$title) {
			$title = strip_tags($this->getTitle());
		}

		$params = array(
			'mainbody' => $this->getContent().' ',
			'title' => $title,
			'h1' => $this->getH1(),
			'meta' => $this->get('meta')->getMeta(),
			'mail_to' => $GLOBALS['ADMIN_EMAIL'],
			'tree' => $this->get('tree'),
			'auth' => $this->get('auth'),
		);
		
		$this->get('templating')->setParams(array_merge($this->get('container')->getVars(), $params));
		
		$templateManager = new \Model\TemplateManager();
		$data = $this->get('smarty')->fetch($templateManager->getByNode($this->node));
		if ($data) {
//			$this->get('cache')->save($data, $GLOBALS['cur_page_id']);
			echo $data;
		} else {
			throw new \Exception('Template calculate error');
		}

	}

	public function handle() {
		global $LIB_DIR;
		
		if (preg_match('/^\/notice\/[\d]{6}$/', $_SERVER['REQUEST_URI'])) {
			$this->get('smarty')->assign('order', str_replace('/notice/', '', $_SERVER['REQUEST_URI']));
			echo $this->get('smarty')->fetch('page.notice.tpl');
			exit;
		}
		
		if (!$this->get('router')->hasParam('node')) {
			throw $this->createNotFoundException('Неcуществующая страница');
		}
		
		$this->node = $this->get('router')->getParam('node');
		$this->methodName = $this->get('router')->getParam('methodName');

		$this->nodeEntity = $this->get('container')->getItem('tree_tree', "name='$this->node'");
		if (!$this->nodeEntity) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		if (!empty($this->nodeEntity['module_id_name'])) {
			$controllerName = $this->nodeEntity['module_id_name'];
			if (file_exists($LIB_DIR.'/Controller/'.ucfirst($controllerName).'Controller.php')) {
				$className = '\\Controller\\'.ucfirst($controllerName).'Controller';
				$this->controller = new $className($controllerName);
				$this->isService = true;
			} else {
				$this->controller = new \Controller\Controller($controllerName);
				$this->isService = false;
			}
		}	
		
		return $this->indexAction();
	}
	
}
