<?php

namespace Fuga\Component;

use Fuga\Component\DB\Table;
use Fuga\Component\Paginator;
use Fuga\Component\Mailer\Mailer;
use Fuga\Component\Scheduler\Scheduler;
use Fuga\Component\Storage\FileStorage;
use Fuga\Component\Storage\ImageStorageDecorator;
use Fuga\Component\Search\SearchEngine;
use Fuga\Component\Cache\Cache;

class Container 
{
	private $tables;
	private $modules;
	private $ownmodules;
	private $templateVars = array();
	private $services = array();
	private $managers = array();
	

	function __construct() {
		
	}
	
	public function initialize() {
		$this->tables = $this->getAllTables();
	}

	public function getModule($moduleName) {
		return empty($this->modules[$moduleName]) ? array() : $this->modules[$moduleName];
	}

	public function getModuleById($moduleId) {
		foreach ($this->modules as $module) {
			if ($moduleId == $module['id']) {
				return $module;
			}
		}
		return null;
	}

	public function getModules() {
		if (!$this->ownmodules) {
			$modules = array();
			if ($this->get('security')->isSuperuser()) {
				$modules = $this->modules;
			} elseif ($user = $this->get('security')->getCurrentUser()) {
				$modules = $this->get('connection')->getItems('config_modules', ' SELECT id, sort, name, title, \'content\' AS ctype FROM config_modules WHERE id IN ('.$user['rules'].') ORDER BY sort, title');
				if ($user['is_admin']) {
					$query = "SELECT id, sort, name, title, 'settings' AS ctype FROM system_modules
						UNION SELECT id, sort, name, title, 'service' AS ctype FROM system_services
						ORDER BY sort, title";
				} else {
					$query = "SELECT id, sort, name, title, 'settings' AS ctype FROM system_modules WHERE name IN ('config', 'meta')
						UNION SELECT id, sort, name, title, 'service' AS ctype FROM system_services
						ORDER BY sort, title";
				}
				$modules = array_merge($modules , $this->get('connection')->getItems('modules', $query));
			}
			$this->ownmodules = $modules;
		}
		return $this->ownmodules;
	}
	
	public function getModulesByState($state) {
		$modules = array();
		foreach ($this->getModules() as $module) {
			if ($state == $module['ctype']) {
				$modules[$module['name']] = $module;
			}
		}
		return $modules;
	}
	
	private function getAllTables() {
		global $LIB_DIR;	
		$ret = array();
		$this->modules = array();
		$query = "SELECT id, sort, name, title, 'content' AS ctype FROM config_modules
		UNION SELECT id, sort, name, title, 'settings' AS ctype FROM system_modules
		UNION SELECT id, sort, name, title, 'service' AS ctype FROM system_services
		ORDER BY sort, title";
		$modules = $this->get('connection')->getItems('modules', $query);
		foreach ($modules as $module) {
			$tables = array();
			$this->modules[$module['name']] = $module;
			if (file_exists($LIB_DIR.'/Fuga/CMSBundle/Model/'.ucfirst($module['name']).'.php')) {
				$className = 'Fuga\\CMSBundle\\Model\\'.ucfirst($module['name']);
				$model = new $className();
				foreach ($model->tables as $table) {
					$table['is_system'] = true;
					$ret[$table['component'].'_'.$table['name']] = new Table($table);
				}
			}
		}
		$tables = $this->get('connection')->getItems('tables', "SELECT tt.*,cm.name as component FROM table_tables tt LEFT JOIN config_modules cm ON tt.module_id=cm.id WHERE publish=1 ORDER BY tt.sort");
		foreach ($tables as $table) {
			$ret[$table['component'].'_'.$table['name']] = new Table($table);
		}
		return $ret;
	}

	function getTable($name) {
		if (isset($this->tables[$name])) {
			return $this->tables[$name];
		} else {
			throw new \Exception('Таблица "'.$name.'" не найдена');
		}
	}

	function getTables($moduleName) {
		$tables = array();
		foreach ($this->tables as $table) {
			if ($table->moduleName == $moduleName)
				$tables[$table->tableName] = $table;
		}
		return $tables;
	}

	function getItem($class, $condition = 0, $sort = '', $select = '') {
		$ret = array();
		if (!isset($this->tables[$class])) {
			throw new \Exception('Table not found: '.$class);
		} else {
			$ret = $this->tables[$class]->getItem($condition, $sort, $select);
		}
		return $ret;

	}

	function getPrev($class, $id, $linkName = 'parent_id') {
		if ($a = $this->getItem($class, intval($id))) {
			$ret = $this->getPrev($class, $a[$linkName], $linkName);
			$ret[] = $a;
		} else {
			$ret = array();
		}
		return $ret;
	}

	function getItems($tableName, $query = '', $sort = '', $limit = false, $select = '', $detail = true) {
		$ret = array();
		if (!isset($this->tables[$tableName])) {
			throw new \Exception('Table not found: '.$tableName);
		} else {
			$a = array('where' => $query, 'order_by' => $sort, 'limit' => $limit);
			if (trim($select) != '') 
				$a['select'] = $select;
			$this->tables[$tableName]->select($a);
			$ret = $this->tables[$tableName]->getNextArrays($detail);
		}
		return $ret;
	}

	function getNativeItems($query) {
		$ret = array();
		if (!stristr($query, 'delete') && !stristr($query, 'truncate') && !stristr($query, 'update') && !stristr($query, 'insert') && !stristr($query, 'drop') && !stristr($query, 'alter')) {
			$items = $this->get('connection')->getItems('nquery', $query);
			foreach ($items as $item) {
				if (isset($item['id'])) {
					$res[$item['id']] = $item;
				} else {
					$res[] = $item;
				}
			}
			$this->get('connection')->freeResult('nquery');
		}
		return $ret;
	}

	function getNativeItem($query) {
		$ret = array();
		if (!stristr($query, 'delete') && !stristr($query, 'truncate') && !stristr($query, 'update') && !stristr($query, 'insert') && !stristr($query, 'drop') && !stristr($query, 'alter')) {
			$ret = $this->get('connection')->getItem('nquery', $query);
			$this->get('connection')->freeResult('nquery');
		}
		return $ret;
	}

	function getCount($class, $condition = '') {
		$ret = array();
		if (!isset($this->tables[$class])) {
			throw new \Exception('Table not found: '.$class);
		} else {
			$ret = $this->tables[$class]->getCount($condition);
		}
		return $ret;
	}

	function addItem($class, $fields, $values) {
		if (!isset($this->tables[$class])) {
			throw new \Exception('Class not found: '.$class);
		} else {
			return $this->tables[$class]->insert($fields, $values);
		}
	}

	function addGlobalItem($class) {
		if (!isset($this->tables[$class])) {
			throw new \Exception('Class not found: '.$class);
		} else {
			return $this->tables[$class]->insertGlobals();
		}
	}

	function updateItem($class, $query = 0, $condition) {
		if (!isset($this->tables[$class])) {
			throw new \Exception('Class not found: '.$class);
		} else {
			if (is_numeric($query)) {
				return $this->tables[$class]->update($condition.' WHERE id='.$query);
			} else {
				return $this->tables[$class]->update($condition.' WHERE '.$query);
			}
		}
	}

	function deleteItem($class, $query) {
		if (!isset($this->tables[$class])) {
			throw new \Exception('Class not found: '.$class);
		} else {
			$query .= $class == 'user_user' || $class == 'user_group' ? ' AND id<>1' : '';
			if ($ids = $this->delRel($class, $this->getItems($class, !empty($query) ? $query : '1<>1'))) 
				return $this->tables[$class]->delete('id IN ('.$ids.')');
			else
				return false;
		}

	}

	function delRel($class, $items = array()) {
		$ids0 = '';
		foreach ($items as $a) {
			if ($this->tables[$class]->params['is_system']) {
				foreach ($this->tables as $t) {
					if ($t->cname != 'user' && $t->cname != 'template' && $t->cname != 'page') {
						foreach ($t->fields as $f) {
							$ft = $t->createFieldType($f);
							if (stristr($ft->params['type'], 'select') && $ft->params['l_table'] == $class) {
								$this->deleteItem($t->getDBTableName(), $ft->getName().'='.$a['id']);
							}
							$ft->free();
						}
					}
				}
			}
			foreach ($this->tables[$class]->fields as $f) {
				$ft = $this->tables[$class]->createFieldType($f, $a);
				if ($ft->params['type'] == 'image' || $ft->params['type'] == 'file' || $ft->params['type'] == 'template') {
					@unlink($GLOBALS['PRJ_DIR'].$a[$ft->getName()]);
					if (isset($ft->params['sizes'])) {
						$path_parts = pathinfo($GLOBALS['PRJ_DIR'].$a[$ft->getName()]);
						$asizes = explode(',', $ft->params['sizes']);
						foreach ($asizes as $sz) {
							$asz = explode('|', $sz);
							if (sizeof($asz) == 2) {
								@unlink($path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename']);	
							}
						}
					}
				}
				$ft->free();
			}
			$ids0 .= ($ids0 ? ',' : '').$a['id'];
		}
		return $ids0;
	}

	function dublicateItem($class, $id = 0, $times = 1) {
		$entity = $this->getItem($class, $id);
		if (count($entity)) {
			for ($i = 1; $i <= $times; $i++)
				$this->tables[$class]->insertArray($entity);
			return $this->getItem($class, $this->get('connection')->getInsertID());
		} else {
			return array();
		}
	}

	function deleteClass($class, $simple = true) {
		if (!$simple) {
			$a = $this->getClass($class);
			if (count($a) == 0){ 
				throw new \Exception('Class not found: '.$class);
			}
			$this->get('connection')->execQuery($class.'deleteFields', 'DELETE FROM table_attributes WHERE table_id='.$a['id']);
			$this->get('connection')->execQuery($class.'deleteTable', "DELETE FROM table_tables WHERE name='$class'");
		}
		return $this->tables[$class]->drop();
	}

	function truncateClass($class) {
		return $this->tables[$class]->truncate();
	}


	function getClass($class) {
		$anames = explode('_',$class);
		$tname = str_replace('_', '', stristr($class, '_'));
		$component = $this->getModule($anames[0]);
		$a = $this->getItem('table_tables', "name='".$tname."' AND module_id=".$component['id']);
		if (sizeof($a) > 0)
			return $a;
		else 
			throw new \Exception('Class not exists: '.$class);
	}

	function getMethodInstance($controllerName, $actionName) {
		$query = "
			SELECT
				method.id,
				method.title,
				method.name,
				method.module_id,
				method.template_id,
				method.processor,
				method.template,
				module.name AS module_name
			FROM
				config_methods method
			LEFT JOIN 
				config_modules module ON method.module_id=module.id
			WHERE
				module.name='$controllerName' AND method.name='$actionName'
		";
		$method = $this->get('connection')->getItem('component.method', $query);
		if ( $method ) {
			return $method;
		} else {
			throw new \Exception('Страница не доступна: '.$controllerName.'.'.$actionName);
		}
	}

	function callMethodInstance($methodData, $paramsData) {
		global $PRJ_DIR, $LIB_DIR;
		if ($methodData['module_name'] != 'page') {
			if (file_exists($LIB_DIR.'/Fuga/PublicBundle/Controller/'.ucfirst($methodData['module_name']).'Controller.php')) {
				$className = '\\Fuga\\PublicBundle\\Controller\\'.ucfirst($methodData['module_name']).'Controller';
				$controller = new $className();
			} else{
				$controller = new \Fuga\CMSBundle\Controller\PublicController($methodData['module_name']);
			}

			$this->get('templating')->setParam('settings', $controller->params);
			$this->get('templating')->setParam('ref', '/'.$this->get('router')->getParam('node').'/');
		}
		if ($methodData['template'] && file_exists($PRJ_DIR.$methodData['template'])) {
			if ($methodData['module_name'] == 'page' && $methodData['name'] == 'index' && !count($paramsData)) {
				$paramsData[] = '/';
			}
			foreach ($paramsData as $key => $param) {
				$this->get('templating')->setParam('param'.$key, $param);
			}
			return $this->get('templating')->render($methodData['template']);
		} else {
			throw new \Exception('Method template error: '.$methodData['module_id_name'].'.'.$methodData['name'].'. <a href="/">Перейти на главную</a>');
		}
	}

	function callMethodByURL($url = '/') {
		try {
			$urlParts = $this->get('router')->parseURL($url);
			return $this->callMethodInstance($this->getMethodInstance($urlParts['node'], $urlParts['methodName']), $urlParts['params']);
		} catch (\Exception $e) {
			echo $this->get('util')->showError($e->getMessage());
		}	
	}

	function callMethod($controller, $methodName = 'index', $params = array()) {
		return $this->callMethodInstance($this->getMethodInstance($controller, $methodName), $params);
	}

	/*
	 * Формирует URL на основе параметров /$nodeName/$methodName.$param0.htm
	 */
	function href($node = '/', $methodName = 'index', $params = array()) {
		$url	= $this->get('router')->getParam('lang') != 'ru' ? '/'.$this->get('router')->getParam('lang') : '';
		$url .= '/'.$node.'/';
		if ($params){
			$url .= $methodName;
			$url .= '.'.implode('.', $params);
			$url .= '.htm';
		} else {
			if ($methodName != 'index') {
				$url .= $methodName.'.htm';
			}
		}
		return $url;
	}

	public function setVar($name, $value) {
		$this->templateVars[$name] = $value;
	}

	public function getVars() {
		return $this->templateVars;	
	}

	public function register($name, $service) {
		$this->services[$name] = $service;
		return $service;
	}

	public function get($name) {
		if ($name == 'filestorage' && !isset($this->services[$name])) {
			$this->services[$name] = new FileStorage();
		}
		if ($name == 'imagestorage' && !isset($this->services[$name])) {
			$this->services[$name] = new ImageStorageDecorator($this->get('filestorage'));
		}
		if ($name == 'paginator' && !isset($this->services[$name])) {
			$this->services[$name] = new Paginator();
		}
		if ($name == 'mailer' && !isset($this->services[$name])) {
			$this->services[$name] = new Mailer();
		}
		if ($name == 'scheduler' && !isset($this->services[$name])) {
			$this->services[$name] = new Scheduler();
		}
		if ($name == 'search' && !isset($this->services[$name])) {
			$this->services[$name] = new SearchEngine();
		}
		if ($name == 'cache' && !isset($this->services[$name])) {
			global $CACHE_DIR, $CACHE_TTL;
			$options = array(
			    'cacheDir' => $CACHE_DIR,
			    'lifeTime' => $CACHE_TTL,
			    'pearErrorMode' => CACHE_ERROR_DIE
			);
			$this->services[$name] = new Cache($options);
		}
		
		if (!isset($this->services[$name])) {
			throw new \Exception('Cлужба "'.$name.'" отсутствует');
		}
		return $this->services[$name];
	}
	
	public function getManager($name) {
		if (!isset($this->managers[$name])) {
			$className = '\\Fuga\\CMSBundle\\Model\\'.ucfirst($name).'Manager';
			$this->managers[$name] = new $className();
		}
		return $this->managers[$name];
	}

}
