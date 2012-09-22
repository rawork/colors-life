<?php

class RTTI {
	private $tables;
	private $units;
	private $tempvars = array();

	private $services = array();

	function __construct() {
		$this->tables = $this->getAllTables();
	}

	public function getComponent($sModuleName) {
		return empty($this->units[$sModuleName]) ? array() : $this->units[$sModuleName];
	}

	public function getComponentById($iModuleId) {
		foreach ($this->units as $aModule) {
			if ($iModuleId == $aModule['id']) {
				return $aModule;
			}
		}
		return null;
	}

	public function getComponents() {
		global $db, $LIB_DIR;
		$ret = array();
		if ($GLOBALS['auth']->isSuperuser()) {
			$ret = $this->units;
		} elseif (CUtils::_sessionVar('user') && $user = $GLOBALS['db']->getItem('users_users',
			"SELECT uu.*, ug.rules FROM users_users uu LEFT JOIN users_groups ug ON uu.group_id=ug.id WHERE uu.syslogin='".CUtils::_sessionVar('user')."'")) {
			$ret = $GLOBALS['db']->getItems('config_modules', ' SELECT id,ord, name, title, \'C\' AS ctype FROM config_modules WHERE id IN ('.$user['rules'].') ORDER BY ord, title');
			if ($user['is_admin']) {
				$q = "SELECT id,ord, name, title, 'A' AS ctype FROM system_modules
					UNION SELECT id,ord, name, title, 'S' AS ctype FROM system_services
					ORDER BY ord, title";
			} else {
				$q = "SELECT id,ord, name, title, 'A' AS ctype FROM system_modules WHERE name IN ('config', 'meta')
					UNION SELECT id,ord, name, title, 'S' AS ctype FROM system_services
					ORDER BY ord, title";
			}
			$avail_units = $db->getItems('modules', $q);
			foreach ($avail_units as $k => $u) {
				$tables = array();
				$ret[$u['name']] = $u;
			}
		}
		return $ret;
	}

	private function getAllTables() {
	global $db, $LIB_DIR;	
		$ret = array();
		$this->units = array();
		$q = "SELECT id,ord, name, title, 'C' AS ctype FROM config_modules
		UNION SELECT id,ord, name, title, 'A' AS ctype FROM system_modules
		UNION SELECT id,ord, name, title, 'S' AS ctype FROM system_services
		ORDER BY ord, title";
		$avail_units = $db->getItems('modules', $q);
		foreach ($avail_units as $k => $u) {
			$tables = array();
			$this->units[$u['name']] = $u;
			$mod_ids = '';
			if (inc_lib('db/DBStructure/'.$u['name'].'.php')) {
				include($LIB_DIR.'/db/DBStructure/'.$u['name'].'.php');
				$tables_array_name = $u['name'].'_tables';
				if (isset($$tables_array_name)) {
					$tables = $$tables_array_name;
					foreach ($tables as $k => $table) {
						$table['is_system'] = true;
						$ret[$table['component'].'_'.$table['name']] = new Table($table);
					}
				}
			}
		}
		$tables = $db->getItems('tables', "SELECT tt.*,cm.name as component FROM table_tables tt LEFT JOIN config_modules cm ON tt.module_id=cm.id WHERE publish='on' ORDER BY tt.ord");
		foreach ($tables as $table) {
			$ret[$table['component'].'_'.$table['name']] = new Table($table);
		}
		return $ret;
	}

	function getTable($name) {
		return $this->tables[$name];
	}

	function getTables($cname) {
		$tables = array();
		foreach ($this->tables as $k => $dbtable) {
			if ($dbtable->cname == $cname)
				$tables[$dbtable->name] = $dbtable;
		}
		return $tables;
	}

	function getItem($class, $condition = 0, $sort = '', $select = '') {
		$ret = array();
		if (!isset($this->tables[$class])) {
			throw new Exception('Class not found: '.$class);
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

	function getItems($class, $condition = '', $sort = '', $limit = false, $select = '', $detail = true) {
		$ret = array();
		if (!isset($this->tables[$class])) {
			throw new Exception('Class not found: '.$class);
		} else {
			$a = array('where' => $condition, 'order_by' => $sort, 'limit' => $limit);
			if (trim($select) != '') 
				$a['select'] = $select;
			$this->tables[$class]->select($a);
			$ret = $this->tables[$class]->getNextArrays($detail);
		}
		return $ret;
	}

	function getNativeItems($query) {
	global $db;
		$ret = array();
		if (!stristr($query, 'delete') && !stristr($query, 'truncate') && !stristr($query, 'update') && !stristr($query, 'insert') && !stristr($query, 'drop') && !stristr($query, 'alter')) {
			$ret = $db->getItems('nquery', $query);
			$db->freeResult('nquery');
		}
		return $ret;
	}

	function getNativeItem($query) {
	global $db;
		$ret = array();
		if (!stristr($query, 'delete') && !stristr($query, 'truncate') && !stristr($query, 'update') && !stristr($query, 'insert') && !stristr($query, 'drop') && !stristr($query, 'alter')) {
			$ret = $db->getItem('nquery', $query);
			$db->freeResult('nquery');
		}
		return $ret;
	}

	function getCount($class, $condition = '') {
		$ret = array();
		if (!isset($this->tables[$class])) {
			throw new Exception('Class not found: '.$class);
		} else {
			$ret = $this->tables[$class]->getCount($condition);
		}
		return $ret;
	}

	function addItem($class, $fields, $values) {
		if (!isset($this->tables[$class])) {
			throw new Exception('Class not found: '.$class);
		} else {
			return $this->tables[$class]->insert($fields, $values);
		}
	}

	function addGlobalItem($class) {
		if (!isset($this->tables[$class])) {
			throw new Exception('Class not found: '.$class);
		} else {
			return $this->tables[$class]->insertGlobals();
		}
	}

	function updateItem($class, $query = 0, $condition) {
		if (!isset($this->tables[$class])) {
			throw new Exception('Class not found: '.$class);
		} else {
			if (is_numeric($query)) {
				return $this->tables[$class]->update($condition.' WHERE id='.$query);
			} else {
				return $this->tables[$class]->update($condition.' WHERE '.$query);
			}
		}
	}

	function deleteItem($class, $q) {
	global $db;
		if (!isset($this->tables[$class])) {
			throw new Exception('Class not found: '.$class);
		} else {
			$q .= $class == 'users_users' || $class == 'users_groups' ? ' AND id<>1' : '';
			if ($ids = $this->delRel($class, $this->getItems($class, !empty($q) ? $q : '1<>1'))) 
				return $this->tables[$class]->delete('id IN ('.$ids.')');
			else
				return false;
		}

	}

	function delRel($class, $items = array()) {
		$ids0 = '';
		foreach ($items as $a) {
			if ($this->tables[$class]->props['is_system']) {
				foreach ($this->tables as $t) {
					if ($t->cname != 'users' && $t->cname != 'templates' && $t->cname != 'tree') {
						foreach ($t->fields as $f) {
							$ft = $t->createFieldType($f);
							if (stristr($ft->props['type'], 'select') && $ft->props['l_table'] == $class) {
								$this->deleteItem($t->getDBTableName(), $ft->getName().'='.$a['id']);
							}
							$ft->free();
						}
					}
				}
			}
			foreach ($this->tables[$class]->fields as $f) {
				$ft = $this->tables[$class]->createFieldType($f, $a);
				if ($ft->props['type'] == 'image' || $ft->props['type'] == 'file' || $ft->props['type'] == 'template') {
					@unlink($GLOBALS['PRJ_DIR'].$a[$ft->getName()]);
					if (isset($ft->props['sizes'])) {
						$path_parts = pathinfo($GLOBALS['PRJ_DIR'].$a[$ft->getName()]);
						$asizes = explode(',', $ft->props['sizes']);
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
	global $db;	
		$a = $this->getItem($class, $id);
		if (sizeof($a) > 0) {
			for ($i = 1; $i <= $times; $i++)
				$this->tables[$class]->insertArray($a);
			return $this->getItem($class, $db->getInsertID());
		} else {
			return array();
		}
	}

	function addClass($name, $seniorid = 0, $dbtablename = '', $is_virtual = false){
		global $db;
		$a = $this->getItem('classes', "dbtablename='$dbtablename'");
		if (!sizeof($a)) {
			if (!$is_virtual && !empty($dbtablename)) {
				$sql = 'CREATE TABLE '.$dbtablename.' (
					id int(11) NOT NULL auto_increment,
					classid int(11) NOT NULL default 0,
					seniorid int(11) NOT NULL default 0,
					name varchar(255) NULL,
					ord int(11) NOT NULL default 0,';

				$sql .=	'PRIMARY KEY (id)) ENGINE=MyISAM;';
			}
			return addItem('classes', 'classid,seniorid,name,dbtablename,sysname,is_vitual', '1,'.$seniorid.",'".$name."','".$dbtablename."','".$dbtablename.",'".($is_virtual ? 1 : 0)."'");
		} else {
			return 'Дублирование таблицы класса';
		}
	}

	function updateClass($class) {
		if (!isset($this->tables[$class])) {
			throw new Exception('Can not update class: '.$class);
		} else {
			$this->tables[$class]->alterNew();
		}
	}

	function deleteClass($class, $simple = true) {
		if (!$simple) {
			$a = $this->getClass($class);
			if (count($a) == 0){ 
				throw new Exception('Class not found: '.$class);
			}
			global $db;
			$db->execQuery($class.'deleteclass', 'DELETE FROM table_attributes WHERE seniorid='.$a['id']);
			$db->execQuery($class.'deleteclass', "DELETE FROM table_tables WHERE name='$class'");
		}
		return $this->tables[$class]->drop();
	}

	function truncateClass($class) {
		return $this->tables[$class]->truncate();
	}


	function getClass($class) {
		$anames = explode('_',$class);
		$tname = str_replace('_', '', stristr($class, '_'));
		$component = $this->getComponent($anames[0]);
		$a = $this->getItem('table_tables', "name='".$tname."' AND module_id=".$component['id']);
		if (sizeof($a) > 0)
			return $a;
		else 
			throw new Exception('Class not exists: '.$class);
	}

	function addMethod($class, $condition = ''){
		// Обязательна проверка есть ли класс
	}

	function updateMethod($class, $met, $condition = '') {

	}

	function deleteMethod($id = 0) {
	global $PRJ_DIR;
		$method = $this->getItem('config_methods', $id);
		if (sizeof($method)) {
			if (!empty($method['processor']) && file_exists($PRJ_DIR.$method['processor'])) {
				@unlink($PRJ_DIR.$method['processor']);
			}
			if (!empty($method['template']) && file_exists($PRJ_DIR.$method['template'])) {
				@unlink($PRJ_DIR.$method['template']);
			}
			return $this->deleteItem('config_methods', $id);
		} else {
			return false;
		}
	}

	function getMethodInstance($sComponentName, $sMethodName) {
		$sQuery = "
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
				module.name='$sComponentName' AND method.name='$sMethodName'
		";
		$aMethod = $GLOBALS['db']->getItem('component.method', $sQuery);
		if ( $aMethod ) {
			return $aMethod;
		} else {
			throw new Exception('Method call error: '.$sComponentName.'.'.$sMethodName.'. <a href="/">Mainpage</a>');
		}
	}

	function callMethodInstance($aMethod, $aParams) {
		global $PRJ_DIR;
		if ($aMethod['module_id_name'] != 'tree') {
			if (inc_u($aMethod['module_id_name'])) {
				$cCls = ucfirst($aMethod['module_id_name']).'Unit';
				$unit = new $cCls($GLOBALS['urlprops']);
			} else{
				inc_lib('components/Unit.php');
				$unit = new Unit($aMethod['module_id_name'], $GLOBALS['urlprops']);
			}
			$tpl = $unit->smarty;
			$tpl->assign('settings', $unit->dbparams);
			$tpl->assign('unit', $unit);
			$tpl->assign('ref', '/'.$GLOBALS['urlprops']['uri'].'/');
		} else {
			global $smarty;
			$tpl = $smarty;
		}
		/*if (!empty($methodInstance['processor']) && file_exists($PRJ_DIR.$methodInstance['processor'])) {
			require_once($PRJ_DIR.$methodInstance['processor']);
		}*/
		if ($aMethod['template'] && file_exists($PRJ_DIR.$aMethod['template'])) {
			if ($aMethod['module_id_name'] == 'tree' && $aMethod['name'] == 'index' && !count($aParams)) {
				$aParams[] = '/';
			}
			foreach ($aParams as $iKey => $mParam) {
				$tpl->assign('param'.$iKey, $mParam);
			}
			$tpl->assign('cname', $aMethod['module_id_name']);
			$tpl->assign('mname', $aMethod['name']);
			return $tpl->fetch($PRJ_DIR.$aMethod['template']);
		} else {
			throw new Exception('Method template error: '.$aMethod['module_id_name'].'.'.$aMethod['name'].'. <a href="/">Mainpage</a>');
		}
	}

	function callMethodByURL($sURL = '/') {
		try {
			$aURL = $this->parseURL($sURL);
			return $this->callMethodInstance($this->getMethodInstance($aURL['cname'], $aURL['mname']), $aURL['params']);
		} catch (Exception $e) {
			echo CUtils::showError($e->getMessage());
		}	
	}

	function callMethod($sComponentName = 'tree', $sMethodName = 'index', $aParams = array()) {
		try {
			return $this->callMethodInstance($this->getMethodInstance($sComponentName, $sMethodName), $aParams);
		} catch (Exception $e) {
			echo CUtils::showError($e->getMessage());
			exit;
		}
	}

	/*
		* Разбирает URL на части Раздел - Метод - Параметры
		*/
	function parseURL($sURL = '/') {
		if (preg_match('/^\/[a-z0-9_\-]+\/?[a-z0-9_\-\.]*(\.htm)?$/i', $sURL) || $sURL == '/') {
			$aURLProperty = array(
				'cname' => 'tree',
				'mname' => 'index',
				'params' => array()
			);
			$aURL = explode('/', $sURL);
			$iURLLength = sizeof($aURL);
			if ($iURLLength == 3)
				$aURLProperty['cname'] = $aURL[$iURLLength-2];
			if (preg_match('/^[a-z0-9_\.\-]+\.htm$/i', $aURL[$iURLLength-1])) {
				$aMethod = explode('.', str_replace('.htm', '', $aURL[$iURLLength-1]));
				$aURLProperty['mname'] = array_shift($aMethod);
				$aURLProperty['params'] = $aMethod;
				foreach ($aMethod as $iKey => $mParam) {
					$aURLProperty['params'][] = $mParam;
				}
			}
			return $aURLProperty;
		} else {
			return false;
		}
	}

	/*
		* Формирует URL на основе параметров /$sComponentName/$sMethodName.$mParam0.htm
		*
		*
		*/
	function href($sComponentName = 'tree', $sMethodName = 'index', $aParams = array()) {
		$sURL	= CUtils::_sessionVar('lang', false, 'ru') != 'ru' ? '/'.CUtils::_sessionVar('lang', false, 'ru') : '';
		if ($sComponentName == 'tree') {
			$sURL .= '/';
		} else {
			$sURL .= '/'.$sComponentName.'/';
		}
		if ($aParams){
			$sURL .= $sMethodName;
			foreach ($aParams as $mParam) {
				$sURL .= URL_PARAM_DELIMETER.$mParam;
			} 
			$sURL .= '.htm';
		} else {
			if ($sMethodName != 'index') {
				$sURL .= $sMethodName.'.htm';
			}
		}
		return $sURL;
	}

	public function setVar($name, $value) {
		$this->tempvars[$name] = $value;
	}

	public function getVars() {
		return $this->tempvars;	
	}

	public function register($name, $service) {
		$this->services[$name] = $service;
		return $service;
	}

	public function get($name) {
		if (!isset($this->services[$name])) {
			throw new Exeption('Service "'.$name.'" not exists');
		}
		return $this->services[$name];
	}

}
