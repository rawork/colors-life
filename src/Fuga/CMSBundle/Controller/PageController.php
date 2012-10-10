<?php

namespace Fuga\CMSBundle\Controller;

use Fuga\CMSBundle\Model\TemplateManager;
use Fuga\CMSBundle\Model\MetaManager;
use Fuga\CMSBundle\Controller\PublicController;
use Fuga\PublicBundle\Controller\AuthController;

class PageController extends Controller {
	
	private $controller;
	private $node;
	private $nodeEntity;
	private $isService;

	public function getNodes($uri = 0, $where = "publish=1", $limit = false) {
		$ret = $this->get('container')->getNativeItems(
			'SELECT t1.*, t3.name as module_id_name FROM page_page as t1 '.
			'LEFT JOIN page_page as t2 ON t1.parent_id=t2.id '.
			'LEFT JOIN config_modules as t3 ON t1.module_id=t3.id '.
			"WHERE t1.publish=1 AND t1.locale='".$this->get('router')->getParam('lang')."' AND ".(is_numeric($uri) ? ($uri == 0 ? ' t1.parent_id=0 ' : 't2.id='.$uri.' ') : "t2.name='".$uri."' ").
			'ORDER BY t1.sort,t1.name '.
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
			$nodes = $this->get('container')->getTable('page_page')->getPrev($this->nodeEntity['id']);
		if ($this->controller instanceof PublicController) {
			$nodes = array_merge($nodes, $this->controller->getPathArray());
		}

		return $nodes;
	}

	function getPath($delimeter = '&gt;') {
		global $PATH_MAINPAGE_TITLE;
		if (count($nodes = $this->getPathArray())) {
			
			if ($nodes[0]['name'] != '/')
				$nodes = array_merge(array(array('name' => '/', 'title' => $PATH_MAINPAGE_TITLE[$this->get('router')->getParam('lang')])), $nodes);
			foreach ($nodes as &$node) {
				if (isset($node['name']) && empty($node['ref'])) 
					$node['ref'] = $this->getUrl($node);
				if (isset($node['name']) && $node['name'] == '/')
					$node['title'] = $PATH_MAINPAGE_TITLE[$this->get('router')->getParam('lang')];
			}
			unset($node);
			return $this->render('service/breadscrumb.tpl', compact('nodes', 'delimeter'));
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
				echo $e->getMessage();
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

	/* Map */
	function getMapList($uri = 0) {
		$nodes = $this->getNodes($uri);
		$block = strval($uri) == '0' ? '' :  '_sub';
		if (count($nodes)) {
			foreach ($nodes as $node) {
				$node['sub'] = '';
				if (isset($node['module_id_name'])) {
					$unit = new PublicController($node['module_id_name']);
					$node['sub'] = $unit->getMap();
				}
				$node['sub'] .= $this->getMapList($node['id']);
			}
			unset($node);
		}
		return $this->render('service/map.tpl', compact('nodes', 'block'));
	}

	public function getMap() {
		return $this->getMapList();
	}
	
	public function indexAction() {
		
		$this->get('container')->register('page', $this);
		$this->get('container')->register('auth', new AuthController());
		$this->get('container')->register('meta', new MetaManager());
		
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
			'page' => $this->get('page'),
			'auth' => $this->get('auth'),
		);
		
		$this->get('templating')->setParams(array_merge($this->get('container')->getVars(), $params));
		
		$templateManager = new TemplateManager();
		$data = $this->render($templateManager->getByNode($this->node));
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
			echo $this->render('page.notice.tpl', array('order' => str_replace('/notice/', '', $_SERVER['REQUEST_URI'])));
			exit;
		}
		
		if (preg_match('/^\/subscribe\//', $_SERVER['REQUEST_URI'])) {
			$key = $this->get('util')->_getVar('key');
			$_SESSION['subscribe_message'] = $this->get('container')->getManager('maillist')->activate($key);
			header('location: /subscribe-process.htm');
			exit;
		}
		
		if (!$this->get('router')->hasParam('node')) {
			throw $this->createNotFoundException('Неcуществующая страница');
		}
		
		$this->node = $this->get('router')->getParam('node');
		$this->methodName = $this->get('router')->getParam('methodName');
		$this->get('templating')->setParam('methodName', $this->methodName);
		$this->nodeEntity = $this->get('container')->getItem('page_page', "name='$this->node'");
		if (!$this->nodeEntity) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		if (!empty($this->nodeEntity['module_id_name'])) {
			$controllerName = $this->nodeEntity['module_id_name'];
			if (file_exists($LIB_DIR.'/Fuga/PublicBundle/Controller/'.ucfirst($controllerName).'Controller.php')) {
				$className = '\\Fuga\\PublicBundle\\Controller\\'.ucfirst($controllerName).'Controller';
				$this->controller = new $className($controllerName);
				$this->isService = true;
			} else {
				$this->controller = new PublicController($controllerName);
				$this->isService = false;
			}
		}	
		
		return $this->indexAction();
	}
	
}
