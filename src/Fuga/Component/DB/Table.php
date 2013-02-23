<?php

namespace Fuga\Component\DB;
	
function fillValue(&$value, $key) {
	$value = "'".$value."'";
}

class Table {
	public $name;
	public $cname;
	public $Id;
	public $cls;
	public $title;
	public $fields;
	public $params;

	public $moduleName;
	public $tableName;
	private $tableNameFull;

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

		$this->params = $table;
		$this->setTableFields();
	}

	/**
	 * Set fields propeties
	 * @throws Exception 
	 */
	function readConfig() {
		if (!empty($this->params['fieldset']) && is_array($this->params['fieldset'])) {
			$this->fields = $this->params['fieldset'];
		} else {
			throw new \Exception('Table config file format error: '.$this->tableName);
		}
	}

	/**
	 * Read Table config from DB
	 * @throws Exception 
	 */
	function readDBConfig() {
		$fields = $this->get('connection')->getItems('table_fields', "SELECT * FROM table_attributes WHERE publish=1 AND table_id=".$this->id." ORDER by sort");
		if (count($fields) > 0) {
			foreach ($fields as $f) {
				$f['group_update'] = $f['group_update'] == 1;
				$f['readonly'] = $f['readonly'] == 1;
				$f['search'] = $f['search'] == 1;
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

		} else {
			throw new \Exception('No fields in table: '.$this->getDBTableName());
		}
	}
	
	function createFieldType(&$fieldParams, $entity = null) {
		switch ($fieldParams['type']) {
			case 'select_tree':
				$fieldName = 'SelectTree';
				break;
			case 'select_list':
				$fieldName = 'SelectList';
				break;
			default:	
				$fieldName = ucfirst($fieldParams['type']);
				break;
		}
		$className = '\\Fuga\\Component\\DB\\Field\\'.$fieldName.'Type';
		return new $className($fieldParams, $entity);
	}

	/*** standart SQL operations */
	function getSQLFieldsList() {
		$ret = '';
		foreach ($this->fields as $aField) {
			$ret .= ($ret ? ',' : '').$aField['name'];
		}
		return $ret;
	}

	function insertGlobals() {
		$query = '';
		$extraIds = array();
		foreach ($this->fields as $fieldParams) {
			if ($fieldParams['type'] == 'listbox') {
				continue;
			}	
			$ft = $this->createFieldType($fieldParams);
			if ($fieldParams['name'] == 'created') {
				$query .= ($query ? ', ' : ' ').'NOW()';
			} elseif (stristr($fieldParams['type'], 'date')) {
				$query .= ($query ? ', ' : '').$ft->getSQLValue();
			} elseif ($fieldParams['name'] == 'locale') {
				$query .= ($query ? ", '" : "'").$this->get('util')->_sessionVar('locale', false, 'ru')."'";
			} else {
				$query .= ($query ? ", '" : "'").$ft->getSQLValue()."'";
			}
			if (($fieldParams['type'] == 'select' 
				|| $fieldParams['type'] == 'select_tree')
				&& !empty($fieldParams['link_type']) 
				&& $fieldParams['link_type'] == 'many'
				) {
				$extraIds = explode(',', $this->get('util')->_postVar($fieldParams['name'].'_extra'));
				$linkTable = $fieldParams['link_table'];
				$linkInversed = $fieldParams['link_inversed'];
				$linkMapped = $fieldParams['link_mapped'];
			}
		}
		if ($ret = $this->insert($this->getSQLFieldsList(), $query)) {
			$lastId = $this->get('connection')->getInsertID();
			if (count($extraIds) > 0) {

				foreach ($extraIds as $extraId) {
					$this->get('connection')->execQuery('ins', 
						'INSERT INTO '.$linkTable.'('.$linkInversed.','.$linkMapped.') VALUES('.$lastId.','.$extraId.')'
					);
				}
			}
			return $lastId;
		} else {
			return false;
		}
		
	}
	function updateGlobals() {
		$entityId = $this->get('util')->_postVar('id', true);
		$entity = $this->getItem($entityId);
		$categoryId = 0;
		$sql = '';
		foreach ($this->fields as $fieldParams) {
			if ($fieldParams['type'] == 'listbox') {
				continue;
			}
			$ft = $this->createFieldType($fieldParams, $entity);
			if ($fieldParams['name'] == 'category_id')
				$categoryId = $ft->getValue();
			if ($this->getDBTableName() == 'user_user' && $fieldParams['name'] == 'login' && $entityId == 1) {
				$sql .= ($sql ? ', ' : '').$ft->getName()."='admin'";
			} elseif ($fieldParams['name'] == 'updated') {
				$sql .= ($sql ? ', ' : '').$ft->getName().'= NOW()';
			} elseif (empty($fieldParams['readonly'])) {
				if (stristr($fieldParams['type'], 'date') || $fieldParams['type'] == 'select' || $fieldParams['type'] == 'select_tree' || $fieldParams['type'] == 'number' || $fieldParams['type'] == 'currency')
					$sql .= ($sql ? ', ' : '').$ft->getName().'='.$ft->getSQLValue(); 
				else
					$sql .= ($sql ? ', ' : '').$ft->getName()."='".$ft->getSQLValue()."'";
			}
			if (($fieldParams['type'] == 'select' 
				|| $fieldParams['type'] == 'select_tree')
				&& !empty($fieldParams['link_type']) 
				&& $fieldParams['link_type'] == 'many'
				) {
				$extraIds = explode(',', $this->get('util')->_postVar($fieldParams['name'].'_extra'));
				$linkTable = $fieldParams['link_table'];
				$linkInversed = $fieldParams['link_inversed'];
				$linkMapped = $fieldParams['link_mapped'];
				$this->get('connection')->execQuery('ins', 
					'DELETE FROM '.$linkTable.' WHERE '.$linkInversed.'='.$entityId
				);
				if (count($extraIds) > 0) {
					
					foreach ($extraIds as $extraId) {
						$this->get('connection')->execQuery('ins', 
							'INSERT INTO '.$linkTable.'('.$linkInversed.','.$linkMapped.') VALUES('.$entityId.','.$extraId.')'
						);
					}
				}
			}
		}
		// Обновление значений дополнительных свойств
		if ($this->getDBTableName() == 'catalog_product1') {
			$category = $this->get('connection')->getItem('get_cat', 'SELECT id,title,name,filters from catalog_category where id='.$categoryId);
			if ($category) {
				$features = $this->get('connection')->getItems('get_features', 'SELECT * from catalog_features where id IN('.$category['filters'].')');
				foreach($features as $featureData) {
					$featureId = $featureData['id'];
					$filterValue = $this->get('util')->_postVar('filter_'.$featureId, true);
					$feature_value = $this->get('connection')->getItem('get_value', 'SELECT id, feature_value_id FROM catalog_features_values WHERE feature_id='.$featureId.' AND product_id='.$entityId);
					if ($feature_value){
						$values = "feature_value_id=".$filterValue;
						$this->get('connection')->execQuery('upd_value', "UPDATE catalog_features_values set ".$values." where id=".$feature_value['id']);
					} else { 
						$values = $filterValue.','.$featureId.','.$entityId;
						$this->get('connection')->execQuery('add_value', "INSERT INTO catalog_features_values (feature_value_id,feature_id,product_id) VALUES (".$values.")");
					}
				}
			}
		}
		$where = '';
		if (($this->getDBTableName() == 'user_user' || $this->getDBTableName() == 'user_group') && !$this->get('security')->isSuperuser())
			$where = ' AND id<>1';

		return $this->update($sql.' WHERE id='.$entityId.$where);
	}

	function group_update() {
		$entities = $this->getArraysWhere('id IN('.$this->get('util')->_postVar('ids').')');
		$query = '';
		foreach ($entities as $entity) {
			$values = '';
			$entityId = $entity['id'];
			foreach ($this->fields as $fieldParams) {
				if ($fieldParams['type'] != 'listbox') {
					$fieldType = $this->createFieldType($fieldParams, $entity);
					if ($fieldParams['type'] == 'checkbox' && !isset($_POST[$fieldType->getName().$entity['id']])) {
						$values .= ($values ? ',' : '').$fieldType->getName()."=0";	
					} elseif (isset($_POST[$fieldType->getName().$entity['id']]) || isset($_FILES[$fieldType->getName().$entity['id']]))
						if (stristr($fieldParams['type'], 'date') || $fieldParams['type'] == 'select' || $fieldParams['type'] == 'select_tree' || $fieldParams['type'] == 'number' || $fieldParams['type'] == 'currency')
							$values .= ($values ? ', ' : '').$fieldType->getName().'='.$fieldType->getGroupSQLValue(); 
						else
							$values .= ($values ? ', ' : '').$fieldType->getName()."='".$fieldType->getGroupSQLValue()."'";
				}
				if (($fieldParams['type'] == 'select' 
					|| $fieldParams['type'] == 'select_tree')
					&& !empty($fieldParams['link_type']) 
					&& $fieldParams['link_type'] == 'many'
					) {
					$extraIds = explode(',', $this->get('util')->_postVar($fieldParams['name'].$entityId.'_extra'));
					$linkTable = $fieldParams['link_table'];
					$linkInversed = $fieldParams['link_inversed'];
					$linkMapped = $fieldParams['link_mapped'];
					$this->get('connection')->execQuery('ins', 
						'DELETE FROM '.$linkTable.' WHERE '.$linkInversed.'='.$entityId
					);
					if (count($extraIds) > 0) {
						foreach ($extraIds as $extraId) {
							$this->get('connection')->execQuery('ins', 
								'INSERT INTO '.$linkTable.'('.$linkInversed.','.$linkMapped.') VALUES('.$entityId.','.$extraId.')'
							);
						}
					}
				}
			}
			if ($values)
				$query .= 'UPDATE '.$this->getDBTableName().' SET '.$values.' WHERE id='.$entity['id'].';#|#|#';
		}
		return $this->get('connection')->execQuery($this->getDBTableName().'_update', $query);
	}

	/* special operations */
	function create() {
		$query = 'CREATE TABLE '.$this->getDBTableName();
		$query .= '( id int(11) NOT NULL auto_increment, ';
		foreach ($this->fields as $aField) {
			$oFieldType = $this->createFieldType($aField);
			if ($oFieldType->getSQL()) {
				$query .= $oFieldType->getSQL().', ';
			}
		}
		$query .= ' PRIMARY KEY(id)) TYPE = InnoDB';
		return $this->get('connection')->execQuery($this->getDBTableName(), $query);
	}
	function drop() {
		return $this->get('connection')->execQuery($this->getDBTableName().'_droptable', 'DROP TABLE '.$this->getDBTableName());
	}
	function truncate() {
		return $this->get('connection')->execQuery($this->getDBTableName().'truncateclass', 'TRUNCATE TABLE '.$this->getDBTableName());
	}
	function alter() {
	global $DB_TYPE;
		$ret = true;
		$sql = '';
		$fields = $this->get('connection')->getFieldsList($this->getDBTableName());
		if (!is_array($fields))
			return false;
		foreach ($this->fields as $f) {
			if ($f['type'] != 'listbox' && $f['type'] != 'gallery') {
				$ft = $this->createFieldType($f);
				if (!isset($fields[$f['name']]))
					$sql .= 'ALTER TABLE '.$this->getDBTableName().' ADD COLUMN '.$ft->getSQL().';#|#|#';
				if (isset($fields[$f['name']]) && $fields[$f['name']]['Type'] != $this->get('connection')->getFieldRealType($f['type']))
					$sql .= 'ALTER TABLE '.$this->getDBTableName().' CHANGE '.$f['name'].' '.$ft->getSQL().';#|#|#';
			// Доработать изменение типа str to int
			// Доработать изменение имени по сравнении старого имени
			}
		}
		foreach ($fields as $k => $f) {
			if ($k == 'id' || $f['Type'] == 'listbox' || $f['Type'] == 'gallery')
				continue;
			if (!isset($this->fields[$k]))
				$sql .= 'ALTER TABLE '.$this->getDBTableName().' DROP COLUMN '.$f['Field'].';#|#|#';
		}
		if ($this->params['is_search']) {
			$indexes = $this->get('connection')->getItems('get_indexes', 'SHOW INDEX FROM '.$this->getDBTableName());
			$fulltext_exists = false;
			foreach ($indexes as $ind) {
				if ($ind['Key_name'] == 'search' && $ind['Index_type'] == 'FULLTEXT') {
					$fulltext_exists = true;
					break;
				}
			}
			$search_fields = '';
			foreach ($this->fields as $f) {
				if (($f['type'] == 'html' || $f['type'] == 'text' || $f['type'] == 'string') && $f['name'] != 'locale' && $f['name'] != 'sort') {
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
		if ($sql) {
			return $this->get('connection')->execQuery('alter_table_'.$this->getDBTableName(), $sql);
		}
		return $ret;
	}

	/*** More */
	function getSearchSQL() {
		$ret = '';
		if (!empty($_REQUEST['search_filter_id'])) {
			$ret .= ' id = '.intval($_REQUEST['search_filter_id']);
		}
		foreach ($this->fields as $f) {
			if ($f['type'] != 'listbox') {
				$fieldType = $this->createFieldType($f);
				if ($filter = $fieldType->getSearchSQL()) {
					$ret .= $ret ? ' AND '.$filter : $filter;
				}
			}
		}
		return $ret;
	}

	function getSearchURL() {
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
		return $this->get('connection')->execQuery($this->getDBTableName(), 'INSERT INTO '.$this->getDBTableName().'('.$names.') VALUES('.$values.')');
	}
	function insertArray($entity) {
		$names = $values = '';
		foreach ($entity as $key => $v) {
			foreach ($this->fields as $fieldData) {
				if ($key && $fieldData['name'] == $key) {
					$names = ($names ? $names.', ' : '').$fieldData['name'];
					if ($entity[$fieldData['name']] && ($fieldData['type'] == 'image' || $fieldData['type'] == 'file' || $fieldData['type'] == 'template')) {
						$ft = $this->createFieldType($fieldData);
						$dest = $this->get('util')->getNextFileName($v);
						@copy($GLOBALS['PRJ_DIR'].$v,$GLOBALS['PRJ_DIR'].$dest);
						$values = ($values ? $values.', ' : '')."'".$dest."'";

						if ($fieldData['type'] == 'image' && isset($ft->params['sizes'])) {
							$pathParts0 = pathinfo($v);
							$pathParts = pathinfo($dest);
							$sizes = explode(',', $ft->params['sizes']);
							foreach ($sizes as $sizeData) {
								$sizeParams = explode('|', $sizeData);
								if (count($sizeParams) == 2) {
									$source = $pathParts0['dirname'].'/'.$pathParts0['filename'].'_'.$sizeParams[0].'.'.$pathParts0['extension'];
									$dest = $pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0].'.'.$pathParts['extension'];
									@copy($GLOBALS['PRJ_DIR'].$source, $GLOBALS['PRJ_DIR'].$dest);
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
		$lastId = $this->get('connection')->getInsertID();
		if ($this->params['multifile']) {
			$sql = "SELECT * FROM system_files WHERE entity_id={$entity['id']} AND table_name='".$this->getDBTableName()."'";
			$photos = $this->get('connection')->getItems('get_system_files', $sql);
			foreach ($photos as $photo) {
				$filepath = $photo['file'];
				$dest = $this->get('util')->getNextFileName($filepath);
				@copy($GLOBALS['PRJ_DIR'].$filepath,$GLOBALS['PRJ_DIR'].$dest);
				unset($photo['id']);
				$photo['file'] 		= $dest;
				$photo['created'] 	= date("Y-m-d H:i:s");
				$photo['entity_id'] = $lastId;
				$names = implode(',', array_keys($photo));
				array_walk($photo, "fillValue");
				$values = implode(',', $photo);
				$sql = "INSERT INTO system_files ($names) VALUES ($values)";
				$this->get('connection')->execQuery($this->getDBTableName(), $sql);
			}
		}
		//exit;
		return $ret;
	}

	function update($sUpdate) {
		$sDBTableName = $this->getDBTableName();
		$sQuery = "
			UPDATE
				$sDBTableName
			SET
				$sUpdate
		";
		//die($sQuery);
		return $this->get('connection')->execQuery($sDBTableName, $sQuery);
	}
	function delete($sQuery) {
		return $this->get('connection')->execQuery($this->getDBTableName().'_deleterecords', 'DELETE FROM '.$this->getDBTableName().' WHERE '.$sQuery);
	}
	function select($a = null) {
		if ($this->params['is_lang']) {
			$a['where'] = empty($a['where']) ? "locale='".$this->get('util')->_sessionVar('locale', false, 'ru')."'" : $a['where']." AND locale='".$this->get('util')->_sessionVar('locale', false, 'ru')."'";
		}
		return $this->get('connection')->execQuery($this->getDBTableName(),
			'SELECT '.(!empty($a['select']) ? $a['select'] : '*').' FROM '.
			(!empty($a['from']) ? $a['from'] : $this->getDBTableName()).
			(!empty($a['where']) ? ' WHERE '.$a['where'] : '').
			(!empty($a['order_by']) ? ' ORDER BY '.$a['order_by'] : (!empty($this->params['order_by']) ? ' ORDER BY '.$this->params['order_by'] : ' ORDER BY id')).
			(!empty($a['limit']) ? ' LIMIT '.$a['limit'] : '')
		);
	}
	function selectWhere($where, $sort = '', $select = '') {
		return $this->select(array('where' => $where, 'select' => $select, 'order_by' => $sort));
	}
	function getNextArray($bDetailed = true) {
		$ret = $this->get('connection')->getNextArray($this->getDBTableName());
		if ($bDetailed) {
		foreach ($this->fields as $f) {
			$ft = $this->createFieldType($f);
			if (stristr($ft->params['type'], 'select')) {
				if (!empty($ret[$ft->getName()])) {
					$this->get('connection')->execQuery($this->getDBTableName().'_next', 'SELECT * FROM '.$ft->params['l_table'].' WHERE id='.$ret[$ft->getName()]);
					if ($a = $this->get('connection')->getNextArray($this->getDBTableName().'_next')) {
						foreach ($a as $k => $v) {
							$ret[$ft->getName().'_'.$k] = $v;
						}
					}
				}
			} else if ($ft->params['type'] == 'image') {
				if (!empty($ret[$ft->getName()])) {
					global $PRJ_DIR;
					if (is_array($i = @GetImageSize($PRJ_DIR.$ret[$ft->getName()]))) {
						$ret[$ft->getName().'_width'] = $i[0];
						$ret[$ft->getName().'_height'] = $i[1];
					}
					if (isset($ft->params['sizes'])) {
						$pathParts = pathinfo($ret[$ft->getName()]);
						$sizes = explode(',', $ft->params['sizes']);
						foreach ($sizes as $sizeData) {
							$sizeParams = explode('|', $sizeData);
							if (count($sizeParams) == 2 && is_array($i = @GetImageSize($PRJ_DIR.$pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0].'.'.$pathParts['extension']))) {
								$ret[$sizeParams[0].'_'.$ft->getName()] = $pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0].'.'.$pathParts['extension'];
								$ret[$sizeParams[0].'_'.$ft->getName().'_width'] = $i[0];
								$ret[$sizeParams[0].'_'.$ft->getName().'_height'] = $i[1];
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
		$ret = array();
		if ($this->getNumRows())
			while ($a = $this->getNextArray($bDetailed))
				if (isset($a['id'])) {
					$ret[$a['id']] = $a;
				} else {
					$ret[] = $a;
				}
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
	function getPrev($id, $linkName = 'parent_id') {
		if ($a = $this->getItem(intval($id))) {
			$ret = $this->getPrev($a[$linkName], $linkName);
			$ret[] = $a;
		} else {
			$ret = array();
		}
		return $ret;
	}

	function getSub($id, $linkName = 'parent_id') {
		$id = intval($id);
		$ret = $id;
		if (sizeof($a = $this->getArraysWhere($linkName.'='.$id)) > 0) {
			foreach ($a as $v) {
				$ret .= ','.$this->getSub($v['id'], $linkName);
			}
		}
		return $ret;
	}

	function getSubAsArray($id, $linkName = 'parent_id') {
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
		return $this->get('connection')->getNumRows($this->getDBTableName());
	}

	public function setDBTableName($sModuleName, $sTableName) {
		$this->tableNameFull = $sModuleName.'_'.$sTableName;
	}

	public function getDBTableName() {
		return $this->tableNameFull;
	}

	private function setTableFields () {
		try {
			if ($this->params['is_system']) {
				$this->readConfig();
			} else {
				$this->readDBConfig();
			}
		} catch (\Exception $e) {
			echo $this->get('util')->showError($e->getMessage());
		}

		if ($this->params['is_sort']) {
			$this->fields['sort'] = array(
				'name' => 'sort',
				'title' => 'Сорт.',
				'type' => 'number',
				'width' => '5%',
				'defvalue' => '500',
				'group_update' => true
			);
		}
		if ($this->params['is_publish']) {
			$this->fields['publish'] = array (
				'name' => 'publish',
				'title' => 'Акт.',
				'type' => 'checkbox',
				'search' => true,
				'group_update'  => true,
				'width' => '1%'
			);
		}
		if ($this->params['is_lang']) {
			$this->fields['locale'] = array (
				'name'  => 'locale',
				'title' => 'Локаль',
				'type'  => 'string',
				'readonly' => true
			);
		}
		$this->fields['created'] = array (
			'name'  => 'created',
			'title' => 'Дата создания',
			'type'  => 'datetime',
			'readonly' => true
		);
		$this->fields['updated'] = array (
			'name'  => 'updated',
			'title' => 'Дата изменения',
			'type'  => 'datetime',
			'readonly' => true
		);
		foreach ($this->fields as $k => $f) {
			$this->fields[$k]['cls'] = $this->getDBTableName();
		}
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
