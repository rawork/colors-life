<?php
	
namespace AdminInterface\Action;

use Common\Paginator;
	
class IndexAction extends Action {

	private $showGroupSubmit	= false;
	private $paginator	= null;
	private $elementsIds		= array();
	protected $rowPerPage		= 25;

	function __construct(&$adminController) {
		parent::__construct($adminController);
		$this->rowPerPage = 
			$this->get('util')->_sessionVar($this->dataTable->getDBTableName().'_rpp', true, 0) > 0 
			? 
			$this->get('util')->_sessionVar($this->dataTable->getDBTableName().'_rpp', true, 0) 
			: 
			$this->dataTable->params['rpp'];
		$this->paginator = $this->get('paginator');
		$this->paginator->paginate($this->dataTable, $this->searchRef.'?page=###', $this->search_sql, $this->rowPerPage, $this->get('util')->_getVar('page', true, 1), 20);
	}

	/* Кнопки управления записью */
	private function _getUpdateDelete($id) {
		global $THEME_REF;
		$ret = '<td width="1">'."\n";
		$ret .= '<a href="javascript:void(0);" onclick="this.blur();admin_menu.ShowMenu(this, [';
		if (empty($this->dataTable->params['noupdate']) || $this->get('security')->isSuperuser()) {
			$ret .= "{'DEFAULT':true,'ICONCLASS':'btn_edit','TEXT':'Изменить','ONCLICK':'admin_menu.PopupHide(); javascript: window.location = \'".$this->fullRef.'/edit/'.$id."\';'}";
		}
		if (empty($this->dataTable->params['nodelete']) || $this->get('security')->isSuperuser()) {
			$ret .= ",{'ICONCLASS':'btn_delete','TEXT':'Удалить','ONCLICK':'admin_menu.PopupHide(); javascript: startDelete(\'".$this->fullRef.'/delete/'.$id."\');'}";
		}
		if (empty($this->dataTable->params['noinsert']) || $this->get('security')->isSuperuser()) {
			$ret .= ",{'ICONCLASS':'btn_duplicate','TEXT':'Копировать','ONCLICK':'admin_menu.PopupHide(); javascript: showDuplicateSettings(\'".$this->fullRef.'/duplicate/'.$id."\');'}";
		}
		$ret .= ']);" title="Действия" class="action context-button icon">'."\n";
		$ret .= '<img src="'.$THEME_REF.'/img/arr_down.gif" class="arrow" alt=""></a></td>'."\n";
		return $ret;
	}

	private function _getTopTableHeader() {
		global $THEME_REF;
		$ret = '<form id="frmGroupUpdate" name="frmGroupUpdate" action="'.$this->baseRef.'/groupedit" method="post">';
		$ret .= $this->getTableHeader();
		$ret .= '<tr>';
		$ret .= '<th width="1%" style="text-align:center;"><input type="checkbox" name="stateall" onClick="setStateAll(this);"></th>';
		$ret .= '<th width="1%">ID</th>';
		foreach ($this->dataTable->fields as $aField) {
			if (!empty($aField['width'])) {
				$ret .= '<th width="'.$aField['width'].'">'.$aField['title'].'</th>';
			}
		}
		if ($this->dataTable->params['show_credate']) {
			$ret .= '<th width="10%">Дата создания</th>';
		}
		$ret .= '<th style="text-align:center;"><img alt="Действия" src="'.$THEME_REF.'/img/action_head.gif" border=0></th>';
		$ret .= '</tr>';
		return $ret;
	}

	private function _getTopTableFooter() {
		$sTableFooter = '</table>';
		return $sTableFooter;
	}

	private function _getTopTableControlPanel() {
		global $THEME_REF;
		$sTableControlPanel = '<div class="ctlbtns"><table class="contextmenu3" cellspacing="0" cellpadding="3" border="0"><tr>';
		if (!empty($this->showGroupSubmit)) {
			$sTableControlPanel .= '<td><a onclick="document.frmGroupUpdate.submit();return false;" class="context-button" title="Сохранить"><img border="0" src="'.$THEME_REF.'/img/icons/icon_save.gif"></a></td>';
		}
		$sTableControlPanel .= '<td>с отмеченными:&nbsp;</td><td><a class="context-button" onclick="startGroupUpdate(\''.$this->fullRef.'\');return false;" title="Редактировать"><img border="0" src="'.$THEME_REF.'/img/icons/icon_edit.gif"></a></td><td><a class="context-button" onclick="startGroupDelete(\''.$this->fullRef.'\');return false;" title="Удалить"><img border="0" src="'.$THEME_REF.'/img/icons/icon_delete.gif"></a></td>';
		if (empty($this->dataTable->params['is_view'])) {
			$rpps = array(10,25,50,100,200);
			$sTableControlPanel .= '<td>&nbsp;&nbsp;Показывать записей:<input type="hidden" id="ref" name="ref" value="'.$this->fullRef.'"> <select name="rpp" onChange="updateRpp(this, \''.$this->dataTable->getDBTableName().'\')">';
			foreach ($rpps as $rpp) {
				$sTableControlPanel .= '<option value="'.$rpp.'"'.($this->rowPerPage == $rpp ? ' selected' : '').'>'.$rpp.'</option>';
			}
			$sTableControlPanel .= '</select></td>';
		}
		$sEntityIds = join(',', $this->elementsIds);
		$sTableControlPanel .= <<<EOD
</tr>
</table>
</div>
<input type="hidden" name="ids" value="$sEntityIds"></form>
EOD;
		return $sTableControlPanel;
	}

	private function _getMainBodyTable() {
		$sTableHtml = $this->paginator->getText().$this->_getTopTableHeader();

		$this->dataTable->select(
			array (
				'where' => $this->search_sql,
				'limit' => $this->paginator->limit
			)
		);
		while ($aEntity = $this->dataTable->getNextArray(false)) {
			$this->elementsIds[] = $aEntity['id'];
			$sTableHtml .= '<tr>';
			$sTableHtml .= '<td width="1%"><input type="checkbox" class="cng" onClick="setStateOne(this)" name="cng'.$aEntity['id'].'" value="'.$aEntity['id'].'"></td><td width="1%">'.$aEntity['id'].'</td>';
			reset($this->dataTable->fields);
			foreach ($this->dataTable->fields as $k => $aProperties) {
				if (!empty($aProperties['width'])) {
					$ft = $this->dataTable->createFieldType($aProperties, $aEntity);
					$sTableHtml .= '<td>';
					$sFieldHtml = '';
					if (!empty($aProperties['group_update']) && empty($aProperties['readonly'])) {
						$sFieldHtml .= $ft->getGroupInput();
						$this->showGroupSubmit = true;
					} else {
						$sFieldHtml .= $ft->getStatic();
					}
					$sTableHtml .= ($sFieldHtml ? $sFieldHtml : '&nbsp;').'</td>'."\n";
				}
			}
			if ( $this->dataTable->params['show_credate'] ) {
				$sTableHtml .= '<td>'.$aEntity['credate'].'</td>'."\n";
			}
			$sTableHtml .= $this->_getUpdateDelete($aEntity['id']).'</tr>'."\n";
		}
		$sTableHtml .= $this->_getTopTableFooter();
		$sTableHtml .= $this->_getTopTableControlPanel();
		$sTableHtml .= $this->paginator->getText();
		return $sTableHtml;
	}

	private function _getSubTree($iParentId, $iPrefixWidth = 0, $sStyleClass = '') {
		global $THEME_REF;
		$ret = '';
		$sWhereCondition = 'p_id='.$iParentId.' '.($this->search_sql ? ' AND '.$this->search_sql : '');
		$this->dataTable->select(
			array(
				'where'		=> $sWhereCondition,
				'order_by'	=> $this->dataTable->params['order_by']
			)
		);
		$aNodeList = $this->dataTable->getNextArrays(false);
		$sStyleClass .= ' t'.$iParentId;
		foreach ($aNodeList as $aEntity) {
			$this->elementsIds[] = $aEntity['id'];
			$ret .= '<tr rel="'.$aEntity['p_id'].'" class="'.$sStyleClass.'"'.($iPrefixWidth > 15000 ? ' style="display:none"' : '').'>';
			$ret .= '<td width="1%"><input type="checkbox" class="cng" onClick="setStateOne(this)" name="cng'.$aEntity['id'].'" value="'.$aEntity['id'].'"></td><td width="1%">'.$aEntity['id'].'</td>';
			$num = 0;

			$sChildrenNodes = $this->_getSubTree($aEntity['id'], $iPrefixWidth + 15, $sStyleClass);
			foreach ($this->dataTable->fields as $iKey => $aField) {
				if (!empty($aField['width'])) {
					$ret .= '<td width="'.$aField['width'].'">';
					if ($num == 0) {
						$ret .= '<span><img src="'.$THEME_REF.'/img/0.gif" width="'.$iPrefixWidth.'" height="1">';
						$ret .= '<a href="#" onClick="return toggleSubNodes('.$aEntity['id'].')"><img id="sitem'.$aEntity['id'].'" src="'.$THEME_REF.'/img/'.($sChildrenNodes && $iParentId ? 'btnminus' : 'btnminus').'.gif" border="0"></a>&nbsp;';
					}
					$ft = $this->dataTable->createFieldType($aField, $aEntity);
					if (!empty($aField['group_update']) && empty($aField['readonly'])) {
						$ret .= $ft->getGroupInput();
						$this->showGroupSubmit = true;
					} else {
						$ret .= $ft->getStatic();
					}
					if ($num == 0) {
						$ret .= '</span>';
						if ($this->dataTable->getDBTableName() == 'tree_tree') {
							$aModule = $this->get('container')->getModuleById($aEntity['module_id']);
							if ( $aModule ) {
								$ret .= ' [компонент:'.$aModule['title'].']';
							}
						}
					}
					$ret .= '</td>';
				}
				$num++;
			}
			$ret .= $this->_getUpdateDelete($aEntity['id']).'</tr>';
			$ret .= $sChildrenNodes;
		}
		return $ret;
	}

	private function _getMainBodyTree() {
		return $this->_getTopTableHeader().$this->_getSubTree(0, 0, '').$this->_getTopTableFooter().$this->_getTopTableControlPanel();
	}

	private function _getFilterForm() {
		// обрабатываем возможные поля для поиска
		$ret = '';
		$ret .= '<form method="post" id="frmFilter" action="'.$this->baseRef.'">';
		$ret .= '<input type="hidden" id="filter_type" name="filter_type" value="set" />';
		$ret .= $this->getTableHeader();
		$ret .= '<tr><th>Фильтр</th><th><div class="empty"></div></th></tr>';
		$ret .= '<tr><td align="left" width="100">ID&nbsp;</td><td>';
		$ret .= '<input name="search_filter_id" style="width: 100%;" value="'.(isset($_REQUEST['search_filter_id']) ? $_REQUEST['search_filter_id'] : '').'" size="60" type="text">';
		$ret .= '</td></tr>'."\n";
		foreach ($this->dataTable->fields as $aField) {
			if (!empty($aField['search'])) {
				$ret .= '<tr><td align="left" width="100">'.$aField['title'].'&nbsp;</td><td>';
				// выводим соответствующий инпут
				$ft = $this->dataTable->createFieldType($aField);
				$ret .= $ft->getSearchInput();
				$ret .= '</td></tr>'."\n";
			}
		}
		return $ret.'</table><div class="ctlbtns"><input class="adm-btn" type="submit" value="Искать"><input class="adm-btn" onclick="preFilter(\'cansel\');" type="button" value="Отменить фильтры"></div></div></form>'; 
	}

	public function getText() {
		$links = array(
			array(
				'ref' => $this->fullRef.'/add',
				'name' => 'Добавить запись'
			)
		);
		if ($this->get('security')->isDeveloper() && __SHOW_NEW_DEV) {
			$links[] =	array(
				'ref' => $this->fullRef.'/table',
				'name' => 'Настройка таблицы'
			);
		}
		$links[] =	array(
			'ref' => $this->fullRef.'/create',
			'name' => 'Создать таблицу'
		);
		$links[] =	array(
			'ref' => $this->fullRef.'/alter',
			'name' => 'Обновить таблицу'
		);
		$ret = $this->getOperationsBar($links);
		if (!empty($this->dataTable->params['is_view'])) {
			$ret .= $this->_getMainBodyTree();
		} else {
			$ret .= $this->_getMainBodyTable();
		}
		$ret .= $this->_getFilterForm();
		return $ret;
	}
}
