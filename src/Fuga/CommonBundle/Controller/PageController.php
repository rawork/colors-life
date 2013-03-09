<?php

namespace Fuga\CommonBundle\Controller;

use Fuga\PublicBundle\Controller\AuthController;

class PageController extends Controller {
	
	public $nodeEntity;
	private $node;

	public function getTitle() {
		return $this->nodeEntity['title'];
	}
	
	public function getH1() {
		return $this->getTitle();
	}
	
	public function getStatic() {
		$content = ' ';
		if ($this->get('router')->getParam('action') == 'index') {
			$content = $this->render('page/static.tpl', array('node' => $this->nodeEntity));
		}
		
		return $content;
	}
	
	public function getContent() {
		if ($this->nodeEntity['module_id']) {
			try {
				return $this->get('container')->callAction(
					$this->nodeEntity['module_id_path'].':'.$this->get('router')->getParam('action'), 
					$this->get('router')->getParam('params')
				);
			} catch (\Exception $e) {
				throw $this->createNotFoundException('Неcуществующая страница');
			}
		}
		return '';
	}

	public function indexAction() {
		$this->get('container')->register('page', $this);
		$this->get('container')->register('auth', new AuthController());

		if ($this->get('container')->isXmlHttpRequest()) {
			echo $this->getContent();
			exit;
		}
		
		$title = $this->get('container')->getManager('Fuga:Common:Meta')->getTitle();
		if (!$title) {
			$title = strip_tags($this->getTitle());
		}
		
		$this->get('templating')->assign(array(
			'h1' => $this->getH1(),
			'title' => $title
		));

		$params = array(
			'mainbody' => $this->getStatic().$this->getContent(),
			'meta' => $this->get('container')->getManager('Fuga:Common:Meta')->getMeta(),
			'auth' => $this->get('auth'),
			'links' => $this->get('container')->getManager('Fuga:Common:Page')->getNodes('/')
		);
		
		$this->get('templating')->assign($params);
		$data = $this->render($this->get('container')->getManager('Fuga:Common:Template')->getByNode($this->node), $this->get('container')->getVars());
		if ($data) {
//			$this->get('cache')->save($data, $GLOBALS['cur_page_id']);
			echo $data;
		} else {
			throw new \Exception('Ошибка вычисления шаблона');
		}

	}

	public function handle() {
		if (preg_match('/^\/notice\/[\d]{6}$/', $_SERVER['REQUEST_URI'])) {
			echo $this->render('page.notice.tpl', array('order' => str_replace('/notice/', '', $_SERVER['REQUEST_URI'])));
			exit;
		}
		
		if (preg_match('/^\/subscribe\//', $_SERVER['REQUEST_URI'])) {
			$key = $this->get('util')->_getVar('key');
			$_SESSION['subscribe_message'] = $this->get('container')->getManager('Fuga:Common:Maillist')->activate($key);
			header('location: '.$this->get('container')->href('subscribe-process'));
			exit;
		}
		
		if (!$this->get('router')->hasParam('node')) {
			throw $this->createNotFoundException('Неcуществующая страница');
		}
		
		$this->node = $this->get('router')->getParam('node');
		$this->get('templating')->assign(array('action', $this->get('router')->getParam('action')));
		$this->nodeEntity = $this->get('container')->getItem('page_page', "name='$this->node'");
		if (!$this->nodeEntity) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		
		return $this->indexAction();
	}
	
}
