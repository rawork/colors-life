<?php

    inc_lib('components/Unit.php');
    class FormsUnit extends Unit {
        function __construct($props = array()) {
            parent::__construct('forms', $props);
        }
        
		public function getForm($query = 'id=0', $params = array()){
			$ret = '';
			$frmItem = $GLOBALS['rtti']->getItem('forms_forms', $query);
			$tbl = !empty($params['table']) ? $params['table'] : '';
			if (sizeof($frmItem) > 0) {
				$frmItem['fields'] = $GLOBALS['rtti']->getItems('forms_fields', 'form_id='.$frmItem['id']);
				inc_lib('tools/CForm.php');
				$frmObject = new CForm('', $frmItem);
				$frmObject->items = $frmItem['fields'];
				$frmObject->message = $this->processForm($frmObject, $tbl);
				if ($frmObject->message[0] == 'error')
					$frmObject->fillGlobals();
				$ret .= $frmObject->getText();
			}
			return $ret;
		}
		
		private function processForm($frmObject, $tbl = '') {
			$ret = array('', '');
			if (CUtils::_postVar('submited')) {
				if($frmObject->defense && CUtils::_sessionVar('c_sec_code') != md5(CUtils::_postVar('securecode').__CAPTCHA_HASH)){
					$ret[0] = 'error';
					$ret[1] = $this->dbparams['no_antispam'];
				} else {
					$ret = $frmObject->sendMail($this->dbparams);
					if (empty($ret[0])){
						$ret[0] = 'accept';
						$ret[1] = $this->dbparams['text_inserted'];
						if ($tbl)
							$GLOBALS['rtti']->addGlobalItem($tbl);
					}
				}
				unset($_SESSION['captcha_keystring']);
			}
			return $ret;
		}
		
		public function getBody() {
			return $this->getForm('dir_id='.$this->props['dir_id']);
        }
    }

?>