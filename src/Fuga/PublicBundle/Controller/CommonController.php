<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;

class CommonController extends Controller {
	
	public function blockAction($params) {
		$item = $this->get('container')->getItem('page_block',"name='{$params[0]}' AND publish=1");
		
		return $item ? $item['content'] : '';
	}
	
	public function breadcrumbAction($params) {
		global $PATH_MAINPAGE_TITLE;
		$nodes = $this->get('container')->getManager('Fuga:Common:Page')->getPathNodes();
		if (!$nodes) {
			return;
		}	
		if ($nodes[0]['name'] != '/')
			$nodes = array_merge(array(array('name' => '/', 'title' => $PATH_MAINPAGE_TITLE[$this->get('router')->getParam('lang')])), $nodes);
		foreach ($nodes as &$node) {
			if (isset($node['name']) && empty($node['ref'])) 
				$node['ref'] = $this->get('container')->getManager('Fuga:Common:Page')->getUrl($node);
			if (isset($node['name']) && $node['name'] == '/')
				$node['title'] = $PATH_MAINPAGE_TITLE[$this->get('router')->getParam('lang')];
		}
		unset($node);
		$action = $this->get('router')->getParam('action');
		
		return $this->render('breadcrumb.tpl', compact('nodes', 'action'));
	}
	
	/* Map */
	function getMapList($uri = 0) {
		$nodes = array();
		$items = $this->get('container')->getManager('Fuga:Common:Page')->getNodes($uri);
		$block = strval($uri) == '0' ? '' :  '_sub';
		if (count($items)) {
			foreach ($items as $node) {
				$node['sub'] = '';
				if ($node['module_id']) {
					$controller = $this->get('container')->createController($node['module_id_path']);
					$node['sub'] = $controller->getMap();
				}
				$node['sub'] .= $this->getMapList($node['id']);
				$nodes[] = $node;
			}
		}
		return $this->render('map.tpl', compact('nodes', 'block'));
	}

	public function mapAction() {
		return $this->getMapList();
	}
	
	public function subscribeAction() {
		$subscribe_message = '';
		if (isset($_SESSION['subscribe_message'])) {
			$subscribe_message = $_SESSION['subscribe_message'];
		}
		return $this->render('subscribe/form.tpl', compact('subscribe_message'));
	}
	
	public function formAction($params) {
		return $this->get('container')->getManager('Fuga:Common:Form')->getForm($params[0]);
	}
	
	public function voteAction($params) {
		return $this->get('container')->getManager('Fuga:Common:Vote')->getForm($params[0]);
	}
	
}