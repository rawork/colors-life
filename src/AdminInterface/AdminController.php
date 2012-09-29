<?php

namespace AdminInterface;

use Common\AbstractController;
use AdminInterface\Action\Action;

class AdminController extends AbstractController {
	public $module;
	public $title;
	public $icon;
	public $description;
	public $users;
	
	function __construct(&$module, $title, $users) {
		$this->module = $module;
		$this->title = $title;
		$this->users = $users;
	}
	
	function isAvailable() {
		return $this->get('security')->isSuperuser() || $this->users[$this->get('util')->_sessionVar('user')] == 1;
	}
	
	function getBaseRef() {
		return '/admin/'.$this->get('router')->getParam('state').'/'.$this->module->name.'/';
	}
	
	function createBaseTableRef($tableName) {
		return $this->getBaseRef().$tableName;
	}
	
	function getBaseTableKey() {
		if (!$this->get('router')->hasParam('table')) {
			if (count($this->module->tables)) {
				foreach ($this->module->tables as $tableName => $v) {
					$this->get('router')->setParam('table', $tableName);
					break;
				}
			} else {
				throw new \Exception('Tables not exists');
			}
		}
		return $this->get('router')->getParam('table');
	}
	
	function getBaseTableRef() {
		return $this->createBaseTableRef($this->getBaseTableKey());
	}
	
	function getBaseTable() {
		return isset($this->module->tables[$this->getBaseTableKey()]) ? $this->module->tables[$this->getBaseTableKey()] : null;
	}
	
	function messageAction($msg, $path = null) {
		if (!$path) {
			$path = $this->getBaseTableRef();
		}
		$_SESSION['message'] = $msg;
		header('location: '.$path);
	}
	
	function getContent() {
		$action = $this->get('router')->getParam('action');
		try {
			$name = '\\AdminInterface\\Action\\'.ucfirst($action).'Action';
			$content = new $name($this);
		} catch (\Exception $e) {
			$content = new Action($this);
		}
		return $content->getText();
	}

	function getMenuItems() {
		$ret = array();
		foreach ($this->module->tables as $k => $v) {
			if (empty($v->params['is_hidden'])) {
				$ret[] = array (
					'ref' => $this->createBaseTableRef($k),
					'name' => $v->title
				);
			}
		}
		if ($this->get('security')->isSuperuser()) {
			if (count($this->module->params)) {
				$ret[] = array (
					'ref' => $this->getBaseTableRef().'/setting',
					'name' => 'Настройки'
				);
			}
		}
		if ($this->module->name == 'config' && $this->get('security')->isSuperuser()) {
			$ret[] = array (
				'ref' => $this->getBaseTableRef().'/backup',
				'name' => 'Резервное копирование'
			);
		}
		if ($this->module->name == 'articles' && $this->get('security')->isSuperuser()) {
			$ret[] = array (
				'ref' => $this->getBaseTableRef().'/counttag',
				'name' => 'Расчет тегов'
			);
		}
		if ($this->module->name == 'maillist' && $this->get('security')->isSuperuser()) {
			$ret[] = array (
				'ref' => $this->getBaseTableRef().'/send',
				'name' => 'Отправка писем'
			);
		}
		if (__PROCESSOR_VISIBLE) {
			if ($this->get('security')->isSuperuser() && empty($this->module->is_admin)) {
				$ret[] = array (
					'ref' => $this->getBaseTableRef().'/methods',
					'name' => 'Методы'
				);
			}
		}
		return $ret;
	}

	public function getTableMenu() {
		return $this->render('admin/submenu.tpl', array('tables' => $this->getMenuItems()));
	}
	
	public function getMessage() {
		$message = $this->get('util')->_sessionVar('message');
		unset($_SESSION['message']);
		return $message;
	}
	
	public function getTitle() {
		$title = str_replace(' ', '&nbsp;', $this->title);
		if ($menu = $this->getMenuItems()) {
			foreach ($menu as $item) {
				if ($this->getBaseTableRef() == $item['ref']) {
					$title .= ': '.$item['name'];
				}
			}
			switch ($this->get('router')->getParam('action')) {
				case 'table':
					$title .= ': Настройка таблицы';break;
				case 'add':
					$title .= ': Добавление';break;
				case 'edit':
					$title .= ': Редактирование';break;
				case 'backup':
					$title .= ': Резервное копирование';break;
				case 'export':
					$title .= ': Экспорт';break;
				case 'import':
					$title .= ': Импорт';break;
				case 'setting':
					$title .= ': Редактирование настроек';break;
				case 'groupedit':
					$title .= ': Груповое редактирование';break;
			}
		}
		return $title;
	}

	public function getIndex() {
		$params = array(
			'title' => $this->getTitle(),
			'message' => $this->getMessage(),
			'content' => $this->getContent()
		);
		return $this->render('admin/content.tpl', $params);
	}
	
}
