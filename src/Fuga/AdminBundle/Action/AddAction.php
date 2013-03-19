<?php

namespace Fuga\AdminBundle\Action;

class AddAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	/* Форма добавления */
	function getForm() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($this->get('util')->_postVar('utype')) {
				if ($lastId = $this->dataTable->insertGlobals()) {
					$path = $this->fullRef.'/edit/'.$lastId;
					$_SESSION['message'] = 'Добавлено';
				} else {
					$path = $this->fullRef.'/add';
					$_SESSION['message'] = 'Ошибка добавления';
				}
				header('location: '.$path);	
			} else {
				$this->messageAction($this->dataTable->insertGlobals() ? 'Добавлено' : 'Ошибка добавления');
			}
		}
		if ($text = $this->render('admin/components/'.$this->get('router')->getParam('module').'.'.$this->get('router')->getParam('table').'.tpl', array(), true)){
			return $text;
		} else {
			reset($this->dataTable->fields);
			$ret = '';
			$fields = '';
			foreach ($this->dataTable->fields as $field) {
				if (empty($field['readonly'])) {
					$vis = '';
					if ($this->dataTable->dbName() == 'table_field' && ($field['name'] == 'select_values' || $field['name'] == 'params')) {
						$vis = ' style="display:none;"';
					}
					$fields .= '<tr'.$vis.' id="add_'.$field['name'].'"><td style="width:180px"><strong>'.$field['title'].'</strong>'.$this->getHelpLink($field).$this->getTemplateName($field).'</td><td>';
					$ft = $this->dataTable->createFieldType($field);
					$fields .= $ft->getInput().'</td></tr>';
				}
			}
			$ret .= '<br><form enctype="multipart/form-data" method="post" name="frmInsert" id="frmInsert" action="'.$this->fullRef.'/add">';
			$ret .= '<input type="hidden" id="utype" name="utype" value="0">';
			$ret .= '<table class="table table-condensed">';
			$ret .= '<thead><tr>';
			$ret .= '<th>Новый элемент<a name=add></a></td>';
			$ret .= '<th></th></tr></thead>';
			$ret .= $fields;
			$ret .= '</table>
<input type="button" class="btn btn-success" onClick="preSubmit(\'frmInsert\', 0)" value="Сохранить">
<input type="button" class="btn" onClick="preSubmit(\'frmInsert\', 1)" value="Применить">
<input type="button" class="btn" onClick="window.location = \''.$this->fullRef.'\'" value="Отменить"></form>';
			return $ret;
		}
	}

	function getText() {
		$content = '';
		$links = array(
			array(
				'ref' => $this->fullRef,
				'name' => 'Список элементов'
			)
		);
		$content .= $this->getOperationsBar($links);
		$content .= $this->getForm();
		return $content;
	}

}
