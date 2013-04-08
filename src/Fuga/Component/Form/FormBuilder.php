<?php
	
namespace Fuga\Component\Form;

class FormBuilder {
	public $items;
	public $action;
	public $defense;
	public $message;
	private $password_postfix;
	private $form;
	private $email;

	public function __construct($action = '.', $form) {
		$this->message = array('', '');
		$this->form = $form;
		$this->action = $action;
		$this->pass_postfix = '_password_check';
		$this->form['needed'] = false;
		$this->defense = !empty($this->form['is_defense']);
		$this->form['submit_text'] = empty($form['submit_text']) ? 'Отправить' : $form['submit_text'];
		$this->email = empty($form['email']) ? $GLOBALS['ADMIN_EMAIL'] : $form['email'];
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

	private function parseItem($item) {
		switch ($item['type']) {
			case 'select':
				if (!empty($item['select_values'])) {
					$item['select_values'] = explode(';', $item['select_values']);
					foreach ($item['select_values'] as $k => $v) {
						if (!is_array($v)) {
							$item['select_values'][$k] = array();
							$item['select_values'][$k]['name'] = $v;
							$item['select_values'][$k]['value'] = $v;
						}
						if (!empty($item['value']) && $item['select_values'][$k]['value'] == $item['value']) {
							$item['select_values'][$k]['sel'] = ' selected';
						}
					}
				}
				if (!empty($item['select_table'])) {
					if (empty($item['select_name'])) {
						$item['select_name'] = 'name';
					}
					if (empty($item['select_value'])) {
						$item['select_value'] = 'id';
					}
					if (empty($item['select_order'])) {
						$item['select_order'] = $item['select_name'];
					}
					if (!empty($item['select_filter'])) {
						$item['select_filter'] = str_replace("`", "'",$item['select_filter']);
					}

					$sql = 'SELECT * FROM '.$item['select_table'].(!empty($item['select_filter']) ? ' WHERE '.$item['select_filter'] : '').' ORDER BY '.$item['select_order'];
					$stmt = $this->get('connection1')->prepare($sql);
					$stmt->execute();
					$items = $stmt->fetchAll();
					$item['select_values'] = array();
					foreach ($items as $item) {
						$citem = array('name' => $item[$item['select_name']], 'value' => $item[$item['select_value']]);
						if (!empty($item['value']) && $item['value'] == $item[$item['select_value']]) {
							$citem['sel'] = ' selected';
						}
						$item['select_values'][] = $citem;
					}
				}
				break;
			case 'enum':
				if (!empty($item['select_values'])) {
					$item['select_values'] = explode(',', $item['select_values']);
					foreach ($item['select_values'] as $k => $v) {
						if (!is_array($v)) {
							$item['select_values'][$k] = array();
							$item['select_values'][$k]['name'] = $v;
							$item['select_values'][$k]['value'] = $v;
						}
						if (!empty($item['value']) && $item['select_values'][$k]['value'] == $item['value']) {
							$item['select_values'][$k]['sel'] = ' selected';
						}
					}
				}
			break;	
			case 'string':
			// do something
		}
		return $item;
	}

	public function getText() {
		$ret = '';
		if (count($this->items)) {
			foreach ($this->items as &$item) {
				$item = $this->parseItem($item);
				if (!empty($item['not_empty'])) {
					$this->form['not_empty'] = true;
				}	
			}
			unset($item);
			$params = array (
				'action' => $this->action,
				'dbform' => $this->form,
				'items'  => $this->items,
				'frmMessage'   => $this->message,
				'pass_postfix' => $this->pass_postfix,
				'sessionName'  => session_name(),
				'sessionId'    => session_id(),
			);
			try {
				$ret = $this->get('templating')->render('form/'.$this->form['name'].'.tpl', $params);
			} catch (\Exception $e) {
				$ret = $this->get('templating')->render('form/basic.tpl', $params);
			}
		} else {
			$ret = 'Форма '.$this->name.' не содержит полей';
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
				$this->get('templating')->assign(array('ftitle', $field['title']));
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
				$this->get('templating')->assign(array('fields', $fields));
				$this->get('mailer')->send(
					$this->form['title'].' на сайте '.$_SERVER['SERVER_NAME'],
					$this->get('templating')->render('form/mail.tpl', compact('fields')),
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
