<?php

namespace Common;

class Container {
	private $tables;
	private $modules;
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
		global $security;
		$ret = array();
		if ($security->isSuperuser()) {
			$ret = $this->modules;
		} elseif ($user = $security->getCurrentUser()) {
			$ret = $this->get('connection')->getItems('config_modules', ' SELECT id, ord, name, title, \'C\' AS ctype FROM config_modules WHERE id IN ('.$user['rules'].') ORDER BY ord, title');
			if ($user['is_admin']) {
				$query = "SELECT id, ord, name, title, 'A' AS ctype FROM system_modules
					UNION SELECT id, ord, name, title, 'S' AS ctype FROM system_services
					ORDER BY ord, title";
			} else {
				$query = "SELECT id, ord, name, title, 'A' AS ctype FROM system_modules WHERE name IN ('config', 'meta')
					UNION SELECT id, ord, name, title, 'S' AS ctype FROM system_services
					ORDER BY ord, title";
			}
			$modules = $this->get('connection')->getItems('modules', $query);
			foreach ($modules as $module) {
				$ret[$module['name']] = $module;
			}
		}
		return $ret;
	}

	private function getAllTables() {
		global $LIB_DIR;	
		$ret = array();
		$this->modules = array();
		$query = "SELECT id, ord, name, title, 'C' AS ctype FROM config_modules
		UNION SELECT id, ord, name, title, 'A' AS ctype FROM system_modules
		UNION SELECT id, ord, name, title, 'S' AS ctype FROM system_services
		ORDER BY ord, title";
		$modules = $this->get('connection')->getItems('modules', $query);
		foreach ($modules as $module) {
			$tables = array();
			$this->modules[$module['name']] = $module;
			if (file_exists($LIB_DIR.'/Model/'.ucfirst($module['name']).'.php')) {
				$className = '\\Model\\'.ucfirst($module['name']);
				$model = new $className();
				foreach ($model->tables as $table) {
					$table['is_system'] = true;
					$ret[$table['component'].'_'.$table['name']] = new \DB\Table($table);
				}
			}
		}
		$tables = $this->get('connection')->getItems('tables', "SELECT tt.*,cm.name as component FROM table_tables tt LEFT JOIN config_modules cm ON tt.module_id=cm.id WHERE publish='on' ORDER BY tt.ord");
		foreach ($tables as $table) {
			$ret[$table['component'].'_'.$table['name']] = new \DB\Table($table);
		}
		return $ret;
	}

	function getTable($name) {
		return $this->tables[$name];
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
			throw new \Exception('Class not found: '.$class);
		} else {
			$ret = $this->tables[$class]->getItem($condition, $sort, $select);
		}
		return $ret;

	}

	function getPrev($class, $id, $linkName = 'p_id') {
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
			throw new \Exception('Class not found: '.$tableName);
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
			$ret = $this->get('connection')->getItems('nquery', $query);
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
			throw new \Exception('Class not found: '.$class);
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
			$query .= $class == 'users_users' || $class == 'users_groups' ? ' AND id<>1' : '';
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
					if ($t->cname != 'users' && $t->cname != 'templates' && $t->cname != 'tree') {
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
				module.name AS module_id_name
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
			throw new \Exception('Страница не доступна: '.$controllerName.'.'.$actionName.'. <a href="/">Перейти на главную</a>');
		}
	}

	function callMethodInstance($methodData, $paramsData) {
		global $PRJ_DIR, $LIB_DIR;
		if ($methodData['module_id_name'] != 'tree') {
			if (file_exists($LIB_DIR.'/Controller/'.ucfirst($methodData['module_id_name']).'.php')) {
				$className = '\\Controller\\'.ucfirst($methodData['module_id_name']).'Controller';
				$controller = new $className();
			} else{
				$controller = new \Controller\Controller($methodData['module_id_name']);
			}

			$this->get('smarty')->assign('settings', $controller->params);
			$this->get('smarty')->assign('unit', $controller);
			$this->get('smarty')->assign('ref', '/'.$this->get('router')->getParam('nodeName').'/');
		}
		if ($methodData['template'] && file_exists($PRJ_DIR.$methodData['template'])) {
			if ($methodData['module_id_name'] == 'tree' && $methodData['name'] == 'index' && !count($paramsData)) {
				$paramsData[] = '/';
			}
			foreach ($paramsData as $key => $param) {
				$this->get('smarty')->assign('param'.$key, $param);
			}
			$this->get('smarty')->assign('controller', $methodData['module_id_name']);
			$this->get('smarty')->assign('methodName', $methodData['name']);
			$this->get('smarty')->assign('node', $this->get('router')->getParam('node'));
			return $this->get('smarty')->fetch($PRJ_DIR.$methodData['template']);
		} else {
			throw new \Exception('Method template error: '.$methodData['module_id_name'].'.'.$methodData['name'].'. <a href="/">Перейти на главную</a>');
		}
	}

	function callMethodByURL($url = '/') {
		try {
			$urlParts = $this->get('router')->parseURL($url);
			return $this->callMethodInstance($this->getMethodInstance($urlParts['controller'], $urlParts['methodName']), $urlParts['params']);
		} catch (\Exception $e) {
			echo $this->get('util')->showError($e->getMessage());
		}	
	}

	function callMethod($controller = 'tree', $methodName = 'index', $params = array()) {
		try {
			return $this->callMethodInstance($this->getMethodInstance($controller, $methodName), $params);
		} catch (\Exception $e) {
			echo $this->get('util')->showError($e->getMessage());
			exit;
		}
	}

	/*
	 * Формирует URL на основе параметров /$sComponentName/$sMethodName.$mParam0.htm
	 */
	function href($controller = 'tree', $methodName = 'index', $params = array()) {
		$url	= $this->get('router')->getParam('lang') != 'ru' ? '/'.$this->get('router')->getParam('lang') : '';
		if ($controller == 'tree') {
			$url .= '/';
		} else {
			$url .= '/'.$controller.'/';
		}
		if ($params){
			$url .= $methodName;
			foreach ($params as $param) {
				$url .= URL_PARAM_DELIMETER.$param;
			} 
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
		if ($name == 'paginator' && !isset($this->services[$name])) {
			$this->services[$name] = new Paginator();
		}
		if (!isset($this->services[$name])) {
			throw new \Exception('Service "'.$name.'" not exists');
		}
		return $this->services[$name];
	}
	
	public function getManager($name) {
		if (!isset($this->managers[$name])) {
			$className = '\\Model\\'.ucfirst().'Manager';
			$this->managers[$name] = new $className();
		}
		return $this->managers[$name];
	}

}
