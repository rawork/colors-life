<?php
	
    class CForm {
        public $items;
        public $action;
		public $defense;
		public $message;
        private $password_postfix;
		private $dbform;
		private $email;

		public function __construct($action = '.', $frmItem) {
			$this->message = array('', '');
			$this->dbform = $frmItem;
            $this->action = $action;
            $this->pass_postfix = '_password_check';
			$this->dbform['needed'] = false;
			$this->defense = !empty($this->dbform['is_defense']);
		    $this->dbform['submit_text'] = empty($frmItem['submit_text']) ? 'Отправить' : $frmItem['submit_text'];
			$this->email = empty($frmItem['email']) ? $GLOBALS['ADMIN_EMAIL'] : $frmItem['email'];
        }
		
        public function fillGlobals() {
            foreach ($this->items as $k => $v) {
                if (empty($this->items[$k]['value'])) {
                    $this->items[$k]['value'] = CUtils::_postVar($v['name']);
                }
            }
        }
		
		public function fillValues(&$a) {
            for ($i = 0; $i < sizeof($this->items); $i++) {
                $name = $this->items[$i]['name'];
                if (!stristr($name, $this->pass_postfix)) {
                    if (!empty($a[$name])) {
                        $this->items[$i]['value'] = $a[$name];
                        if (stristr($name, 'password')) {
                            $this->items[$i + 1]['value'] = $this->items[$i]['value'];
                        }
                    }
                }
            }
        }
		
        private function parseItem($aItem) {
            switch ($aItem['type']) {
              case 'select':
                if (!empty($aItem['select_values'])) {
                    $aItem['select_values'] = explode(';', $aItem['select_values']);
					foreach ($aItem['select_values'] as $k => $v) {
						if (!is_array($v)) {
							$aItem['select_values'][$k] = array();
                            $aItem['select_values'][$k]['name'] = $v;
							$aItem['select_values'][$k]['value'] = $v;
                        }
                        if (!empty($aItem['value']) && $aItem['select_values'][$k]['value'] == $aItem['value']) {
                            $aItem['select_values'][$k]['sel'] = ' selected';
                        }
                    }
                }
                if (!empty($aItem['select_table'])) {
					if (empty($aItem['select_name'])) {
						$aItem['select_name'] = 'name';
					}
					if (empty($aItem['select_value'])) {
						$aItem['select_value'] = 'id';
					}
					if (empty($aItem['select_order'])) {
						$aItem['select_order'] = $aItem['select_name'];
					}
					if (!empty($aItem['select_filter'])) {
						$aItem['select_filter'] = str_replace("`", "'",$aItem['select_filter']);
					}

					$sQuery = 'SELECT * FROM '.$aItem['select_table'].(!empty($aItem['select_filter']) ? ' WHERE '.$aItem['select_filter'] : '').' ORDER BY '.$aItem['select_order'];
					$items = $GLOBALS['db']->getItems('frm_select_items', $sQuery);
					$aItem['select_values'] = array();
                    foreach ($items as $item) {
                        $citem = array('name' => $item[$aItem['select_name']], 'value' => $item[$aItem['select_value']]);
						if (!empty($aItem['value']) && $aItem['value'] == $item[$aItem['select_value']]) {
							$citem['sel'] = ' selected';
						}
                        $aItem['select_values'][] = $citem;
                    }
                }
                break;
			  case 'enum':
                if (!empty($aItem['select_values'])) {
                    $aItem['select_values'] = explode(';', $aItem['select_values']);
					foreach ($aItem['select_values'] as $k => $v) {
						if (!is_array($v)) {
							$aItem['select_values'][$k] = array();
                            $aItem['select_values'][$k]['name'] = $v;
							$aItem['select_values'][$k]['value'] = $v;
                        }
                        if (!empty($aItem['value']) && $aItem['select_values'][$k]['value'] == $aItem['value']) {
                            $aItem['select_values'][$k]['sel'] = ' selected';
                        }
                    }
                }
                break;	
              case 'string':
                // do something
            }
			return $aItem;
        }
		
        public function getText() {
		global $smarty, $PRJ_DIR;
            $ret = '';
            if (sizeof($this->items) > 0) {
	            foreach ($this->items as $k => $v) {
            	    $this->items[$k] = $this->parseItem($v);
					if (!empty($v['not_empty'])) $this->dbform['needed'] = true;
            	}
        	    $smarty->assign('action', $this->action);
				$smarty->assign('dbform', $this->dbform);
				$smarty->assign('items', $this->items);
				$smarty->assign('frmMessage', $this->message);
				$smarty->assign('pass_postfix', $this->pass_postfix);
				$smarty->assign('sess_name', session_name());
				$smarty->assign('sess_id', session_id());
				if (empty($this->dbform['template'])) {
					$ret = $smarty->fetch('service/form.tpl');
				} else {
					$ret = $smarty->fetch($PRJ_DIR.$this->dbform['template']);
				}
			} else {
				$ret = 'Пустая форма '.$this->name;
			}
			return $ret;
        }
        
		function getFieldValue($sName) {
            return isset($_POST[$sName]) ? addslashes($_POST[$sName]) : null;
        }
		
        public function getIncorrectFieldTitle() {
            foreach ($this->items as $i) {
                if (!empty($i['not_empty']) && !$this->getFieldValue($i['name'])) {
                    return $i['title'];
                }
            }
            return null;
        }
        
		public function isCorrect() {
            foreach ($this->items as $k => $i) {
                if ($i['type'] == 'password' && CUtils::_postVar($i['name']) != CUtils::_postVar($i['name'].$this->pass_postfix)) {
					return false;
                }
            }
            return $this->getIncorrectFieldTitle() === null;
        }
		
        public function sendMail($params) {
		global $smarty, $MAX_FILE_SIZE;
			inc_lib('libmail.php');
			$ret = array('', '');
			$msg = new Mail();
            $msg->From($GLOBALS['ADMIN_EMAIL']);
           	$msg->Subject($this->dbform['title'].' на сайте '.$_SERVER['SERVER_NAME']);
			$fields = array();
			foreach ($this->items as $k => $field){
				$value = CUtils::_postVar($field['name']);
				if ($field['not_empty'] && empty($value)) {
  				    $ret[0] = 'error';
					$smarty->assign('ftitle', $field['title']);
					$GLOBALS['tplvar_message'] = $params['text_not_inserted'];
					$ret[1] .= ($ret[1] ? '<br>' : '').$smarty->fetch('var:message');
				}
				if ($field['type'] == 'checkbox') {
					$value = (empty($value) ? 'нет' : 'да').'<br>';
				} elseif ($field['type'] == 'file' && is_array($_FILES) && isset($_FILES[$field['name']]) && $_FILES[$field['name']]['name'] != '') {
					$upfile = $_FILES[$field['name']];
					if ($upfile['name'] != '' && $upfile['size'] < $MAX_FILE_SIZE ){
  						$msg->AttachFile( $upfile['tmp_name'], $upfile['name'],  $upfile['type']);	
					}
					$value = $upfile['name'].' см. вложение<br>';
				} else {	
					$value = htmlspecialchars($value);
				}
				$fields[] = array('value' => $value, 'title' => $field['title']);
			}
			if (!empty($ret[1])) {
   	    		$ret[1] = '<div class="tree-error">'.$ret[1].'</div>';
   	    	} else {
   	    		if ($this->defense)
					$fields[] = array('value' => CUtils::_postVar('keystring'), 'title' => 'Код безопасности');
				$smarty->assign('fields', $fields);
				$msg->Html($smarty->fetch('service/form.mail.tpl'), 'windows-1251');
       			$msg->To(explode(',', $this->email));
   	    		$msg->Send();
   	    	}
            return $ret;
        }
        
		public function getSQLUpdate() {
            $ret = '';
            foreach ($this->items as $i) {
                if (!stristr($i['name'], $this->password_postfix)) {
                    $ret .= ($ret ? ', ' : '').$i['name']."='".$this->getFieldValue($i['name'])."'";
                }
            }
            return $ret;
        }
		
        public function getSQLWhere() {
            $ret = '';
            foreach ($this->items as $i) {
                if ($this->getFieldValue($i['name'])) {
                    $ret .= ($ret ? ' AND ' : '').$i['name']."='".$this->getFieldValue($i['name'])."'";
                }
            }
            return $ret;
        }
		
    }
	
    class CDBForm extends CForm {

		private $_oDBTable;

		public function __construct($oDBTable, $sAction = '.', $aFormFields = array()) {
            parent::__construct($sAction, $aFormFields);
			$this->_oDBTable = $oDBTable;
            $this->addFieldsFromDBTable($this->_oDBTable);
        }
		
        public function fillValuesById($iId) {
            if ($aValues = $this->_oDBTable->getItem(intval($iId))) {
                $this->fillValues($aValues);
            }
        }

		public function addFieldsFromDBTable($oTable, $sFieldPropertyName = 'form') {
            foreach ($oTable->fields as $aFieldProperties) {
                if (isset($aFieldProperties[$sFieldPropertyName])) {
                    $this->addItemFromDBTableField($aFieldProperties);
                }
            }
        }

		public function addItemFromDBTableField($aFieldProperties) {
            $aItem = array (
                'title'		=> $aFieldProperties['title'],
                'name'		=> $aFieldProperties['name'],
                'type'		=> $aFieldProperties['type'],
				'is_check'	=> !empty($aFieldProperties['is_check'])
            );
            
			$aItem['not_empty'] = !empty($aFieldProperties['not_empty']);
            
			if (stristr($aItem['type'], 'select')) {
                $aItem['type']			= 'select';
                $aItem['select_table']	= $aFieldProperties['l_table'];
                $aItem['select_name']	= $aFieldProperties['l_field'];
				if (isset($aFieldProperties['l_filter'])) {
					$aItem['select_filter']	= $aFieldProperties['l_filter'];
				}
            }
			
			if (stristr($aItem['type'], 'enum')) {
                $aItem['type']			= 'enum';
                $aItem['select_values']	= $aFieldProperties['select_values'];
            }
            
			$this->items[] = $aItem;
        }
    }
