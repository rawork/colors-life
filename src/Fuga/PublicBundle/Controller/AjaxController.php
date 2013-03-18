<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;

class AjaxController extends Controller {	
	
	public function voteProcess($voteName, $formData = null) {
		$elements = null;
		if ($formData) {
			parse_str($formData, $elements);
		}
		return json_encode(array('content' => $this->getManager('Fuga:Common:Vote')->getResult($voteName, $elements)));
	}                                                                       
                                                                                
}
