<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;

class CommonController extends Controller {
	
	public function blockAction($params) {
		$item = $this->get('container')->getItem('page_block',"name='{$params[0]}' AND publish=1");
		
		return $item ? $item['content'] : '';
	}
	
	public function breadcrumbAction() {
		
		$node = $this->getManager('Fuga:Common:Page')->getCurrentNode();
		$nodes = $this->getManager('Fuga:Common:Page')->getPathNodes($node['id']);
		//TODO add category block
		$action = $this->get('router')->getParam('action');	
		$params = $this->get('router')->getParam('params');
		if ($node['name'] == 'catalog' && $action == 'index' && isset($params[0])) {
			$nodes = array_merge($nodes, $this->getManager('Fuga:Common:Category')->getPathNodes($params[0]));
		} elseif ($node['name'] == 'catalog' && $action == 'stuff') {
			if (isset($params[0])) {
				$product = $this->get('container')->getItem('catalog_product', $params[0]);
				$nodes = array_merge($nodes, $this->getManager('Fuga:Common:Category')->getPathNodes($product['category_id']));
			}
		} elseif ($node['name'] == 'catalog' && $action == 'brands') {
			$nodes[] = array(
				'title' => 'Бренды',
				'ref'   => $this->get('container')->href($this->get('router')->getParam('node'), 'brands', array())
			);
		} elseif ($node['name'] == 'catalog' && $action == 'brand') {
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
		
		return $this->render('breadcrumb.tpl', compact('nodes', 'action'));
	}
	
	/* Map */
	function getMapList($uri = 0) {
		
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

		$nodes = array();
		$items = $this->getManager('Fuga:Common:Page')->getNodes($uri);
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
		return $this->getManager('Fuga:Common:Form')->getForm($params[0]);
	}
	
	public function voteAction($params) {
		return $this->getManager('Fuga:Common:Vote')->getForm($params[0]);
	}
	
}