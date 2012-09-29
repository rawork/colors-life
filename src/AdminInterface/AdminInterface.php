<?php

namespace AdminInterface;

use Common\AbstractController;
use Admin\Admin;

class AdminInterface extends AbstractController {
	private $units = array(); 
	private $currentModule;
	private $currentState;
	private $states;
	private $modules;
	
	public function __construct() {
		$this->setOptions();
		$this->setModules();
		$this->setStates();
	}
	
	private function setStates() {
		$this->states = array(
			'content' => 'Структура и контент',
			'service' => 'Сервисы',
			'settings' => 'Настройки',
		); 
	}
	
	private function setOptions() {
		$this->currentModule = $this->get('router')->getParam('module');
		$this->currentState = $this->get('router')->getParam('state');
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
			$this->modules = $ret;
		} else {
			unset($_SESSION['user']);
			unset($_SESSION['ukey']);
			session_destroy();
			header('/admin/');
		}	
	}

	private function addAdmin($module, $users) 
	{
		$admin = new Admin($module['name']);
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
	
	private function backupAction() {
		$file = $this->get('util')->_getVar('file', false, 'empty.file');
		$filename = $GLOBALS['BACKUP_DIR'].'/'.$file;
		$sfilename = $file;
		if (!file_exists($filename)) {
			header ("HTTP/1.0 404 Not Found");
			die();
		}
		// сообщаем размер файла
		header( 'Content-Length: '.filesize($filename) );
		// дата модификации файла для кеширования
		header( 'Last-Modified: '.date("D, d M Y H:i:s T", filemtime($filename)) );
		// сообщаем тип данных - zip-архив
		header('Content-type: text/rtf');
		// файл будет получен с именем $filename
		header('Content-Disposition: attachment; filename="'.$sfilename.'"');
		// начинаем передачу содержимого файла
		$handle = fopen($filename, 'rb');
		while (!feof($handle)) {
			echo fread($handle, 8192);
		}
		fclose($handle);
	}

	public function show() {
		if ($this->get('router')->hasParam('action') && $this->get('router')->getParam('action') == 'backupget') {
			$this->backupAction();
		} else {
			$params = array(
				'user' => $this->get('util')->_sessionVar('user'),
				'languages' => $this->get('connection')->getItems('config_languages', 'SELECT * FROM config_languages'),
				'currentLanguage' => $this->get('util')->_sessionVar('lang', false, 'ru'),
				'module' => $this->currentModule,
				'modules' => $this->modules,
				'states' => $this->states,
				'state' => $this->currentState,
				'version' => $GLOBALS['LIB_VERSION'],
				'content' => $this->currentModule ? $this->getModule($this->currentModule)->getIndex() : ''
			);
			echo $this->render('admin/layout.new.tpl', $params);
		}
	}
	
}
