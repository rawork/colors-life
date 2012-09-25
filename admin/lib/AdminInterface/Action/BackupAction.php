<?php

namespace AdminInterface\Action;

class BackupAction extends Action {
	public function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	public function getFilter() {
		return $this->dataTable->getSeachSQL();
	}

	/* Кнопки управления записью */
	protected function getUpdateDelete($file) {
		global $THEME_REF;
		$ret = '<td width="1">'."\n";
		$ret .= '<a href="javascript:void(0);" onclick="this.blur();admin_menu.ShowMenu(this, [';
		if (empty($this->dataTable->params['noupdate']) || $this->get('security')->isAdmin()) {
			$ret .= "{'DEFAULT':true,'ICONCLASS':'btn_edit','TEXT':'Скачать','ONCLICK':'admin_menu.PopupHide(); javascript: window.location = \'".$file."\';'}";
		}
		if (empty($this->dataTable->params['nodelete']) || $this->get('security')->isAdmin()) {
			$ret .= ",{'ICONCLASS':'btn_delete','TEXT':'Удалить','ONCLICK':'admin_menu.PopupHide(); javascript: window.location = \'".$this->fullRef.'/backupdelete?file='.$file."\';'}";
		}
		$ret .= ']);" title="Действия" class="action context-button icon">'."\n";
		$ret .= '<img src="'.$THEME_REF.'/img/arr_down.gif" class="arrow" alt=""></a></td>'."\n";
		return $ret;
	}

	protected function getTopTableHeader() {
	global $THEME_REF;
		$ret = '<form id="frmGroupUpdate" name="frmGroupUpdate" action="'.$this->fullRef.'/groupedit'.($this->get('util')->_getVar('p_id') ? '?p_id='.$this->get('util')->_getVar('p_id') : '').'" method="post">';
		$ret .= $this->getTableHeader();
		$ret .= '<tr>';
		$ret .= '<th width="20%">Имя</th>';
		$ret .= '<th width="30%">Ссылка</th>';
		$ret .= '<th width="22%">Размер файла, MБ</th>';
		$ret .= '<th width="22%">Создан</th>';
		$ret .= '<th style="text-align:center;"><img alt="Действия" src="'.$THEME_REF.'/img/action_head.gif" border=0></th>';
		$ret .= '</tr>';
		return $ret;
	}

	protected function getTopTableFooter() {
		$ret = '</table>';
		return $ret;
	}

	protected function getMainBodyTable() {
		$ret = $this->getTopTableHeader();
		$dirname = $GLOBALS['PRJ_DIR'].'/app/backup/';
		$dir = @opendir($dirname);
		$files = array();
		while ($file = @readdir($dir))
		{
			$fullname = $dirname . $file;
			if ($file == "." || $file == ".." || @is_dir($fullname))
				continue;
			else if (@file_exists($fullname))
				$files[] = array ('name' => $fullname, 'name2' => $file,
					'type' => 0,
					'ext' => substr($file, strrpos($file, ".")), 'stat' => stat($fullname));
		}
		@closedir($dir);

		foreach ($files as $current) {
			if ($current['ext'] == '.gz') {
				$ret .= '<tr>';
				$tmp_ret = '';
				$link = str_replace($GLOBALS['PRJ_DIR'], '', $current['name']);
				$ret .= '<td>'.$current['name2'].'</td>';
				$ret .= '<td>'.str_replace($_SERVER['DOCUMENT_ROOT'], '', $current['name']).'</td>';
				$ret .= '<td>'.$this->get('util')->getSize($current['stat'][7]).'</td>';
				$ret .= '<td>'.date('d.m.Y H:i:s', $current['stat'][9]).'</td>';
				$ret .= $this->getUpdateDelete($link).'</tr>'."\n";
			}
		}
		$ret .= $this->getTopTableFooter();
		return $ret;
	}

	protected function formArchive() {
		$archiveReport = '';
		if ($archiveReport = $this->get('util')->_sessionVar('archiveReport')) {
			unset($_SESSION['archiveReport']);
		}
		$ret = '<div id="archive_info">'.$archiveReport.'</div>';
		$ret .= '<div id="fields_panel" class="panel">
<form  method="post" name="frmArchive" id="frmArchive" action="">
	<div class="ctlbtns">
	<input type="button" class="adm-btn" onClick="makeArchive();" value="Архивировать">
	</div>
</form>
</div>';
		return $ret;
	}

	public function getText() {
		$ret = '';
		$ret .= $this->formArchive();
		$ret .= $this->getMainBodyTable();
		return $ret;
	}

}
