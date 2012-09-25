<?php

namespace AdminInterface\Action;

class AddAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	/* Форма добавления */
	function getSInsert() {
	global $PRJ_DIR, $THEME_REF;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($this->get('util')->_postVar('utype')) {
				if ($this->dataTable->insertGlobals()) {
					$path = $this->fullRef.'/edit/'.$this->get('connection')->getInsertID();
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
		if (file_exists($PRJ_DIR.'/app/Resources/views/admin/components/'.$this->get('router')->getParam('module').'.'.$this->get('router')->getParam('table').'.tpl')){
			return $this->get('smarty') ->fetch('admin/components/'.$this->get('router')->getParam('module').'.'.$this->get('router')->getParam('table').'.tpl');
		} else {
			reset($this->dataTable->fields);
			$ret = '';
			$fields = '';
			foreach ($this->dataTable->fields as $k => $v) {
				if (empty($v['readonly'])) {
					$vis = '';
					if ($this->dataTable->getDBTableName() == 'table_attributes' && ($v['name'] == 'select_values' || $v['name'] == 'params')) {
						$vis = ' style="display:none;"';
					}
					$fields .= '<tr'.$vis.' id="add_'.$v['name'].'" bgcolor="#fafafa"><td class="left" width="180" align="left"><b>'.$v['title'].'</b>'.$this->getHelpLink($k, $v).$this->getTemplateName($v).'</td><td class="right">';
					$ft = $this->dataTable->createFieldType($v);
					$fields .= $ft->getInput().'</td></tr>'."\n";
				}
			}
			$ret .= '<form enctype="multipart/form-data" method="post" name="frmInsert" id="frmInsert" action="'.$this->fullRef.'/add">';
			$ret .= '<input type="hidden" id="utype" name="utype" value="0">';
			$ret .= $this->getTableHeader();
			$ret .= '<tr">'."\n";
			$ret .= '<td><b>Добавить</b><a name=add></a><br><img src="'.$THEME_REF.'/img/0.gif" width="150" height="1"></td>'."\n";
			$ret .= '<td><div class="empty"></div></td></tr>'."\n";
			$ret .= $fields;
			$ret .= '</table><div class="ctlbtns"><input type="button" class="adm-btn" onClick="preSubmit(\'frmInsert\', 1)" value="Применить"><input type="button" class="adm-btn" onClick="preSubmit(\'frmInsert\', 0)" value="Сохранить"><input type="button" class="adm-btn" onClick="window.location = \''.(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $this->fullRef).'\'" value="Отменить"></div></form>'."\n";
			return $ret;
		}
	}

	function getText() {
		$ret = '';
		$links = array(
			array(
				'ref' => $this->fullRef,
				'name' => 'Список элементов'
			)
		);
		$ret .= $this->getOperationsBar($links);
		$ret .= $this->getSInsert();
		return $ret;
	}

}
