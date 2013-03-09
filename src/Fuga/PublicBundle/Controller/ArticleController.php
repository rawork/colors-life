<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class ArticleController extends PublicController {
	
	public function __construct() {
		parent::__construct('article');
	}
	
	public function cloudAction() {
		$items = $this->get('container')->getItems('article_tag', null, 'name');
		return $this->render('article/cloud.tpl', compact('items'));
	}
	
	public function indexAction() {
		$tagId = $this->get('util')->_getVar('tag', true);
		$page = $this->get('util')->_getVar('page', true, 1);
		$paginator = $this->get('paginator');
		$paginator->setTemplate('public');
		if ($tagId){
			$tag = $this->get('container')->getItem('article_tag', $tagId, null, 'id,name');
			$paginator->paginate(
				$this->get('container')->getTable('article_article'),
				$this->get('container')->href($this->get('router')->getParam('node')).'?page=###',
				"tag LIKE '%".$tag['name']."%' AND publish=1",
				$this->getParam('per_page'),
				$page
			);
			$items = $this->get('container')->getItems(
					'article_article', 
					"tag LIKE '%".$tag['name']."%' AND publish=1",
					null,
					$paginator->limit
			);		
			$local_h1 = '<h1>Статьи по теме &laquo;'.$tag['name'].'&raquo;</h1>';
		} else { 
			$paginator->paginate(
				$this->get('container')->getTable('article_article'),
				$this->get('container')->href($this->get('router')->getParam('node')).'?page=###',
				"publish=1",
				$this->getParam('per_page'),
				$page
			);
			$items = $this->get('container')->getItems(
				'article_article', 
				"publish=1",
				null,
				$paginator->limit
			);
		}
		
		return $this->render('article/index.tpl', compact('items', 'paginator', 'local_h1'));
	}
	
	public function readAction($params) {
		if (!isset($params[0])) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$item = $this->get('container')->getItem('article_article', 'id='.$params[0].' AND publish=1');
		if (!$item) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$this->get('container')->setVar('title', $item['name']);
		
		return $this->render('article/read.tpl', compact('item'));
	}
	
	public function tagsAction() {
		$items = $this->get('container')->getItems('article_tag', null, 'name');
		
		return $this->render('article/tags.tpl', compact('items'));
	}
	
}