<?php
	
namespace Fuga\Component\Form;

class Form {
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
				$this->items[$k]['value'] = $this->get('util')->_postVar($v['name']);
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
				$items = $this->get('connection')->getItems('frm_select_items', $sQuery);
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
		global $PRJ_DIR;
		$ret = '';
		if (count($this->items)) {
			foreach ($this->items as $k => $item) {
				$this->items[$k] = $this->parseItem($item);
				if (!empty($item['not_empty'])) $this->dbform['needed'] = true;
			}
			$params = array (
				'action' => $this->action,
				'dbform' => $this->dbform,
				'items' => $this->items,
				'frmMessage' => $this->message,
				'pass_postfix' => $this->pass_postfix,
			);
			if (empty($this->dbform['template'])) {
				$ret = $this->get('templating')->render('service/form.tpl', $params);
			} else {
				$ret = $this->get('templating')->render($this->dbform['template'], $params);
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
			if ($i['type'] == 'password' && $this->get('util')->_postVar($i['name']) != $this->get('util')->_postVar($i['name'].$this->pass_postfix)) {
				return false;
			}
		}
		return $this->getIncorrectFieldTitle() === null;
	}

	public function sendMail($params) {
		global $MAX_FILE_SIZE;
		$ret = array('', '');
		$fields = array();
		foreach ($this->items as $field){
			$value = $this->get('util')->_postVar($field['name']);
			if ($field['not_empty'] && empty($value)) {
				$ret[0] = 'error';
				$this->get('templating')->setParam('ftitle', $field['title']);
				$GLOBALS['tplvar_message'] = $params['text_not_inserted'];
				$ret[1] .= ($ret[1] ? '<br>' : '').$this->get('templating')->render('var:message');
			}
			if ($field['type'] == 'checkbox') {
				$value = (empty($value) ? 'нет' : 'да').'<br>';
			} elseif ($field['type'] == 'file' && is_array($_FILES) && isset($_FILES[$field['name']]) && $_FILES[$field['name']]['name'] != '') {
				$upfile = $_FILES[$field['name']];
				if ($upfile['name'] != '' && $upfile['size'] < $MAX_FILE_SIZE ){
					$this->get('mailer')->Attach( $upfile['tmp_name'], $upfile['type'], 'inline', $upfile['name']);	
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
				$fields[] = array('value' => $this->get('util')->_postVar('keystring'), 'title' => 'Код безопасности');
				$this->get('templating')->setParam('fields', $fields);
				$this->get('mailer')->send(
					$this->dbform['title'].' на сайте '.$_SERVER['SERVER_NAME'],
					$this->get('templating')->render('service/form.mail.tpl'),
					$this->email
				);	
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
	
	public function get($name) {
		global $container, $security;
		if ($name == 'container') {
			return $container;
		} elseif ($name == 'security') {
			return $security;
		} else {
			return $container->get($name);
		}
	}

}
