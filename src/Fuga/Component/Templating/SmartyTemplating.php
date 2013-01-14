<?php

namespace Fuga\Component\Templating;

class SmartyTemplating implements TemplatingInterface {
	
	private $engine;
	private $basePath		= '/app/Resources/views/';
	private $baseCachePath	= '/app/cache/smarty/';
	private $realPath		= '';
	
	public function __construct() {
		global $PRJ_DIR;
		$this->engine = new \Smarty();
		$this->realPath = $PRJ_DIR.$this->basePath;
		$this->engine->template_dir = $PRJ_DIR.$this->basePath;
		$this->engine->compile_dir = $PRJ_DIR.$this->baseCachePath;
		$this->engine->compile_check = false;
		$this->engine->debugging = false;
	}
	
	public function assign($params) {
		foreach ($params as $paramName => $paramValue) {
			$this->engine->assign($paramName, $paramValue);
		}
	}
	
	public function render($template, $params = array(), $silent = false) {
		if (empty($template)) {
			throw new \Exception('Для обработки передан шаблон без названия');
		}
		$template = str_replace(array($this->realPath, $this->basePath), '', $template);
		if ($this->exists($template)) {
			$this->assign($params);
			return $this->engine->fetch($template);
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
