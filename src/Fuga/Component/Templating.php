<?php

namespace Fuga\Component;

class Templating {
	
	private $engine;
	private $assignMethod;
	private $renderMethod;
	private $basePath = '/app/Resources/views/';
	
	public function __construct($engine, $options = array()) {
		$this->engine = $engine;
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
	
	public function render($template, $params = array(), $silent = false) {
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
		global $PRJ_DIR;
		return file_exists($PRJ_DIR.$this->basePath.$template);
	}
	
}

