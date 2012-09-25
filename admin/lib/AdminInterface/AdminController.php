<?php

namespace AdminInterface;

class AdminController {
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
			$name = '\\AdminInterface\\Action\\Action';
			$content = new $name($this);
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

	function getTableMenu() {
		$this->get('smarty')->assign('tables', $this->getMenuItems());
		return $this->get('smarty')->fetch('admin/submenu.tpl');
	}

	function getIndex() {
		$title = str_replace(' ', '&nbsp;', $this->title);
		$message = $this->get('util')->_sessionVar('message');
		unset($_SESSION['message']);
		if ($menu = $this->getMenuItems()) {
			foreach ($menu as $m) {
				if ($this->getBaseTableRef() == $m['ref']) {
					$title .= ':&nbsp;'.$m['name'];
				}
			}
			if ($this->get('util')->_getVar('action')) {
				switch ($this->get('util')->_getVar('action')) {
					case 's_table':
					$title .= ':&nbsp;Настройка таблицы';break;
					case 's_insert':
					$title .= ':&nbsp;Добавление';break;
					case 's_update':
					$title .= ':&nbsp;Редактирование';break;
				}
			}
		}
		$title .= !empty($message) ? ':&nbsp;'.$message : '';
		$this->get('smarty')->assign('title', $title);
		$this->get('smarty')->assign('body', $this->getContent());
		return $this->get('smarty')->fetch('admin/mainbody.tpl');
	}
	
	public function get($name) {
		global $container, $security;
		if ($name == 'container') {
			return $container;
		} elseif ($name == 'security') {
			return $security;
		} else {
			return $container->get($name);
		}
	}
}
