<?php

namespace Fuga\Component;

use Fuga\Component\Exception\NotFoundHttpException;
	
class Router {
	
	private $container;
	private $url;
	private $params = array();
	private $paths = array();
	private $locales = array(array('name' => 'ru'));
	
	public function __construct($container){
		$this->container = $container;
		$this->url = $_SERVER['REQUEST_URI'];
	}
	
	public function setLocale() {
		$locale = $this->container->get('util')->_postVar('locale');
		if ( $locale
			&& $this->container->get('util')->_sessionVar('locale', false, 'ru') != $locale) {
			$_SESSION['locale'] = $locale;
			header('location: '.$_SERVER['REQUEST_URI']);
		}
	}
	
	public function getLocales() {
		return $this->locales;
	}
	
	public function getPath($nativeUrl = null) {
		$url = $nativeUrl ?: $this->url;
		if (!isset($this->paths[$nativeUrl])) {
			$_SESSION['locale'] = 'ru';
			if ($this->isPublic($url)) {
				foreach ($this->getLocales() as $locale) {
					if (stristr($url, DIRECTORY_SEPARATOR.$locale['name'].DIRECTORY_SEPARATOR) 
						|| $this->container->get('util')->_getVar('locale') == $locale['name']) 
				    {
						$_SESSION['locale'] = $locale['name'];
						$url = str_replace(DIRECTORY_SEPARATOR.$locale['name'].DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $url);
						if (!$url)
							$url = DIRECTORY_SEPARATOR;
					}
				}
			}

			$this->setParam('locale', $this->container->get('util')->_sessionVar('locale', false, 'ru'));
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
	public function getRoute($url = '/') {
		if ('/' == $url) {
			return array(
				'node' => '/',
				'action' => 'index',
				'params' => array()
			);
		} elseif (substr($url, -1) == '/') {
			$url = substr($url, 0, strlen($url)-1);
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".$url);
			exit();
		} elseif (preg_match('/^(\/[a-z0-9\-]+)+$/', $url)) {
			$path = explode('/', $url);
			array_shift($path);
			$node = array_shift($path);
			$action = !$path || is_numeric($path[0]) ? 'index' :array_shift($path);
			$params = $path;
			return array(
				'node' => $node,
				'action' => $action,
				'params' => $params
			);
		} elseif (preg_match('/^\/[a-z0-9\-]+\/?[a-z0-9\-\.]*(\.htm)?$/', $url)) {
			$url = str_replace('.htm', '', $url);
			$url = str_replace('/index.', '/', $url);
			$url = str_replace('.', '/', $url);
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".$url);
			exit();
		} else {
			throw new NotFoundHttpException('Несуществующая страница');
		}
	}

	public function setParams($url = null){
		$url = $url ?: $this->url;
		$url = $this->getPath($url);
		if ($this->isPublic($url)) {
			$route = $this->getRoute($url);
			$this->setParam('node', $route['node']);
			$this->setParam('action', $route['action']);
			$this->setParam('params', $route['params']);
			
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
	
}
