<?php

    class Unit {
		public $component_name;
		public $ocomponent;
        public $tables;
        public $lang;
        public $tpl;
		public $props;
		public $params;
        function __construct($name, $props = array()) {
		global $PRJ_DIR, $smarty, $db;
            $this->tables = array();
			$this->props = $props;
			$this->ocomponent = $GLOBALS['rtti']->getComponent($name);
			if (empty($this->ocomponent['name'])) {
				$this->ocomponent['name'] = $name;
			}
			$this->smarty = $smarty;
			$this->smarty->assign('tree_title', (isset($this->props['node']) && is_array($this->props['node'])) ? $this->props['node']['title'] : '');
			$this->addTables();
			$dbparams = $db->getItems('unit.settings', "SELECT * FROM config_settings WHERE komponent='$name'");
			$this->dbparams = array();
			foreach ($dbparams as $param) {
				$this->dbparams[$param['name']] = $param['type'] == 'int' ? intval($param['value']) : $param['value'];
			}
			if (empty($this->props['uri'])) {
				$this->props['uri'] = $this->ocomponent['name'];
			}
        }
        
		function addTables() {
			$this->tables = $GLOBALS['rtti']->getTables($this->ocomponent['name']);
        }
		
        /*** template methods */
        function getTpl($name) {
		global $PRJ_DIR;
            if (file_exists($PRJ_DIR.'/templates/'.$name.'.tpl')) {
                return $this->smarty->fetch($name.'.tpl');
            }
        }

        function getTitle() {
			$ret = $this->ocomponent['title'];
       		if (!empty($this->props['node']))
       			$ret = $this->props['node']['title'];
			return $ret;
        }
        
        function getH1() {
            return $this->getTitle();
        }

        /*** for cron */
        function everyMin() { ; }
        function everyHour() { ; }
        function everyDay() { ; }

        /*** search */
        function getSearchFieldsArray($tableName) {
            $ret = array();
            foreach ($this->tables[$tableName]->fields as $a)
                if (($a['type'] == 'string' || $a['type'] == 'text' || $a['type'] == 'html') && $a['name'] != 'lang')
                    $ret[] = $a['name'];
            return $ret;
        }
        
		function getSearchResultRef(&$a, $methodName = '') {
            return $GLOBALS['rtti']->href(!empty($a['dir_id']) ? $a['dir_id_name'] : $this->props['uri'], $methodName, array($a['id']));
        }
        
		function getTableSearchResults($text, $tableName, $where = '', $idName = '', $nName = 'name') {
            $ret = array();
            $fields = $this->getSearchFieldsArray($tableName);
			$fields_text = implode(',',$fields);
			$search_query = '';
			if (is_array($text)) {
				$query_text = '';
				foreach ($text as $t) {
					if (strlen($t) > 2)
						$query_text .= ($query_text ? ' ' : '').'+'.$t.'*';
				}
				$search_query = " MATCH(".$fields_text.") AGAINST ('".$query_text."' IN BOOLEAN MODE)";
            } else {
				$search_query = " MATCH(".$fields_text.") AGAINST ('\"".$text."\"' IN BOOLEAN MODE)";
			}
			$items = $GLOBALS['db']->getItems('get_search_items', "SELECT id,".$nName.",name, ".$search_query." AS coef FROM ".$this->tables[$tableName]->getDBTableName()." WHERE ".$search_query.($where ? ' AND '.$where : '').' ORDER BY coef');
            foreach ($items as $a) {
				$ret[] = array (
                    'ref' => $this->getSearchResultRef($a, $idName),
                	'text' => CUtils::cut_text(strip_tags($a[$nName], 150))
            	);
            }
            return $ret;
        }
        
		function getSearchResults($text) { return array(); }
		
        function getMapList($id = 0) {
			$a = $GLOBALS['db']->getItems('get_cats', "SELECT id,name as title,name,p_id FROM catalog_categories WHERE p_id=".$id." ORDER BY ord");
			if (sizeof($a) > 0) {
    	        foreach ($a as $k => $i) {
                	$a[$k]['ref'] = '/catalog/index.'.$i['id'].'.htm';
					$a[$k]['sub'] .= $this->getMapList($i['id']);
    	        }
			}
			$this->smarty->assign('items', $a);
			$this->smarty->assign('block', '_sub');
            return $this->smarty->fetch('service/map.tpl');
        }
		 
		function getMap() {
			if ($this->ocomponent['name'] == 'catalog') {
				return $this->getMapList();
			} else {
				return '';
			}
		} 
        
		function getPathArray() {
			$ret = array();
			if ($this->ocomponent['name'] == 'catalog' && $GLOBALS['urlprops']['method'] == 'index') {
				if (isset($GLOBALS['urlprops']['params'][0])) {
					$path = $this->tables['categories']->getPrev($GLOBALS['urlprops']['params'][0]);
					foreach ($path as $k => $item) {
						$path[$k]['title'] = $item['name'];
						$path[$k]['ref'] = $GLOBALS['rtti']->href($GLOBALS['urlprops']['node']['name'], 'index', array($item['id']));
					}
					$ret = $path;
				}
			} elseif ($this->ocomponent['name'] == 'catalog' && $GLOBALS['urlprops']['method'] == 'stuff') {
				if (isset($GLOBALS['urlprops']['params'][0])) {
					$stuff = $GLOBALS['rtti']->getItem('catalog_stuff', $GLOBALS['urlprops']['params'][0]);
					if (isset($stuff['c_id'])) {
						$path = $this->tables['categories']->getPrev($stuff['c_id']);
						foreach ($path as $k => $item) {
							$path[$k]['title'] = $item['name'];
							$path[$k]['ref'] = $GLOBALS['rtti']->href($GLOBALS['urlprops']['node']['name'], 'index', array($item['id']));
						}
						$ret = $path;
					}
				}
			}
			return $ret;
		}
		
        function getPath() {
            return '';
        }
		
		function getMethod($name) {
			
		}
    }

?>