<?php

namespace AdminInterface\Action;

class GroupeditAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$ids_array = array();
		foreach ($_POST as $k => $req_item) {
			if (stristr($k, 'cng'))
				$ids_array[] = intval($req_item);
		}
		if (!$ids_array) {
			$this->messageAction($this->dataTable->group_update() ? 'Обновлено' : 'Ошибка обновления записей');
		}
		$ids_array = array();
		foreach ($_POST as $k => $req_item) {
			if (stristr($k, 'cng'))
				$ids_array[] = intval($req_item);
		}
		$ret = '';
		$fields = '';
		foreach ($ids_array as $id) {
			if ($a = $this->dataTable->getItem($id)) {
				$fields = '';
				foreach ($this->dataTable->fields as $k => $v) {
					$ft = $this->dataTable->createFieldType($v, $a);
					$fields .= '<tr><td class="left" align="left" width="150"><b>'.$v['title'].'</strong>'.$this->getHelpLink($k, $v).$this->getTemplateName($v).'</td><td class="right">';
					if (!empty($v['readonly'])) {
						$fields .= $ft->getStatic();
					} else {
						$fields .= $ft->getInput('', $ft->getName().$id);
					}
					$fields .= '</td></tr>'."\n";
				}
				$ret .= '<tr>'."\n";
				$ret .= '<th>Изменить</th>'."\n";
				$ret .= '<th>ID: '.$a['id'].'</th></tr>'."\n";
				$ret .= $fields;
			}
		}
		if ($ret) {
			$links = array(
				array(
					'ref' => $this->fullRef,
					'name' => 'Список элементов'
				)
			);
			$ret = $this->getOperationsBar($links).$this->getTableHeader().$ret;
			$ret = '<form enctype="multipart/form-data" method="post" name="frmInsert" id="frmInsert" action="'.$this->fullRef.'/groupedit"><input type="hidden" name="ids" value="'.implode(',', $ids_array).'">'.$ret;
			$ret .= '</table><div class="ctlbtns"><input class="adm-btn" type="button" onClick="preSubmit(\'frmInsert\')" value="Сохранить"></div></form>';
		}
		return $ret;
	}

}
