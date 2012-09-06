<?php

    inc_lib('components/Unit.php');
    class TreeUnit extends Unit {
        private $subunit;
		private $subunit_service;
		
        function __construct($props = array()) {
			parent::__construct('tree', $props);
			if (!empty($this->props['node']['module_id_name']) && $this->props['node']['module_id_name'] != 'tree') {
				if (inc_u($this->props['node']['module_id_name'])) {
					$unitName = ucfirst($this->props['node']['module_id_name']).'Unit';
					$this->subunit = new $unitName($this->props);
					$this->subunit_service = true;
				} else {
					$this->subunit = new Unit($this->props['node']['module_id_name'], $this->props);
					$this->subunit_service = false;
				}	
			}
        }
		
        /* db */
        function getNodes($uri = 0, $where = "publish='on'", $limit = false) {
			$ret = $GLOBALS['rtti']->getNativeItems(
				'SELECT t1.*, t3.name as module_id_name FROM tree_tree as t1 '.
				'LEFT JOIN tree_tree as t2 ON t1.p_id=t2.id '.
				'LEFT JOIN config_modules as t3 ON t1.module_id=t3.id '.
				"WHERE t1.publish='on' AND t1.lang='".$this->props['lang']."' AND ".(is_numeric($uri) ? ($uri == 0 ? ' t1.p_id=0 ' : 't2.id='.$uri.' ') : "t2.name='".$uri."' ").
				'ORDER BY t1.ord,t1.name '.
				'LIMIT '.($limit ? $limit : '0,1000')
			);
   	        foreach ($ret as $k => $v) {
				if ($ret[$k]['h1_img']) {
					if (is_array($i = @GetImageSize($GLOBALS['PRJ_DIR'].$ret[$k]['h1_img']))) {
        	            $ret[$k]['h1_img_width'] = $i[0];
    	            	$ret[$k]['h1_img_height'] = $i[1];
	                }
				}
				$ret[$k]['ref'] = $this->getTreeRef($v);
            }
			return $ret;	
        }
		
		function getTreeRef($a) {
		global $PRJ_REF;
			if (trim($a['url']) != '') {
				return stristr($a['url'], 'http://') ? $a['url'] : $PRJ_REF.$a['url'];
			} else {	
				$lang = $this->props['lang'] == 'ru' ? '' : $this->props['lang'].'/';
				$alias = $a['name'] == '/' ? $PRJ_REF.$a['name'].$lang : $PRJ_REF.'/'.$lang.$a['name'].(!empty($a['module_id']) ? '/' : '.htm');
				return $alias;
			}
		}
        
		/* design */
        function getPathArray() {
           	$ret = array();
			if (!empty($this->props['node']))
				$ret = $this->tables['tree']->getPrev($this->props['node']['id']);
			if (is_object($this->subunit)) {
				$ret = array_merge($ret, $this->subunit->getPathArray());
			}
            return $ret;
        }
                 
        function getPath($delimeter = '&gt;') {
		global $PATH_MAINPAGE_TITLE;	
            if (sizeof($path = $this->getPathArray()) > 0) {
				if (__PATH_MAINPAGE_VISIBLE && $path[0]['name'] != '/')
					$path = array_merge(array(array('name' => '/', 'title' => $PATH_MAINPAGE_TITLE[$this->props['lang']])), $path);
				if (!__PATH_MAINPAGE_VISIBLE && $path[0]['name'] == '/') {
					$path[0] = array();
				}
				foreach ($path as $k => $v) {
					if (isset($v['name']) && empty($v['ref'])) 
						$path[$k]['ref'] = $this->getTreeRef($v);
   	                if (isset($v['name']) && $v['name'] == '/')
                    	$path[$k]['title'] = $PATH_MAINPAGE_TITLE[$this->props['lang']];
                }
				$this->smarty->assign('pathitems', $path);
				$this->smarty->assign('delimeter', $delimeter);
                return $this->smarty->fetch('service/path.tpl');
            }
        }
        
		function getTitle() {
			$ret = '';
			if (!empty($this->props['node']))
       			$ret = $this->props['node']['title'];
            if ($this->subunit) {
				$ret = $this->subunit->getTitle();
			}
			return $ret;
		}
		
		function getComponentBody() {
			$ret = '';
			if (!empty($this->props['node']['module_id_name']) && $this->props['node']['module_id_name'] != 'tree') {
				if (is_object($this->subunit) && $this->subunit_service) {
					$ret = $this->subunit->getBody();
				} elseif ($this->subunit) {
					$ret = $GLOBALS['rtti']->callMethod($this->props['component'], $this->props['method'], $this->props['params']);
				}
				
            }
			return $ret;
		}
		
        function getBody() {
			$ret = '';
			if ($this->props['method'] == 'index')
				$ret .= $GLOBALS['rtti']->callMethod('tree', 'index', $this->props['uri'] != '/' ? array($this->props['uri']) : array());
			$ret .= $this->getComponentBody();
			return $ret;
        }
        
        /* search */
		function getSearchResultRef(&$a, $idName = '') {
			$lang = $this->props['lang'] == 'ru' ? '' : $this->props['lang'].'/';
			if ($a['name'] == '/') {
				$ref = $lang.$a['name'];
			} else {
				$ref = (trim($a['url']) != '' ? $a['url'] :  ($a['name'] != '' ? $GLOBALS['PRJ_REF'].'/'.$lang.$a['name'].(!empty($a['module_id']) ? '/' : '.htm') : '/'));
			}
            return $ref;
        } 
		 
        function getSearchResults($text) {
            return $this->getTableSearchResults($text, 'tree', "publish='on'", '', 'title');
        }
        
		/* Map */
		function getMapList($uri = 0) {
		global $PRJ_DIR;
            $a = $this->getNodes($uri);
			$block = strval($uri) == '0' ? '' :  '_sub';
			$props = $this->props;
			if (sizeof($a) > 0) {
    	        foreach ($a as $k => $i) {
					$a[$k]['sub'] = '';
					if (isset($i['module_id_name'])) {
						$unit = new Unit($i['module_id_name'], $this->props);
						$a[$k]['sub'] = $unit->getMap();
					}
                	$a[$k]['sub'] .= $this->getMapList($i['id']);
    	        }
			}
			$this->smarty->assign('items', $a);
			$this->smarty->assign('block', $block);
            return $this->smarty->fetch('service/map.tpl');
        }
		
        function getMap() {
            return $this->getMapList();
        }
    }

?>