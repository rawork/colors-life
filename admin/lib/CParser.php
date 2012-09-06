<?php
	
	class CParser {
		protected $url;
		protected $method = false;
		protected $props = array();
		public function __construct(){
			$this->url = $_SERVER['REQUEST_URI'];
		}
	
		protected function parseVariables(){
			$vars = array(array('_POST', '_post'),array('_GET', '_get'),array('_SESSION', '_session'),array('_COOKIE', '_cookie'));
			foreach ($vars as $v) {
				if (isset($$v['0'])) {
					foreach ($$v['0'] as $key => $value){
						$func = $v['1'].'Var';
						$this->props[$v['1']][$key] = $func($key);
					}
				}
			}
		}
		
		protected function checkURL($uri) {
			return substr($uri, 0, 6) != '/admin' && $uri != '/secureimage.php' && $uri != '/procajax.php';
		}
	
		public function getURLProps($url = ''){
		global $PRJ_REF;
			$uri = empty($url) ? $this->url : $url;
			$this->props['url'] = $uri;
			if (!stristr($uri, '/admin')) {
				$langs = $GLOBALS['db']->getItems('config_languages', 'SELECT * FROM config_languages');
				$findlang = false;
				foreach ($langs as $l) {
					if (stristr($uri, '/'.$l['name'].'/') || CUtils::_getVar('lang') == $l['name']) {
						$_SESSION['lang'] = $l['name'];
						$uri = str_replace('/'.$l['name'].'/', '/', $uri);
						$findlang = true;
						if (empty($uri))
							$uri = '/';
					}
				}
				if (!$findlang) $_SESSION['lang'] = 'ru';
			}
			$this->parseVariables();
			$this->props['lang'] = CUtils::_sessionVar('lang', false, 'ru');
			$uri = str_replace(stristr($uri, '#'), '', $uri);
			$uri = str_replace('?'.$_SERVER['QUERY_STRING'], '', $uri);
			$clear_uri = $uri = str_replace($PRJ_REF.'/','/',$uri);
			if ($this->checkURL($clear_uri)) {
				$url_parts = $GLOBALS['rtti']->parseURL($clear_uri);
				if (!$url_parts) {
					$this->props['error'] = '404';
				}
				$this->props['component'] = $url_parts['cname'];
				$this->props['method'] = $url_parts['mname'];
				$this->props['params'] = $url_parts['params'];
				$url_error = false;
				if ($clear_uri == '/') {
					//echo '1';
					$dir = $GLOBALS['rtti']->getItem('tree_tree', "name='/'");
					if (isset($dir['module_id_name']))
						$this->props['component'] = $dir['module_id_name'];
				} elseif ($clear_uri == '/'.$this->props['method'].'.htm' && $dir = $GLOBALS['rtti']->getItem('tree_tree', "name='".$this->props['method']."'")) {
					//echo '2';
					$this->props['params'] = array($this->props['method']);
					$this->props['method'] = 'index';
				} elseif (sizeof(explode('/', $clear_uri)) == 2) {
					//echo '2.2';
					$dir = $GLOBALS['rtti']->getItem('tree_tree', "name='/'");
					if (!isset($dir['module_id_name']))
						$url_error = true;
					else
						$this->props['component'] = $dir['module_id_name'];
				} else {
					//echo '3';
					$dir = $GLOBALS['rtti']->getItem('tree_tree', "name='".$this->props['component']."'");
					if (!isset($dir['module_id_name']))
						$url_error = true;
					else
						$this->props['component'] = $dir['module_id_name'];
				}
				if (is_array($dir) && !$url_error) {
					$this->props['node'] = $dir;
					$this->props['dir_id'] = $dir['id'];
					$this->props['dir_uri'] = $this->props['uri'] = $dir['name'];
				} else {
					$this->props['error'] = '404';
				}
			} else {
				$this->props['uri'] = $clear_uri;
			}
			return $this->props;	
		}
	}
