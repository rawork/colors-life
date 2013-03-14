<?php

namespace Fuga\Component\DB;
	
class Table {
	public $name;
	public $id;
	public $title;
	public $fields;
	public $params;

	public $moduleName;
	public $tableName;
	private $dbname;
	private $stmt;
	private $realTypes = array (
			'html' => 'text', 'checkbox' => 'boolean', 'currency' => 'decimal', 'select' => 'integer',
			'select_tree' => 'integer', 'select_list' => 'string', 'date' => 'date', 'datetime' => 'datetime',
			'text' => 'text', 'password' => 'string', 'enum' => 'string', 'image' => 'string',
			'string' => 'string', 'file' => 'string', 'number' => 'integer', 'template' => 'string'
	);

	public function __construct($table) {
		$this->name = $table['name'];
		$this->title = $table['title'];
		$this->tableName		= $table['name'];
		$this->moduleName		= $table['component'];
		$this->dbname			= $this->moduleName.'_'.$this->tableName;

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
	
	public function dbName() {
		return $this->dbname;
	}
	
	public function realType($type) {
		return $this->realTypes[$type];
	}

	private function readConfig() {
		if (!empty($this->params['fieldset']) && is_array($this->params['fieldset'])) {
			$this->fields = $this->params['fieldset'];
		} else {
			throw new \Exception('Table config file format error: '.$this->tableName);
		}
	}

	private function readDBConfig() {
		$sql = "SELECT * FROM table_attributes WHERE publish=1 AND table_id= :id ORDER by sort";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->bindValue('id', $this->id);
		$stmt->execute();
		$fields = $stmt->fetchAll();
		if ($fields) {
			foreach ($fields as $field) {
				$field['group_update'] = $field['group_update'] == 1;
				$field['readonly'] = $field['readonly'] == 1;
				$field['search'] = $field['search'] == 1;
				if (!empty($field['params'])) {
					$params = explode(';', trim($field['params']));
					foreach ($params as $param) {
						if (!empty($param) && stristr($param, ':')) {
							$values = explode(':', $param);
							$field[$values[0]] = str_replace("`", "'", $values[1]);
						}
					}
				}
				$this->fields[$field['name']] = $field;
			}
		} else {
			throw new \Exception('В таблице '.$this->dbName().' не настроены поля');
		}
	}
	
	public function createFieldType($field, $entity = null) {
		switch ($field['type']) {
			case 'select_tree':
				$fieldName = 'SelectTree';
				break;
			case 'select_list':
				$fieldName = 'SelectList';
				break;
			default:	
				$fieldName = ucfirst($field['type']);
				break;
		}
		$className = '\\Fuga\\Component\\DB\\Field\\'.$fieldName.'Type';
		return new $className($field, $entity);
	}

	public function getFieldList() {
		$ret = array('id');
		foreach ($this->fields as $field) {
			$ret[] = $field['name'];
		}
		return $ret;
	}

	public function insertGlobals() {
		$extraIds = array();
		$values = array();
		foreach ($this->fields as $field) {
			if ($field['type'] == 'listbox') {
				continue;
			}	
			$fieldType = $this->createFieldType($field);
			if ($field['name'] == 'created') {
				$values[$fieldType->getName()] = date('Y-m-d H:i:s');
			} elseif ($field['name'] == 'locale') {
				$values[$fieldType->getName()] = $this->get('router')->getParam('locale');
			} else {
				$values[$fieldType->getName()] = $fieldType->getSQLValue();
			}
			if (($field['type'] == 'select' 
				|| $field['type'] == 'select_tree')
				&& !empty($field['link_type']) 
				&& $field['link_type'] == 'many'
				) {
				$extraIds = explode(',', $this->get('util')->_postVar($field['name'].'_extra'));
				$linkTable = $field['link_table'];
				$linkInversed = $field['link_inversed'];
				$linkMapped = $field['link_mapped'];
			}
		}
		if (!$this->insert($values)) {
			$lastId = $this->get('connection1')->lastInsertId();
			foreach ($extraIds as $extraId) {
				$this->get('connection1')->insert(
					$linkTable,
					array($linkInversed => $lastId, $linkMapped => $extraId)
				);
			}
			
			return $lastId;
		} else {
			return false;
		}
	}
	
	public function updateGlobals() {
		$entityId = $this->get('util')->_postVar('id', true);
		$entity = $this->getItem($entityId);
		$values = array();
		foreach ($this->fields as $field) {
			if ($field['type'] == 'listbox') {
				continue;
			}
			$fieldType = $this->createFieldType($field, $entity);
			if ($this->dbName() == 'user_user' && $field['name'] == 'login' && $entityId == 1) {
				$values[$fieldType->getName()] = 'admin';
			} elseif ($field['name'] == 'updated') {
				$values[$fieldType->getName()] = date('Y-m-d H:i:s');
			} elseif (empty($field['readonly'])) {
				$values[$fieldType->getName()] = $fieldType->getSQLValue();
			}
			if (($field['type'] == 'select'	|| $field['type'] == 'select_tree')
				&& isset($field['link_type']) && $field['link_type'] == 'many'
				) {
				$extraIds = explode(',', $this->get('util')->_postVar($field['name'].'_extra'));
				$linkTable = $field['link_table'];
				$linkInversed = $field['link_inversed'];
				$linkMapped = $field['link_mapped'];
				$this->get('connection1')->delete($linkTable, array($linkInversed => $entityId));
				foreach ($extraIds as $extraId) {
					$this->get('connection1')->insert($linkTable, 
							array($linkInversed => $entityId, $linkMapped => $extraId)
					);
				}
			}
		}

		return $this->update($values, array('id' => $entityId));
	}

	function group_update() {
		$this->select(array('where' => 'id IN('.$this->get('util')->_postVar('ids').')')); 
		$entities = $this->getNextArrays();
		foreach ($entities as $entity) {
			$values = array();
			$entityId = $entity['id'];
			foreach ($this->fields as $field) {
				if ($field['type'] != 'listbox') {
					$fieldType = $this->createFieldType($field, $entity);
					if ($field['type'] == 'checkbox') {
						$values[$fieldType->getName()] = $this->get('util')->_postVar($fieldType->getName().$entity['id']);	
					}
					if ($this->get('util')->_postVar($fieldType->getName().$entity['id']) 
						|| isset($_FILES[$fieldType->getName().$entity['id']])) {
						$values[$fieldType->getName()] = $fieldType->getGroupSQLValue(); 
					}	
				}
				if (($field['type'] == 'select' || $field['type'] == 'select_tree')
					&& isset($field['link_type']) && $field['link_type'] == 'many'
					) {
					$extraIds = explode(',', $this->get('util')->_postVar($field['name'].$entityId.'_extra'));
					$linkTable = $field['link_table'];
					$linkInversed = $field['link_inversed'];
					$linkMapped = $field['link_mapped'];
					$this->get('connection1')->delete($linkTable, array($linkInversed => $entityId));
					foreach ($extraIds as $extraId) {
						$this->get('connection1')->insert($linkTable, array(
							$linkInversed => $entityId,
							$linkMapped => $extraId
						));
					}
				}
			}
			if ($values) {
				$this->update($values, array('id' => $entity['id']));
			}	
		}
		return true;
	}
	
	public function getSchema() {
		$schema = new \Doctrine\DBAL\Schema\Schema();
		$table = $schema->createTable($this->dbName());
		$column = $table->addColumn('id', 'integer', array('unsigned' => true));
		$column->setAutoincrement(true);
		foreach ($this->fields as $field) {
			$table->addColumn($field['name'], $this->realType($field['type']));
		}
		$table->setPrimaryKey(array('id'));
		return $schema;
	}

	public function create() {
		try {
			$queries = $this->getSchema()->toSql($this->get('connection1')->getDatabasePlatform());
			foreach ($queries as $sql) {
				$this->get('connection1')->query($sql);
			}
			return true;
		} catch (\Exception $e) {
			return false;
		}	
	}
	
	public function alter() {
		try {
			$sm = $this->get('connection1')->getSchemaManager();
			$fromSchema = $sm->createSchema();
			$toSchema = clone $fromSchema;
			$table = $toSchema->getTable($this->dbName());
			foreach ($this->fields as $field) {
				if (in_array($field['type'], array('listbox', 'gallery'))) {
					continue;
				}
				try {
					$column = $table->getColumn($field['name']);
					if ($column->getType()->getName() != $this->realType($field['type'])) {
						$table->changeColumn(
							$field['name'], 
							array('Type' => \Doctrine\DBAL\Types\Type::getType($this->realType($field['type']))
						));
					}
				} catch (\Exception $e) {
					$table->addColumn($field['name'], $this->realType($field['type']));
				}
			}
			$columns = $table->getColumns();
			foreach ($columns as $column) {
				if ('id' == $column->getName()) {
					continue;
				}	
				if (!isset($this->fields[$column->getName()]))
					$table->dropColumn($column->getName());
			}

			// TODO Написать создание уникальных индексов по описанию
			// TODO Написать создание индексов по описанию

			if ($this->params['is_search']) {
				// TODO Заново написать создание индексов для поиска
			}
			
			$queries = $fromSchema->getMigrateToSql($toSchema, $this->get('connection1')->getDatabasePlatform());
			foreach ($queries as $sql) {
				$this->get('connection1')->query($sql);
			}
			return true;
		} catch (\Exception $e) {
			return false;
		}	
		
	}
	
	private function drop() {
		return $this->get('connection1')->query('DROP TABLE '.$this->dbName());
	}
	
	private function truncate() {
		return $this->get('connection1')->query('TRUNCATE TABLE '.$this->dbName());
	}
	
	function getSearchSQL() {
		$filters = array();
		if (!empty($_REQUEST['search_filter_id'])) {
			$filters[] = 'id='.intval($_REQUEST['search_filter_id']);
		}
		foreach ($this->fields as $field) {
			if ($field['type'] != 'listbox') {
				$fieldType = $this->createFieldType($field);
				if ($filter = $fieldType->getSearchSQL()) {
					$filters[] = $filter;
				}
			}
		}
		return implode(' AND ', $filters);
	}

	public function getSearchURL() {
		$filters = array();
		if (!empty($_REQUEST['search_filter_id'])) {
			$filters[] = 'search_filter_id='.intval($_REQUEST['search_filter_id']);
		}
		foreach ($this->fields as $field) {
			if ($field['type'] != 'listbox') {
				$fieldType = $this->createFieldType($field);
				if ($filter = $fieldType->getSearchURL()) {
					$filters[] = $filter;
				}
			}
		}
		return implode('&', $filters);
	}
	
	public function insert($values) {
		return $this->get('connection1')->insert($this->dbName(), $values);
	}
	
	function insertArray($entity) {
		$values = array();
		foreach ($entity as $key => $v) {
			foreach ($this->fields as $field) {
				if ($key && $field['name'] == $key) {
					$fieldType = $this->createFieldType($field);
					if ($entity[$field['name']] && ($field['type'] == 'image' || $field['type'] == 'file' || $field['type'] == 'template')) {
						$dest = $this->get('util')->getNextFileName($v);
						@copy($GLOBALS['PRJ_DIR'].$v, $GLOBALS['PRJ_DIR'].$dest);
						$values[$fieldType->getName()] = $dest;

						if ($field['type'] == 'image' && isset($fieldType->params['sizes'])) {
							$pathParts0 = pathinfo($v);
							$pathParts = pathinfo($dest);
							$sizes = explode(',', $fieldType->params['sizes']);
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
						$values[$fieldType->getName()] = $v;
					}
					break;
				}
			}
		}
		$ret = $this->insert($values);
		$lastId = $this->get('connection1')->lastInsertId();
		if ($this->params['multifile']) {
			$sql = "SELECT * FROM system_files WHERE entity_id= :id AND table_name= :table";
			$stmt = $this->get('connection1')->prepare($sql);
			$stmt->bindValue('id', $entity['id']);
			$stmt->bindValue('table', $this->dbName());
			$stmt->execute();
			$photos = $stmt->fetchAll();
			foreach ($photos as $photo) {
				$filepath = $photo['file'];
				$dest = $this->get('util')->getNextFileName($filepath);
				@copy($GLOBALS['PRJ_DIR'].$filepath,$GLOBALS['PRJ_DIR'].$dest);
				unset($photo['id']);
				$photo['file'] 		= $dest;
				$photo['created'] 	= date("Y-m-d H:i:s");
				$photo['entity_id'] = $lastId;
				$this->get('connection1')->insert('system_files', $photo);
			}
		}
		return $ret;
	}

	public function update($values, $criteria) {
		return $this->get('connection1')->update($this->dbName(), $values, $criteria);
	}
	
	public function delete($criteria) {
		return $this->get('connection1')->query('DELETE FROM '.$this->dbName().' WHERE '.$criteria);
	}
	
	public function select($options = array()) {
		try {
			if ($this->params['is_lang']) {
				$locale = $this->get('router')->getParam('locale');
				$options['where'] = empty($options['where']) ? 
						"locale='".$locale."'" 
						: 
						$options['where']." AND locale='".$locale."'";
			}
			if (empty($options['select'])) {
				$options['select'] = implode(',', $this->getFieldList());
			}
			if (empty($options['from'])) {
				$options['from'] = $this->dbName();
			}
			if (empty($options['where'])) {
				$options['where'] = '1=1';
			}
			if (empty($options['order_by'])) {
				$options['order_by'] = $this->params['order_by'] ?: 'id';
			}
			if (empty($options['limit'])) {
				$options['limit'] = '100000';
			}
			$sql = 'SELECT '.$options['select'].
				' FROM '.$options['from'].
				' WHERE '.$options['where'].
				' ORDER BY '.$options['order_by'].
				' LIMIT '.$options['limit'];
			$this->stmt = $this->get('connection1')->prepare($sql);
			$this->stmt->execute();
			
			return true;	
		} catch (\Exception $e) {
			
			return false;
		}	
	}
	
	public function getNextArray($detailed = true) {
		$ret = $this->stmt->fetch();
		if ($detailed) {
			foreach ($this->fields as $field) {
				$fieldType = $this->createFieldType($field);
				if (stristr($fieldType->params['type'], 'select')) {
					if (!empty($ret[$fieldType->getName()])) {
						$sql = 'SELECT * FROM '.$fieldType->params['l_table'].' WHERE id='.$ret[$fieldType->getName()];
						$stmt = $this->get('connection1')->prepare($sql);
						$stmt->execute();
						$item = $stmt->fetch();
						if ($item) {
							foreach ($item as $k => $v) {
								$ret[$fieldType->getName().'_'.$k] = $v;
							}
						}
					}
				} else if ($fieldType->params['type'] == 'image') {
					if (!empty($ret[$fieldType->getName()])) {
						global $PRJ_DIR;
						if (is_array($i = @GetImageSize($PRJ_DIR.$ret[$fieldType->getName()]))) {
							$ret[$fieldType->getName().'_width'] = $i[0];
							$ret[$fieldType->getName().'_height'] = $i[1];
						}
						if (isset($fieldType->params['sizes'])) {
							$pathParts = pathinfo($ret[$fieldType->getName()]);
							$sizes = explode(',', $fieldType->params['sizes']);
							foreach ($sizes as $sizeData) {
								$sizeParams = explode('|', $sizeData);
								if (count($sizeParams) == 2 && is_array($i = @GetImageSize($PRJ_DIR.$pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0].'.'.$pathParts['extension']))) {
									$ret[$sizeParams[0].'_'.$fieldType->getName()] = $pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0].'.'.$pathParts['extension'];
									$ret[$sizeParams[0].'_'.$fieldType->getName().'_width'] = $i[0];
									$ret[$sizeParams[0].'_'.$fieldType->getName().'_height'] = $i[1];
								}
							}
						}
					}
				}
			}
		}
		return $ret;
	}
	
	public function getNextArrays($detailed = true) {
		$items = array();
		while ($item = $this->getNextArray($detailed)) {
			if (isset($item['id'])) {
				$items[$item['id']] = $item;
			} else {
				$items[] = $item;
			}
		}	
		
		return $items;
	}
	
	public function getItem($criteria, $sort = '', $select = '', $detailed = true) {
		$criteria = is_numeric($criteria) ? 'id='.$criteria : $criteria;
		$this->select(array('where' => $criteria, 'select' => $select, 'order_by' => $sort));
		return $this->getNextArray($detailed);    
	}
	
	public function getPrev($id, $parent = 'parent_id') {
		$ret = array();
		$node = $this->getItem($id);
		if ($node) {
			$ret = $this->getPrev($node[$parent], $parent);
			$ret[] = $node;
		}
		
		return $ret;
	}

	function count($criteria = '') {
		$sql = 'SELECT COUNT(id) as с FROM '.$this->dbName().' WHERE '.$criteria;
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$item = $stmt->fetch();
		
		return $item ? (int)$item['c'] : 0;
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
		foreach ($this->fields as &$field) {
			$field['table'] = $this->dbName();
		}
	}
	
	public static function fillValue(&$value, $key) {
		$value = "'".$value."'";
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
