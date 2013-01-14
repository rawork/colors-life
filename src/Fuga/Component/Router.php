<?php

namespace Fuga\Component;
	
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

			$this->paths[$nativeUrl] = $urlParts[0]; 
		}
		
		return $this->paths[$nativeUrl];
	}
	
	public function isPublic($url = null) {
		$url = $url ?: $this->url;
		return !preg_match('/^\/(admin|ajax|adminajax|notice|bundles)\//', $url);
	}
	
	public function isAdmin($url = null) {
		$url = $url ?: $this->url;
		return preg_match('/^\/(admin)\//', $url);
	}
	
	/**
	 * Разбирает URL на части /Controller/Action/Params
	 */
	public function parseURL($url = '/') {
		if (preg_match('/^\/[a-z0-9\-]+\/?[a-z0-9\-\.]*(\.htm)?$/', $url) || $url == '/') {
			$urlParts = array(
				'node' => '',
				'methodName' => 'index',
				'params' => array()
			);
			$pathParts = pathinfo($url);
			$dirParts = explode('/', $pathParts['dirname']);
			if ($pathParts['dirname'] == '/' && empty($pathParts['basename'])) {
				$urlParts['node'] = '/';
			} elseif ($pathParts['dirname'] == '/' && empty($pathParts['extension'])) {
				$urlParts['node'] = $pathParts['filename'];
			} elseif ($pathParts['dirname'] == '/' && $pathParts['extension'] == 'htm') {
				$urlParts['node'] = $pathParts['filename'];
			} elseif (count($dirParts) == 2) {
				if ($pathParts['extension'] != 'htm') {
					$urlParts['node'] = 'paraamnikudotaim'; // :)
				} else {
					$urlParts['node'] = $dirParts[1];
					$method = explode('.', $pathParts['filename']);
					$urlParts['methodName'] = array_shift($method);
					$urlParts['params'] = $method;
				}
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
			$urlParts = $this->parseURL($url);
			if (!$urlParts) {
				$this->setParam('error', '404');
			}
			$this->setParam('node', $urlParts['node']);
			$this->setParam('methodName', $urlParts['methodName']);
			$this->setParam('params', $urlParts['params']);
			
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
