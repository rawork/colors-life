<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class NewsController extends PublicController {
	
	public function __construct() {
		parent::__construct('news');
	}
	
	public function indexAction() {
		$page = $this->get('util')->_getVar('page', true, 1);
		$paginator = $this->get('paginator');
		$paginator->paginate(
				$this->get('container')->getTable('news_news'),
				$this->get('container')->href($this->get('router')->getParam('node')).'?page=###',
				'publish=1',					 		  
				$this->getParam('per_page'),
				$page
		);
		$paginator->setTemplate('public');
		$items = $this->get('container')->getItems('news_news', 'publish=1', null, $paginator->limit);
		
		return $this->get('templating')->render('news/index.tpl', compact('items', 'paginator'));
	}
	
	public function lentaAction() {
		$items = $this->get('container')->getItems('news_news', 'publish=1', null, $this->getParam('per_lenta'));
		
		return $this->get('templating')->render('news/lenta.tpl', compact('items'));
	}
	
	public function readAction($params) {
		if (!isset($params[0])) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$news = $this->get('container')->getItem('news_news', 'id='.$params[0].' AND publish=1');
		if (!$news) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$this->get('container')->setVar('title', $news['name']);
		
		return $this->get('templating')->render('news/read.tpl', compact('news'));
	}
}