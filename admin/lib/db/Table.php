<?php
	
function fillValue(&$sValue) {
	$sValue = "'".$sValue."'";
}

inc_lib('db/Type/checkbox.php');
inc_lib('db/Type/color.php');
inc_lib('db/Type/currency.php');
inc_lib('db/Type/date.php');
inc_lib('db/Type/datetime.php');
inc_lib('db/Type/enum.php');
inc_lib('db/Type/file.php');
inc_lib('db/Type/gallery.php');
inc_lib('db/Type/html.php');
inc_lib('db/Type/image.php');
inc_lib('db/Type/number.php');
inc_lib('db/Type/password.php');
inc_lib('db/Type/select.php');
inc_lib('db/Type/select_list.php');
inc_lib('db/Type/select_tree.php');
inc_lib('db/Type/string.php');
inc_lib('db/Type/template.php');
inc_lib('db/Type/text.php');

class Table {
	public $name;
	public $cname;
	public $Id;
	public $cls;
	public $title;
	public $fields;
	public $props;

	private $tableNameDB;
	private $moduleName;
	private $tableName;

	private $_aFieldTypes;

	function __construct($table) {
		$this->name = $table['name'];
		$this->cname = $table['component'];
		$this->title = $table['title'];
		$this->cls = $this->cname.'_'.$this->name;

		$this->tableName		= $table['name'];
		$this->moduleName		= $table['component'];
		$this->setDBTableName($this->moduleName, $this->tableName);

		$this->id = isset($table['id']) ? $table['id'] : 0;
		$this->fields = array();

		$table['is_lang']		= !empty($table['is_lang']);
		$table['is_sort']		= !empty($table['is_sort']);
		$table['is_publish']	= !empty($table['is_publish']);
		$table['noinsert']		= !empty($table['no_insert']);
		$table['noupdate']		= !empty($table['no_update']);
		$table['nodelete']		= !empty($table['no_delete']);
		$table['is_system']		= !empty($table['is_system']);
		$table['is_search']		= !empty($table['is_search']);
		$table['multifile']		= !empty($table['multifile']);
		$table['show_credate']	= !empty($table['show_credate']);
		$table['order_by']		= !empty($table['order_by']) ? $table['order_by'] : '';
		$table['rpp']			= !empty($table['rpp']) ? $table['rpp'] : 25;

		$this->props = $table;
		$this->setTableFields();
	}

	/**
		* Set fields propeties
		*/
	function readConfig() {
		if (!empty($this->props['fieldset']) && is_array($this->props['fieldset'])) {
			$this->fields = $this->props['fieldset'];
		} else {
			throw new Exception('Table config file format error: '.$this->tableName);
		}
	}

	/*
		* Read Table config from DB
		*/
	function readDBConfig() {
		global $db;
		$fields = $db->getItems('table_fields', "SELECT * FROM table_attributes WHERE publish='on' AND table_id=".$this->id." ORDER by ord");
		if (sizeof($fields) > 0) {
			foreach ($fields as $f) {
				$f['group_update'] = $f['group_update'] == 'on';
				$f['readonly'] = $f['readonly'] == 'on';
				$f['search'] = $f['search'] == 'on';
				if (!empty($f['params'])) {
					$aparams = explode(';', trim($f['params']));
					foreach ($aparams as $ap) {
						if (!empty($ap) && stristr($ap, ':')) {
							$vals = explode(':', $ap);
							$f[$vals[0]] = str_replace("`", "'", $vals[1]);
						}
					}
				}
				$this->fields[$f['name']] = $f;
			}

			foreach ($this->fields as $k => $f) {

			}
		} else {
			throw new Exception('No fields in table: '.$this->getDBTableName());
		}
	}
	/*** standart SQL operations */
	function createFieldType(&$aProperties, $aDBEntity = null) {
//			if (!isset($this->_aFieldTypes[$aProperties['type']])) {
//				$fullName =	 $aProperties['type'].'FieldType';
//				$this->_aFieldTypes[$aProperties['type']] = new $fullName($aProperties, $aDBEntity);
//			} else {
//				$this->_aFieldTypes[$aProperties['type']]->setProperties($aProperties);
//				$this->_aFieldTypes[$aProperties['type']]->setDBEntity($aDBEntity);
//			}
//			return $this->_aFieldTypes[$aProperties['type']];
		$fullName =	 $aProperties['type'].'FieldType';
		return new $fullName($aProperties, $aDBEntity);
	}

	function getSQLFieldsList() {
		$ret = '';
		foreach ($this->fields as $aField) {
			$ret .= ($ret ? ',' : '').$aField['name'];
		}
		return $ret;
	}

	function insertGlobals() {
		$sQuery = '';
		foreach ($this->fields as $aField) {
			if ($aField['type'] != 'listbox') {
				$ft = $this->createFieldType($aField);
				if ($aField['name'] == 'credate') {
					$sQuery .= ($sQuery ? ', ' : ' ').'NOW()';
				} elseif (stristr($aField['type'], 'date')) {
					$sQuery .= ($sQuery ? ', ' : '').$ft->getSQLValue();
				} elseif ($aField['name'] == 'lang') {
					$sQuery .= ($sQuery ? ", '" : "'").CUtils::_sessionVar('lang', false, 'ru')."'";
				} else {
					$sQuery .= ($sQuery ? ", '" : "'").$ft->getSQLValue()."'";
				}
			}
		}
		return $this->insert($this->getSQLFieldsList(), $sQuery);
	}
	function updateGlobals() {
	global $db;
		$iEntityId = CUtils::_postVar('id', true);
		$aEntity = $this->getItem($iEntityId);
		$iCategoryId = 0;
		$sql = '';
		foreach ($this->fields as $aField) {
			if ($aField['type'] != 'listbox') {
				$ft = $this->createFieldType($aField, $aEntity);
				if ($aField['name'] == 'c_id')
					$iCategoryId = $ft->getValue();
				if ($this->getDBTableName() == 'users_users' && $aField['name'] == 'login' && $iEntityId == 1) {
					$sql .= ($sql ? ', ' : '').$ft->getName()."='admin'";
				} elseif ($aField['name'] == 'change_date') {
					$sql .= ($sql ? ', ' : '').$ft->getName().'= NOW()';
				} elseif (empty($aField['readonly'])) {
					if (stristr($aField['type'], 'date') || $aField['type'] == 'select' || $aField['type'] == 'select_tree' || $aField['type'] == 'number' || $aField['type'] == 'currency')
						$sql .= ($sql ? ', ' : '').$ft->getName().'='.$ft->getSQLValue(); 
					else
						$sql .= ($sql ? ', ' : '').$ft->getName()."='".$ft->getSQLValue()."'";
				}
			}	
		}
		// Обновление значений дополнительных свойств
		if ($this->getDBTableName() == 'catalog_stuff1') {
			$aCategory = $GLOBALS['db']->getItem('get_cat', 'SELECT id, name,filters from catalog_categories where id='.$iCategoryId);
			if ($aCategory) {
				$aFeatures = $GLOBALS['db']->getItems('get_features', 'SELECT * from catalog_features where id IN('.$aCategory['filters'].')');
				foreach($aFeatures as $aFeature) {
					$iFeatureId = $aFeature['id'];
					$mFilterValue = CUtils::_postVar('filter_'.$iFeatureId, true);
					$feature_value = $GLOBALS['db']->getItem('get_value', 'SELECT id, feature_value_id FROM catalog_features_values WHERE feature_id='.$iFeatureId.' AND stuff_id='.$iEntityId);
					if ($feature_value){
						$values = "feature_value_id=".$mFilterValue;
						$GLOBALS['db']->execQuery('upd_value', "UPDATE catalog_features_values set ".$values." where id=".$feature_value['id']);
					} else { 
						$values = $mFilterValue.','.$iFeatureId.','.$iEntityId;
						$GLOBALS['db']->execQuery('add_value', "INSERT INTO catalog_features_values (feature_value_id,feature_id,stuff_id) VALUES (".$values.")");
					}
				}
			}
		}
		$where = '';
		if (($this->getDBTableName() == 'users_users' || $this->getDBTableName() == 'users_groups') && !$GLOBALS['auth']->isSuperuser())
			$where = ' AND id<>1';

		return $this->update($sql.' WHERE id='.$iEntityId.$where);
	}

	function group_update() {
	global $db;
		$recs = $this->getArraysWhere('id IN('.CUtils::_postVar('ids').')');
		$query = '';
		foreach ($recs as $a) {
			$values = '';
			foreach ($this->fields as $f) {
				if ($f['type'] != 'listbox') {
					$ft = $this->createFieldType($f, $a);
					if ($f['type'] == 'checkbox' && !isset($_POST[$ft->getName().$a['id']])) {
						$values .= ($values ? ',' : '').$ft->getName()."=''";	
					} elseif (isset($_POST[$ft->getName().$a['id']]) || isset($_FILES[$ft->getName().$a['id']]))
						if (stristr($f['type'], 'date') || $f['type'] == 'select' || $f['type'] == 'select_tree' || $f['type'] == 'number' || $f['type'] == 'currency')
							$values .= ($values ? ', ' : '').$ft->getName().'='.$ft->getGroupSQLValue(); 
						else
							$values .= ($values ? ', ' : '').$ft->getName()."='".$ft->getGroupSQLValue()."'";
				}	
			}
			if ($values)
				$query .= 'UPDATE '.$this->getDBTableName().' SET '.$values.' WHERE id='.$a['id'].';#|#|#';
		}
		//var_dump($query);
		//die();
		return $db->execQuery($this->getDBTableName().'_update', $query);
	}

	/* special operations */
	function create() {
	global $db;
		$query = 'CREATE TABLE '.$this->getDBTableName();
		$query .= '( id int(11) NOT NULL auto_increment, ';
		$query .= 'classid int(11) NOT NULL default 0, ';
		$query .= 'seniorid int(11) NOT NULL default 0, ';
		foreach ($this->fields as $aField) {
			$oFieldType = $this->createFieldType($aField);
			if ($oFieldType->getSQL()) {
				$query .= $oFieldType->getSQL().', ';
			}
		}
		$query .= ' PRIMARY KEY(id)) TYPE = myisam';
		return $db->execQuery($this->getDBTableName(), $query);
	}
	function drop() {
	global $db;
		return $db->execQuery($this->getDBTableName().'_droptable', 'DROP TABLE '.$this->getDBTableName());
	}
	function truncate() {
		global $db;
		return $db->execQuery($this->getDBTableName().'truncateclass', 'TRUNCATE TABLE '.$this->getDBTableName());
	}
	function alter() {
	global $db, $DB_TYPE;
		$ret = true;
		$sql = '';
		$fields = $db->getFieldsList($this->getDBTableName());
		if (!is_array($fields))
			return false;
		foreach ($this->fields as $f) {
			if ($f['type'] != 'listbox' && $f['type'] != 'gallery') {
				$ft = $this->createFieldType($f);
				if (!isset($fields[$f['name']]))
					$sql .= 'ALTER TABLE '.$this->getDBTableName().' ADD COLUMN '.$ft->getSQL().';#|#|#';
				if (isset($fields[$f['name']]) && $fields[$f['name']]['Type'] != $db->getFieldRealType($f['type']))
					$sql .= 'ALTER TABLE '.$this->getDBTableName().' CHANGE '.$f['name'].' '.$ft->getSQL().';#|#|#';
			// Доработать изменение типа str to int
			// Доработать изменение имени по сравнении старого имени
			}
		}
		foreach ($fields as $k => $f) {
			if ($k == 'id' || $k == 'classid' || $k == 'seniorid' || $f['Type'] == 'listbox' || $f['Type'] == 'gallery')
				continue;
			if (!isset($this->fields[$k]))
				$sql .= 'ALTER TABLE '.$this->getDBTableName().' DROP COLUMN '.$f['Field'].';#|#|#';
		}
		//var_dump($sql);
		//die();
		if ($this->props['is_search']) {
			$indexes = $db->getItems('get_indexes', 'SHOW INDEX FROM '.$this->getDBTableName());
			$fulltext_exists = false;
			foreach ($indexes as $ind) {
				if ($ind['Key_name'] == 'search' && $ind['Index_type'] == 'FULLTEXT') {
					$fulltext_exists = true;
					break;
				}
			}
			$search_fields = '';
			foreach ($this->fields as $f) {
				if (($f['type'] == 'html' || $f['type'] == 'text' || $f['type'] == 'string') && $f['name'] != 'lang' && $f['name'] != 'ord') {
					$search_fields .= ($search_fields ? ',' : '').$f['name'];
				}
			}
			if ($search_fields) {
//					if ($fulltext_exists)
//						$sql .= 'ALTER TABLE '.$this->getDBTableName().' DROP INDEX search;#|#|#';
//					$sql .= 'ALTER TABLE '.$this->getDBTableName().' ADD FULLTEXT INDEX search ('.$search_fields.');#|#|#';
			} elseif ($fulltext_exists) {
				$sql .= 'ALTER TABLE '.$this->getDBTableName().' DROP INDEX search;#|#|#';
			}
		}
		//var_dump($sql);
		//die();
		if ($sql) {
			return $db->execQuery('alter_table_'.$this->getDBTableName(), $sql);
		}
		return $ret;
	}

	/*** More */
	function getSeachSQL() {
		$ret = '';
		if (!empty($_REQUEST['search_filter_id'])) {
			$ret .= ' id = '.intval($_REQUEST['search_filter_id']);
		}
		foreach ($this->fields as $f) {
			if ($f['type'] != 'listbox') {
				$ft = $this->createFieldType($f);
				if ($filter = $ft->getSearchSQL()) {
					$ret .= $ret ? ' AND '.$filter : $filter;
				}
			}
		}
		return $ret;
	}

	function getSeachURL() {
		$ret = '';
		if (!empty($_REQUEST['search_filter_id'])) {
			$ret .= 'search_filter_id='.intval($_REQUEST['search_filter_id']);
		}
		foreach ($this->fields as $f) {
			if ($f['type'] != 'listbox') {
				$ft = $this->createFieldType($f);
				if ($filter = $ft->getSearchURL()) {
					$ret .= $ret ? '&'.$filter : $filter;
				}
			}
		}
		return $ret;
	}
	/*** easy SQL */
	function insert($names, $values) {
	global $db;
		//var_dump('INSERT INTO '.$this->getDBTableName().'('.$names.',classid,seniorid) VALUES('.$values.','.$this->id.',0)');
		//die();
		return $db->execQuery($this->getDBTableName(), 'INSERT INTO '.$this->getDBTableName().'('.$names.',classid,seniorid) VALUES('.$values.','.$this->id.',0)');
	}
	function insertArray(&$a) {
	global $db;	
		$names = $values = '';
		foreach ($a as $k => $v) {
			foreach ($this->fields as $f) {
				if ($k && $f['name'] == $k) {
					$names = ($names ? $names.', ' : '').$f['name'];
					if ($a[$f['name']] && ($f['type'] == 'image' || $f['type'] == 'file' || $f['type'] == 'template')) {
						$ft = $this->createFieldType($f);
						$dest = CUtils::getNextFileName($v);
						@copy($GLOBALS['PRJ_DIR'].$v,$GLOBALS['PRJ_DIR'].$dest);
						$values = ($values ? $values.', ' : '')."'".$dest."'";

						if ($f['type'] == 'image' && isset($ft->props['sizes'])) {
							$path_parts = pathinfo($a[$ft->getName()]);
							$asizes = explode(',', $ft->props['sizes']);
							foreach ($asizes as $sz) {
								$asz = explode('|', $sz);
								if (sizeof($asz) == 2) {
									$v = $path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename'];
									$dest = CUtils::getNextFileName($v);
									@copy($GLOBALS['PRJ_DIR'].$v,$GLOBALS['PRJ_DIR'].$dest);
								}
							}
						}
					} else {
						$values = ($values ? $values.', ' : '')."'".$v."'";	
					}
					break;
				}
			}
		}
		$ret = $this->insert($names, $values);
		$iLastInsertID = $db->getInsertID();
		if ($this->props['multifile']) {
			$sql = "SELECT * FROM system_files WHERE record_id={$a['id']} AND table_name='".$this->getDBTableName()."'";
			$photos = $db->getItems('get_system_files', $sql);
			foreach ($photos as $photo) {
				$filepath = $photo['file'];
				$dest = CUtils::getNextFileName($filepath);
				@copy($GLOBALS['PRJ_DIR'].$filepath,$GLOBALS['PRJ_DIR'].$dest);
				unset($photo['id']);
				$photo['file'] 		= $dest;
				$photo['credate'] 	= date("Y-m-d H:i:s");
				$photo['record_id'] = $iLastInsertID;
				$names = implode(',', array_keys($photo));
				array_walk($photo, "fillValue");
				$values = implode(',', $photo);
				$sql = "INSERT INTO system_files ($names) VALUES ($values)";
				$db->execQuery($this->getDBTableName(), $sql);
			}
		}
		//exit;
		return $ret;
	}

	function update($sUpdate) {
	global $db;
		$sDBTableName = $this->getDBTableName();
		$sQuery = "
			UPDATE
				$sDBTableName
			SET
				$sUpdate
		";
		//die($sQuery);
		return $db->execQuery($sDBTableName, $sQuery);
	}
	function delete($sQuery) {
	global $db;
		return $db->execQuery($this->getDBTableName().'_deleterecords', 'DELETE FROM '.$this->getDBTableName().' WHERE '.$sQuery);
	}
	function select($a = null) {
	global $db;
		if ($this->props['is_lang']) {
			$a['where'] = empty($a['where']) ? "lang='".CUtils::_sessionVar('lang', false, 'ru')."'" : $a['where']." AND lang='".CUtils::_sessionVar('lang', false, 'ru')."'";
		}
		return $db->execQuery($this->getDBTableName(),
			'SELECT '.(!empty($a['select']) ? $a['select'] : '*').' FROM '.
			(!empty($a['from']) ? $a['from'] : $this->getDBTableName()).
			(!empty($a['where']) ? ' WHERE '.$a['where'] : '').
			(!empty($a['order_by']) ? ' ORDER BY '.$a['order_by'] : (!empty($this->props['order_by']) ? ' ORDER BY '.$this->props['order_by'] : ' ORDER BY id')).
			(!empty($a['limit']) ? ' LIMIT '.$a['limit'] : '')
		);
	}
	function selectWhere($where, $sort = '', $select = '') {
		return $this->select(array('where' => $where, 'select' => $select, 'order_by' => $sort));
	}
	function getNextArray($bDetailed = true) {
	global $db;
		$ret = $db->getNextArray($this->getDBTableName());
		if ($bDetailed) {
		foreach ($this->fields as $f) {
			$ft = $this->createFieldType($f);
			if (stristr($ft->props['type'], 'select')) {
				if (!empty($ret[$ft->getName()])) {
					$db->execQuery($this->getDBTableName().'_next', 'SELECT * FROM '.$ft->props['l_table'].' WHERE id='.$ret[$ft->getName()]);
					if ($a = $db->getNextArray($this->getDBTableName().'_next')) {
						foreach ($a as $k => $v) {
							$ret[$ft->getName().'_'.$k] = $v;
						}
					}
				}
			} else if ($ft->props['type'] == 'image') {
				if (!empty($ret[$ft->getName()])) {
					global $PRJ_DIR;
					if (is_array($i = @GetImageSize($PRJ_DIR.$ret[$ft->getName()]))) {
						$ret[$ft->getName().'_width'] = $i[0];
						$ret[$ft->getName().'_height'] = $i[1];
					}
					if (isset($ft->props['sizes'])) {
						$path_parts = pathinfo($PRJ_DIR.$ret[$ft->getName()]);
						$path_parts2 = pathinfo($ret[$ft->getName()]);
						$asizes = explode(',', $ft->props['sizes']);
						foreach ($asizes as $sz) {
							$asz = explode('|', $sz);
							if (sizeof($asz) == 2 && is_array($i = @GetImageSize($path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename']))) {
								$ret[$asz[0].'_'.$ft->getName()] = $path_parts2['dirname'].'/'.$asz[0].'_'.$path_parts2['basename'];
								$ret[$asz[0].'_'.$ft->getName().'_width'] = $i[0];
								$ret[$asz[0].'_'.$ft->getName().'_height'] = $i[1];
							}
						}
					}
				}
			}
		}
		}
		return $ret;
	}
	function getNextArrays($bDetailed = true) {
	global $db;
		$ret = array();
		if ($this->getNumRows())
			while ($a = $this->getNextArray($bDetailed))
				$ret[] = $a;
		return $ret;
	}
	function getArraysWhere($where = '', $limit = false, $sort = '', $detail = true) {
		$this->select(array('where' => $where, 'limit' => $limit, 'order_by' => $sort)); 
		return $this->getNextArrays($detail);
	}
	function getItem($where = 0, $sort = '', $select = '', $detail = true) {
		$where = is_numeric($where) ? 'id='.$where : $where;
		$this->select(array('where' => $where, 'select' => $select, 'order_by' => $sort));
		return $this->getNextArray($detail);    
	}
	function getValue($id, $name) {
		$a = $this->getItem(intval($id));
		return is_array($a) ? $a[$name] : false;
	}

	/*** more */
	function getFieldByProperty($propertyName, $propertyValue, $counter = 0) {
		foreach ($this->fields as $k => $f) {
			if (!empty($f[$propertyName]) && $f[$propertyName] == $propertyValue) {
				if (!$counter) {
					return $f;
				} else {
					$counter--;
				}
			}
		}
		return false;
	}
	function getFieldByName($name) {
		return isset($this->fields[$name]) ? $this->fields[$name] : false;
	}

	/*** tree methods */
	function getPrev($id, $linkName = 'p_id') {
		if ($a = $this->getItem(intval($id))) {
			$ret = $this->getPrev($a[$linkName], $linkName);
			$ret[] = $a;
		} else {
			$ret = array();
		}
		return $ret;
	}

	function getSub($id, $linkName = 'p_id') {
		$id = intval($id);
		$ret = $id;
		if (sizeof($a = $this->getArraysWhere($linkName.'='.$id)) > 0) {
			foreach ($a as $v) {
				$ret .= ','.$this->getSub($v['id'], $linkName);
			}
		}
		return $ret;
	}

	function getSubAsArray($id, $linkName = 'p_id') {
		return preg_split(',', $this->getSub($id, $linkName));
	}

	function getCount($where = '') {
		$a = array('select' => 'COUNT(id) as c');
		if ($where) {
			$a['where'] = $where;
		}
		return $this->select($a) && ($a = $this->getNextArray()) ? $a['c'] : 0;
	}

	public function getNumRows() {
		global $db;
		return $db->getNumRows($this->getDBTableName());
	}

	public function setDBTableName($sModuleName, $sTableName) {
		$this->tableNameDB = $sModuleName.'_'.$sTableName;
	}

	public function getDBTableName() {
		return $this->tableNameDB;
	}

	private function setTableFields () {
		try {
			if ($this->props['is_system']) {
				$this->readConfig();
			} else {
				$this->readDBConfig();
			}
		} catch (Exception $e) {
			echo CUtils::showError($e->getMessage());
		}

		if ($this->props['is_sort']) {
			$this->fields['ord'] = array(
				'name' => 'ord',
				'title' => 'Сорт.',
				'type' => 'number',
				'width' => '5%',
				'defvalue' => '500',
				'group_update' => true
			);
		}
		if ($this->props['is_publish']) {
			$this->fields['publish'] = array (
				'name' => 'publish',
				'title' => 'Акт.',
				'type' => 'checkbox',
				'search' => true,
				'group_update'  => true,
				'width' => '1%'
			);
		}
		if ($this->props['is_lang']) {
			$this->fields['lang'] = array (
				'name'  => 'lang',
				'title' => 'Язык',
				'type'  => 'string',
				'readonly' => true
			);
		}
		$this->fields['credate'] = array (
			'name'  => 'credate',
			'title' => 'Дата создания',
			'type'  => 'datetime',
			'readonly' => true
		);
		$this->fields['change_date'] = array (
			'name'  => 'change_date',
			'title' => 'Дата изменения',
			'type'  => 'datetime',
			'readonly' => true
		);
		foreach ($this->fields as $k => $f) {
			$this->fields[$k]['cls'] = $this->getDBTableName();
		}
	}
	
}
