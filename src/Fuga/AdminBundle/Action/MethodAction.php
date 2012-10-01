<?php

namespace Fuga\AdminBundle\Action;

class MethodAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	
	/* Кнопки управления записью */
	private function getUpdateDelete($id) {
		global $THEME_REF;
		$ret = '';
//		$ret .= '<a href="javascript:void(0);" onclick="this.blur();admin_menu.ShowMenu(this, [';
//		if (empty($this->t->params['noupdate']) || $this->get('security')->isSuperuser()) {
//			$ret .= "{'DEFAULT':true,'ICONCLASS':'btn_edit','TEXT':'Изменить','ONCLICK':'admin_menu.PopupHide(); javascript: window.location = \'".$this->fullRef.'/edit/'.$id."\';'}";
//		}
//		if (empty($this->t->params['nodelete']) || $this->get('security')->isSuperuser()) {
//			$ret .= ",{'ICONCLASS':'btn_delete','TEXT':'Удалить','ONCLICK':'admin_menu.PopupHide(); javascript: startDelete(\'".$this->fullRef.'/delete/'.$id."\');'}";
//		}
//		if (empty($this->t->params['noinsert']) || $this->get('security')->isSuperuser()) {
//			$ret .= ",{'ICONCLASS':'btn_duplicate','TEXT':'Копировать','ONCLICK':'admin_menu.PopupHide(); javascript: showDuplicateSettings(\'".$this->fullRef.'/duplicate/'.$id."\');'}";
//		}
//		$ret .= ']);" title="Действия" class="action context-button icon">'."\n";
//		$ret .= '<img src="'.$THEME_REF.'/img/arr_down.gif" class="arrow" alt=""></a>';
		return $ret;
	}

	function getText() {
		$ret = '<form enctype="multipart/form-data" method="post" action="'.$this->fullRef.'/method">
<table class="table table-condensed">
<thead><tr>
<th>Название</th>
<th>Макет</th>
<th>Шаблон</th>
<th><i class="icon-align-justify"></i></th>
</tr></thead>';
		$module = $this->get('container')->getModule($this->get('router')->getParam('module'));
		$methods = $this->get('connection')->getItems('config_methods',
			'SELECT cm.*,tt.name as template_name FROM config_methods cm LEFT JOIN templates_templates tt ON cm.template_id=tt.id WHERE cm.module_id='.$module['id']);
		foreach ($methods as $method) {
			$ret .= '<tr><td class=left align=left width=250>'.$method['title'].' ('.$method['name'].')</td>
<td>'.$method['template_name'].'</td>
<td class="right">'.($method['template'] ? $method['template'] : 'нет').'</td>';
			$ret .= '<td>'.$this->getUpdateDelete($method['id']).'</td></tr>';
		} 
		$ret .= '</table></form>';
		return $ret;
	}
}
