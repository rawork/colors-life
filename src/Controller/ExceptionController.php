<?php

namespace Controller;

use Common\AbstractController;

class ExceptionController extends AbstractController {
	
	public function indexAction($status_code, $status_text) {
		header("HTTP/1.0 404 Not Found");
		return $this->render('page.error.tpl', compact('status_code', 'status_text'));
	}
	
}
