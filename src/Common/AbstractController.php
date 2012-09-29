<?php

namespace Common;

use Exception\NotFoundHttpException;

abstract class AbstractController {
	
	public function get($name) 
	{
		global $container, $security;
		if ($name == 'container') {
			return $container;
		} elseif ($name == 'security') {
			return $security;
		} else {
			return $container->get($name);
		}
	}
	
	public function render($template, $params = array(), $silent = false) 
	{
		return $this->get('templating')->render($template, $params, $silent);
	}
	
	public function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }
	
}