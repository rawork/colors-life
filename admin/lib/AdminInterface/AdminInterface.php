<?php

namespace AdminInterface;

use Admin\BaseAdmin;

class AdminInterface {
	private $units = array(); 
	private $currentModule;
	private $currentState;
	
	public function __construct() {
		$this->setOptions();
		$this->setModules();
	}

	protected function setOptions() {
		$this->currentModule = $this->get('router')->getParam('module');
		$this->currentState = $this->get('router')->getParam('state');
		$this->get('smarty')->assign('user', $this->get('util')->_sessionVar('user'));
		$this->get('smarty')->assign('lang', $this->get('util')->_sessionVar('lang', false, 'ru'));
		$this->get('smarty')->assign('module', $this->currentModule);
		$this->get('smarty')->assign('state', $this->currentState);
	}

	protected function setModules() {
	global $PRJ_DIR, $THEME_REF;
		$modules = $this->get('container')->getModules();
		if (count($modules)) {
			foreach ($modules as $module)
				if ($module['name'] == $this->currentModule)
					$this->addAdmin($module, array($this->get('util')->_sessionVar('user') => 1));
			switch ($this->currentState) {
				case 'content': $stateLetter = 'C'; break;
				case 'settings': $stateLetter = 'A'; break;
				case 'service': $stateLetter = 'S'; break;
				default : $stateLetter = 'N';
			}
			$ret = array();
			foreach ($modules as $module) {
				if ($module['ctype'] == $stateLetter) {
					$basePath = $THEME_REF.'/img/module/';
					$ret[] = array(
						'name' => $module['name'],
						'title' => $module['title'],
						'ref' => $this->getBaseRef($module['name']),
						'icon' => (file_exists($PRJ_DIR.$basePath.$module['name'].'.gif') ? $basePath.$module['name'] : $basePath.'folder').'.gif',
						'tablelist' => $module['name'] == $this->currentModule ? $this->getModule($module['name'])->getTableMenu() : '',
						'current' => $module['name'] == $this->currentModule
					);	
				}
			}
			$this->get('smarty')->assign('modules', $ret);
		} else {
			unset($_SESSION['user']);
			unset($_SESSION['ukey']);
			session_destroy();
			header('/admin/');
		}	
	}

	private function addAdmin($module, $users) 
	{
		$admin = new \Admin\Admin($module['name']);
		$this->units[$module['name']] = new AdminController($admin, $module['title'], $users);
	}

	public function getModule($moduleName) {
		if (isset($this->units[$moduleName]) && $this->units[$moduleName]->isAvailable()){
			return $this->units[$moduleName];
		} else {
			throw new \Exception('Отсутствует запрашиваемый модуль: '.$moduleName);
		}
	}

	public function getBaseRef($moduleName) {
		return $this->currentState.'/'.$moduleName.'/';
	}

	public function cron($period) {
		if (!empty($period)) {
			set_time_limit(0);
			echo 'Cron ('.$period.'):';
			foreach ($this->units as $u) {
				echo ' '.$u->currentModule->name;
				$name = 'every'.$period;
				$u->currentModule->$name();
			}
		} else {
				throw new \Exception('Cron params error');
		}
	}

	public function show() {
		$this->get('smarty')->assign('version', $GLOBALS['LIB_VERSION']);
		$this->get('smarty')->assign('content', !empty($this->currentModule) ? $this->getModule($this->currentModule)->getIndex() : '');
		$this->get('smarty')->assign('langs', $this->get('connection')->getItems('config_languages', 'SELECT * FROM config_languages'));
		$this->get('smarty')->display('admin/layout.tpl');
		ob_end_flush();
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
