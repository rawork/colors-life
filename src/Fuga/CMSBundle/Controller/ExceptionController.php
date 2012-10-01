<?php

namespace Fuga\CMSBundle\Controller;

class ExceptionController extends Controller {
	
	public function indexAction($status_code, $status_text) {
		header("HTTP/1.0 ".$status_code." Not Found");
		return $this->render('page.error.tpl', compact('status_code', 'status_text'));
	}
	
}
