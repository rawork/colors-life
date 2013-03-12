<?php

namespace Fuga\CommonBundle\Model;

class PageManager extends ModelManager {
	
	private $node;
	
	public function getNodes($uri = 0, $where = "publish=1") {
		$nodes = $this->get('container')->getItemsRaw(
			'SELECT t1.*, t3.name as module_id_name, t3.path as module_id_path FROM page_page as t1 '.
			'LEFT JOIN page_page as t2 ON t1.parent_id=t2.id '.
			'LEFT JOIN config_modules as t3 ON t1.module_id=t3.id '.
			"WHERE t1.publish=1 AND t1.locale='".$this->get('router')->getParam('lang')."' AND ".(is_numeric($uri) ? ($uri == 0 ? ' t1.parent_id=0 ' : 't2.id='.$uri.' ') : "t2.name='".$uri."' ").
			'ORDER BY t1.sort,t1.name '
		);
		foreach ($nodes as &$node) {
			$node['ref'] = $this->getUrl($node);
		}
		
		return $nodes;	
	}
	
	public function getUrl($node) {
		return trim($node['url']) ?: $this->get('container')->href($node['name']);
	}
	
	public function getPathNodes($id = 0) {
		global $PATH_MAINPAGE_TITLE;
		$nodes = $this->get('container')->getTable('page_page')->getPrev($id);
		if ($nodes[0]['name'] != '/') {
			array_unshift($nodes, array(
				'name' => '/', 
				'url' => '', 
				'title' => $PATH_MAINPAGE_TITLE[$this->get('router')->getParam('lang')]
			));
		}
		foreach ($nodes as &$node) {
			if ('/' == $node['name']) {
				$node['title'] = $PATH_MAINPAGE_TITLE[$this->get('router')->getParam('lang')];
			}	
			$node['ref'] = $this->getUrl($node);
		}
		
		return $nodes;
	}
	
	public function getCurrentNode() {
		if (!$this->node) {
			if ($this->get('router')->hasParam('node')) {
				$this->node = $this->get('container')->getItem('page_page', "name='".$this->get('router')->getParam('node')."'");
			}
		}
		
		return $this->node;
	}
	
}
