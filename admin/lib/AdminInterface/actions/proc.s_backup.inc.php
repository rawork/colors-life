 <?php

inc_lib('AdminInterface/actions/UnitAdminBody.php');

class s_backupUnitAdminBody extends UnitAdminBody {
	public function __construct(&$unitAdminInterface) {
		parent::__construct($unitAdminInterface);
	}

	public function getFilter() {
		return $this->t->getSeachSQL();
	}

	/* Кнопки управления записью */
	protected function getUpdateDelete($file) {
	global $THEME_REF;
		global $auth;
		$ret = '<td width="1">'."\n";
		$ret .= '<a href="javascript:void(0);" onclick="this.blur();admin_menu.ShowMenu(this, [';
		if (empty($this->t->props['noupdate']) || $auth->isAdmin()) {
			$ret .= "{'DEFAULT':true,'ICONCLASS':'btn_edit','TEXT':'Скачать','ONCLICK':'admin_menu.PopupHide(); javascript: window.location = \'".$file."\';'}";
		}
		if (empty($this->t->props['nodelete']) || $auth->isAdmin()) {
			$ret .= ",{'ICONCLASS':'btn_delete','TEXT':'Удалить','ONCLICK':'admin_menu.PopupHide(); javascript: window.location = \'".$this->fullRef.'&amp;action=backup_delete&amp;file='.$file."\';'}";
		}
		$ret .= ']);" title="Действия" class="action context-button icon">'."\n";
		$ret .= '<img src="'.$THEME_REF.'/img/arr_down.gif" class="arrow" alt=""></a></td>'."\n";
		return $ret;
	}

	protected function getTopTableHeader() {
	global $THEME_REF;
		$ret = '<form id="frmGroupUpdate" name="frmGroupUpdate" action="'.$this->fullRef.'&action=group_update'.(CUtils::_getVar('p_id') ? '&p_id='.CUtils::_getVar('p_id') : '').'" method="post">';
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
		$dirname = $GLOBALS['PRJ_DIR'].'/admin/backup/';
		//var_dump($dirname);
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
			//var_dump($current['ext']);
			if ($current['ext'] == '.gz') {
				$ret .= '<tr>';
				$tmp_ret = '';
				$link = str_replace($GLOBALS['PRJ_DIR'], '', $current['name']);
				$ret .= '<td>'.$current['name2'].'</td>';
				$ret .= '<td>'.str_replace($_SERVER['DOCUMENT_ROOT'], '', $current['name']).'</td>';
				$ret .= '<td>'.CUtils::getSize($current['stat'][7]).'</td>';
				$ret .= '<td>'.date('d.m.Y H:i:s', $current['stat'][9]).'</td>';
				$ret .= $this->getUpdateDelete($link).'</tr>'."\n";
			}
		}
		$ret .= $this->getTopTableFooter();
		return $ret;
	}

	/* Управление таблицей */
	protected function getTableOperations() {
		$ret = '<table align="center" cellpadding="0" cellspacing="0" border="0" class="contextmenu2">
<tr class="top">
<td class="left"><div class="empty"></div></td>
<td><div class="empty"></div></td>
<td class="right"><div class="empty"></div></td>
</tr>
<tr>
<td class="left"><div class="empty"></div></td>
<td class="content">
<table cellpadding="2" cellspacing="0" border="0">
<tr>';
		if (!$this->p['noinsert']) {
		$ret .= '<td><div class="section-separator first"></div></td>
<td align="center"><a href="'.$this->fullRef.'&amp;action=s_insert'.(CUtils::_getVar('p_id') ? '&p_id='.CUtils::_getVar('p_id') : '').'" class="context-button">Добавить запись</a></td>';
		}

		$ret .= '<td><div class="section-separator"></div></td>
<td align="center"><a href="'.$this->fullRef.'&amp;action=create" class="context-button">Создать таблицу</a></td>';
$ret .= '<td><div class="section-separator"></div></td>
<td align="center"><a href="'.$this->fullRef.'&amp;action=alter" class="context-button">Обновить таблицу</a></td>';
		$ret .= '</tr>
</table>
</td>
<td class="right"><div class="empty"></div></td>
</tr>
<tr class="bottom">
<td class="left"><div class="empty"></div></td>
<td><div class="empty"></div></td>
<td class="right"><div class="empty"></div></td>
</tr>

<tr class="bottom-all">
<td class="left"><div class="empty"></div></td>
<td><div class="empty"></div></td>
<td class="right"><div class="empty"></div></td>
</tr>
</table><br>';
		return $ret;
	}

	protected function formArchive() {
		$ret = '<div id="archive_info"></div>';
		$ret .= '<div id="fields_panel" class="panel">
<form  method="post" name="frmArchive" id="frmArchive" action="">
	<!--<table width="100%" cellspacing="0" class="tprops">
	<tr>
		<td><h4>Настройки</h4>
		<table class="tfields" align="center" width="720" border="0" cellpadding="2" cellspacing="0">
			<tr>
			<td>Название поля</td>
			<td><input type="text" name="title" value="" /></td>
			</tr>
		</table></td>
	</tr>
	</table>-->
	<div class="ctlbtns">
	<input type="button" class="adm-btn" onClick="makeArchive(xajax.getFormValues(\'frmArchive\'));" value="Архивировать">
	</div>
</form>
</div>';
		return $ret;
	}

	public function getText() {
		$ret = '';
		//$ret .= $this->getTableOperations();
		$ret .= $this->formArchive();
		$ret .= $this->getMainBodyTable();
		return $ret;
	}

}
