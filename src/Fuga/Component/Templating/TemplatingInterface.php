<?php

namespace Fuga\Component\Templating;

interface TemplatingInterface {
	
	public function render(string $templateName, array $params = array(), bool $silent = false);
	
}