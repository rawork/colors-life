<?php

namespace Fuga\Component;

class Templating {
	
	private $engine;
	private $assignMethod;
	private $renderMethod;
	private $basePath		= '/app/Resources/views/';
	private $baseCachePath	= '/app/cache/smarty/';
	private $realPath		= '';
	
	public function __construct($engine, $options = array()) {
		global $PRJ_DIR;
		$this->engine = $engine;
		$this->realPath = $PRJ_DIR.$this->basePath;
		$this->engine->template_dir = $PRJ_DIR.$this->basePath;
		$this->engine->compile_dir = $PRJ_DIR.$this->baseCachePath;
		$this->engine->compile_check = false;
		$this->engine->debugging = false;
		$this->assignMethod = 'assign';
		$this->renderMethod = 'fetch';
		$this->setOptions($options);
	}
	
	public function setOptions($options = array()) {
		if (isset($options['assignMethod'])) {
			$this->assignMethod = $options['assignMethod'];
		}
		if (isset($options['renderMethod'])) {
			$this->renderMethod = $options['renderMethod'];
		}
	}
	
	public function setParams($params = array()) {
		$method = $this->assignMethod;
		foreach ($params as $paramName => $paramValue) {
			$this->engine->$method($paramName, $paramValue);
		}
	}
	
	public function setParam($paramName, $paramValue) {
		$method = $this->assignMethod;
		$this->engine->$method($paramName, $paramValue);
	}
	
	public function render($template, $params = array(), $silent = false) {
		if (empty($template)) {
			throw new \Exception('Шаблон без названия');
		}
		$template = str_replace($this->realPath, '', $template);
		$template = str_replace($this->basePath, '', $template);
		if ($this->exists($template)) {
			$method = $this->renderMethod;
			$this->setParams($params);
			return $this->engine->$method($template);
		} elseif ($silent) {
			return false;
		} else {	
			throw new \Exception('Несуществующий шаблон "'.$template.'"');
		}
	}
	
	public function exists($template) {
		return file_exists($this->realPath.$template);
	}
	
}

