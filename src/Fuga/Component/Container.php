<?php

namespace Fuga\Component;

use Fuga\CommonBundle\Security\SecurityHandler;

class Container 
{
	private $tables;
	private $modules;
	private $ownmodules;
	private $controllers = array();
	private $templateVars = array();
	private $services = array();
	private $managers = array();

	public function initialize() 
	{
		$this->tables = $this->getAllTables();
	}

	public function getModule($name) 
	{
		if (empty($this->modules[$name])) {
			throw new \Exception('Модуль '.$name.' отсутствует'); 
		}
		
		return  $this->modules[$name];
	}

	public function getModules() 
	{
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
			try {
				$className = 'Fuga\\CommonBundle\\Model\\'.ucfirst($module['name']);
				$model = new $className();
				foreach ($model->tables as $table) {
					$table['is_system'] = true;
					$ret[$table['component'].'_'.$table['name']] = new DB\Table($table);
				}
			} catch (Exception\AutoloadException $e) {
				
			}
		}
		$tables = $this->get('connection')->getItems('tables', "SELECT tt.*,cm.name as component FROM table_tables tt LEFT JOIN config_modules cm ON tt.module_id=cm.id WHERE publish=1 ORDER BY tt.sort");
		foreach ($tables as $table) {
			$ret[$table['component'].'_'.$table['name']] = new DB\Table($table);
		}
		return $ret;
	}

	public function getTable($name) {
		if (isset($this->tables[$name])) {
			return $this->tables[$name];
		} else {
			throw new \Exception('Таблица "'.$name.'" не найдена');
		}
	}

	public function getTables($moduleName) {
		$tables = array();
		foreach ($this->tables as $table) {
			if ($table->moduleName == $moduleName)
				$tables[$table->tableName] = $table;
		}
		return $tables;
	}
	
	public function getPrev($table, $id, $linkName = 'parent_id') {
		$ret = null;
		if ($node = $this->getItem($table, intval($id))) {
			$ret = $this->getPrev($table, $node[$linkName], $linkName);
			$ret[] = $node;
		}
		return $ret;
	}

	public function getItem($table, $criteria = 0, $sort = null, $select = null) {
		return $this->getTable($table)->getItem($criteria, $sort, $select);
	}

	public function getItems($table, $criteria = '', $sort = '', $limit = null, $select = '', $detailed = true) {
		$ret = null;
		if (!isset($this->tables[$table])) {
			throw new \Exception('Table not found: '.$table);
		} else {
			$options = array('where' => $criteria, 'order_by' => $sort, 'limit' => $limit);
			if (trim($select) != '') 
				$options['select'] = $select;
			$this->getTable($table)->select($options);
			$ret = $this->getTable($table)->getNextArrays($detailed);
		}
		return $ret;
	}

	public function getItemsRaw($query) {
		$ret = array();
		if (!preg_match('/(delete|truncate|update|insert|drop|alter)+/i', $query)) {
			$items = $this->get('connection')->getItems('raw', $query);
			foreach ($items as $item) {
				if (isset($item['id'])) {
					$ret[$item['id']] = $item;
				} else {
					$ret[] = $item;
				}
			}
		}
		return $ret;
	}

	public function getItemRaw($query) {
		$ret = null;
		if (!preg_match('/(delete|truncate|update|insert|drop|alter)+/i', $query)) {
			$ret = $this->get('connection')->getItem('raw', $query);
		}
		return $ret;
	}

	public function getCount($table, $criteria = '') {
		return $this->getTable($table)->getCount($criteria);
	}

	public function addItem($class, $fields, $values) {
		if (!isset($this->tables[$class])) {
			throw new \Exception('Class not found: '.$class);
		} else {
			return $this->tables[$class]->insert($fields, $values);
		}
	}

	public function addGlobalItem($class) {
		if (!isset($this->tables[$class])) {
			throw new \Exception('Class not found: '.$class);
		} else {
			return $this->tables[$class]->insertGlobals();
		}
	}

	public function updateItem($class, $query = 0, $condition) {
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

	public function deleteItem($class, $query) {
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

	public function delRel($class, $items = array()) {
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

	public function dublicateItem($class, $id = 0, $times = 1) {
		$entity = $this->getItem($class, $id);
		if (count($entity)) {
			for ($i = 1; $i <= $times; $i++)
				$this->tables[$class]->insertArray($entity);
			return $this->getItem($class, $this->get('connection')->getInsertID());
		} else {
			return array();
		}
	}

	public function deleteClass($table, $complex = false) {
		if ($complex) {
			$tableObj = $this->getTable($table);
			$this->get('connection')->execQuery('delete_fields', 'DELETE FROM table_attributes WHERE table_id='.$tableObj->id);
			$this->get('connection')->execQuery('delete_table', "DELETE FROM table_tables WHERE name='$table'");
		}
		return $this->getTable($table)->drop();
	}

	public function truncateClass($table) {
		return $this->getTable($table)->truncate();
	}
	
	public function getControllerClass($path) {
		list($vendor, $bundle, $name) = explode(':', $path);
		return $vendor.'\\'.$bundle.'Bundle\\Controller\\'.ucfirst($name).'Controller';
	}
	
	public function createController($path) {
		if (!isset($this->controllers[$path])) {
			$className = $this->getControllerClass($path);
			$this->controllers[$path] = new $className();
		}
		return $this->controllers[$path];
	}

	public function callAction($path, $params = array()) {
		list($vendor, $bundle, $name, $action) = explode(':', $path);
		$obj = new \ReflectionClass($this->getControllerClass($path));
		$action .= 'Action'; 	
		if (!$obj->hasMethod($action)) {
			return $this->get('util')->showError('Несуществующая ссылка '.$path);
		}
		return $obj->getMethod($action)->invoke($this->createController($path), $params);	
	}

	public function href($node = '/', $action = 'index', $params = array()) {
		if ($node == '/') {
			return $node;
		}
		$path = array('');
		if ('ru' != $this->get('router')->getParam('lang')) {
			$path[] = $this->get('router')->getParam('lang');
		}
		$path[] = $node;
		if ($action != 'index') {
			$path[] = $action;
		}
		if (count($params)){
			$path = array_merge($path, $params);
		}
		return implode('/', $path);
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
		if (!isset($this->services[$name])) {
			switch ($name) {
				case 'log':
					$this->services[$name] = new Log\Log();
					break;
				case 'util':
					$this->services[$name] = new Util();
					break;
				case 'templating':
					$this->services[$name] = new Templating\SmartyTemplating();
					break;
				case 'connection':
					try {
						$className = 'Fuga\\Component\\DB\\Connector\\'.ucfirst($GLOBALS['DB_TYPE']).'Connector';
						$this->services[$name] = new $className($GLOBALS['DB_HOST'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS'], $GLOBALS['DB_BASE']);
					} catch (\Exception $e) {
						throw new \Exception('DB connection type error (DB_TYPE). Possible value: mysql,mysqli. Check DB connection parameters');
					}
					break;
				case 'filestorage':
					$this->services[$name] = new Storage\FileStorage();
					break;
				case 'imagestorage':
					$this->services[$name] = new Storage\ImageStorageDecorator($this->get('filestorage'));
					break;
				case 'paginator':
					$this->services[$name] = new Paginator();
					break;
				case 'mailer':
					$this->services[$name] = new Mailer\Mailer();
					break;
				case 'scheduler':
					$this->services[$name] = new Scheduler\Scheduler();
					break;
				case 'search':
					$this->services[$name] = new Search\SearchEngine($this);
					break;
				case 'router':
					$this->services[$name] = new Router($this);
					break;
				case 'security':
					$this->services[$name] = new SecurityHandler($this);
					break;
				case 'cache':
					global $CACHE_DIR, $CACHE_TTL;
					$options = array(
						'cacheDir' => $CACHE_DIR,
						'lifeTime' => $CACHE_TTL,
						'pearErrorMode' => CACHE_ERROR_DIE
					);
					$this->services[$name] = new Cache\Cache($options);
					break;
			}	
		}
		if (!isset($this->services[$name])) {
			throw new \Exception('Cлужба "'.$name.'" отсутствует');
		}
		
		return $this->services[$name];
	}
	
	public function getManager($path) {
		if (!isset($this->managers[$path])) {
			list($vendor, $bundle, $name) = explode(':', $path);
			$className = $vendor.'\\'.$bundle.'Bundle\\Model\\'.ucfirst($name).'Manager';
			$this->managers[$path] = new $className();
		}

		return $this->managers[$path];
	}
	
	public function isXmlHttpRequest() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH'];
	}

}
