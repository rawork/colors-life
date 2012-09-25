<?php

namespace Common;
	
class Router {
	
	private $url;
	private $params = array();
	private $paths = array();
	
	public function __construct(){
		$this->url = $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Установка языка сайта 
	 */
	public function setLanguage() {
		if ($this->get('util')->_postVar('lang') && $this->get('util')->_sessionVar('lang') != $this->get('util')->_postVar('lang')) {
			$_SESSION['lang'] = $this->get('util')->_postVar('lang');
			header('location: '.$_SERVER['REQUEST_URI']);
		}
	}
	
	public function getPath($nativeUrl = null) {
		global $PRJ_REF;
		$url = $nativeUrl ?: $this->url;
		if (!isset($this->paths[$nativeUrl])) {
			//  Установка языка по части URL, например, /ru/about.htm или /catalog/ru/index.htm
			if ($this->isPublic($url)) {
				$languages = $this->get('connection')->getItems('config_languages', 'SELECT * FROM config_languages');
				$_SESSION['lang'] = 'ru';
				foreach ($languages as $language) {
					if (stristr($url, '/'.$language['name'].'/') || $this->get('util')->_getVar('lang') == $language['name']) {
						$_SESSION['lang'] = $language['name'];
						$url = str_replace('/'.$language['name'].'/', '/', $url);
						if (empty($url))
							$url = '/';
					}
				}
			}

			$this->setParam('lang', $this->get('util')->_sessionVar('lang', false, 'ru'));
			$urlParts = explode('#', $url);
			if (!empty($urlParts[1])) {
				$this->setParam('ajaxmethod', $urlParts[1]);
			}
			$urlParts = explode('?', $urlParts[0]);
			if (!empty($urlParts[1])) {
				$this->setParam('query', $urlParts[1]);
			}

			$this->paths[$nativeUrl] = preg_replace('/^'.$PRJ_REF.'\//', '/', $urlParts[0]); 
		}
		
		return $this->paths[$nativeUrl];
	}
	
	private function parseVariables(){
		$vars = array('_POST' => '_post', '_GET' => '_get', '_SESSION' => '_session', '_COOKIE' => '_cookie');
		foreach ($vars as $type => $name) {
			if (isset($$type)) {
				foreach ($$type as $key => $value){
					$methodName = $name.'Var';
					$this->params[$name][$key] = $this->get('util')->$methodName($key);
				}
			}
		}
	}

	public function isPublic($url) {
		return !preg_match('/^\/(admin|ajax|adminajax|notice)\//', $url);
	}
	
	public function isAdmin($url) {
		return preg_match('/^\/(admin)\//', $url);
	}
	
	/*
	 * Разбирает URL на части Раздел - Метод - Параметры
	 */
	public function parseURL($url = '/') {
		if (preg_match('/^\/[a-z0-9\-]+\/?[a-z0-9\-\.]*(\.htm)?$/', $url) || $url == '/') {
			$urlParts = array(
				'controller' => 'tree',
				'methodName' => 'index',
				'params' => array(),
				'type' => 'static'
			);
			$pathParts = pathinfo($url);
			if ($url == '/') {
				$urlParts['params'] = array('/');
				$urlParts['type'] = 'mainpage';
			} elseif ($pathParts['filename'] == $pathParts['basename']) {
				$urlParts['controller'] = $pathParts['basename'];
				$urlParts['type'] = 'dynamic';
			} elseif ($pathParts['dirname'] == '/') {
				$urlParts['params'] = array($pathParts['filename']);
				$urlParts['type'] = 'static';
			} else {
				$dirParts = explode('/', $pathParts['dirname']);
				$urlParts['controller'] = $dirParts[1];
				$method = explode('.', $pathParts['filename']);
				$urlParts['methodName'] = array_shift($method);
				$urlParts['params'] = $method;
				$urlParts['type'] = 'dynamic';
			}	
			return $urlParts;
		} else {
			return false;
		}
	}

	public function setParams($url = null){
		$url = $url ?: $this->url;
		$url = $this->getPath($url);
		if ($this->isPublic($url)) {
			$this->parseVariables();
			$urlParts = $this->parseURL($url);
			if (!$urlParts) {
				$this->setParam('error', '404');
			}
			$this->setParam('controller', $urlParts['controller']);
			$this->setParam('methodName', $urlParts['methodName']);
			$this->setParam('params', $urlParts['params']);
			$isError = false;
			$node = null;
			switch ($urlParts['type']) {
				case 'mainpage':
					$node = $this->get('container')->getItem('tree_tree', "name='/'");
					if (isset($node['module_id_name'])) {
						$this->setParam('controller', $node['module_id_name']);
					}	
					break;
				case 'static':
					$node = $this->get('container')->getItem('tree_tree', "name='".$urlParts['params'][0]."'");
					if (!$node) {
						$isError = true;
					}	
					break;
				case 'dynamic':
					$node = $this->get('container')->getItem('tree_tree', "name='".$this->getParam('controller')."'");
					if (!$node || !isset($node['module_id_name'])) {
						$isError = true;
					} else {
						$this->setParam('controller', $node['module_id_name']);
					}	
					break;
				default:
			}
			
			if ($node && !$isError) {
				$this->setParam('node', $node);
				$this->setParam('nodeId', $node['id']);
				$this->setParam('nodeName', $node['name']);
			} else {
				$this->setParam('error', '404');
			}
		} elseif ($this->isAdmin($url)) {
			$urlParts = explode('/', $url);
			if (!empty($urlParts[2])) {
				$this->setParam('state', $urlParts[2]);  
			} else {
				$this->setParam('state', 'content');
			}
			if (!empty($urlParts[3])) {
				$this->setParam('module', $urlParts[3]);  
			} else {
				$this->setParam('module', '');
			}
			if (!empty($urlParts[4])) {
				$this->setParam('table', $urlParts[4]);  
			} else {
				$this->setParam('table', '');
			}
			if (!empty($urlParts[5])) {
				$this->setParam('action', $urlParts[5]);  
			} else {
				$this->setParam('action', 'index');
			}
			if (!empty($urlParts[6])) {
				$this->setParam('id', $urlParts[6]);  
			} else {
				$this->setParam('id', 0);
			}
		} else {	
			$this->setParam('uri', $this->getPath($url));
		}
	}
	
	public function setParam($name, $value) {
		$this->params[$name] = $value; 
	}
	
	public function hasParam($name) {
		return !empty($this->params[$name]); 
	}
	
	public function getParam($name) {
		return $this->params[$name]; 
	}
	
	public function getController() {
		
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
