<?php
	inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class s_methodsUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
		// Методы
		function getText() {
		global $db, $THEME_REF;
			$ret = '';
			$ret .= '<form enctype="multipart/form-data" method="post" name="frmInsert" id="frmInsert" action="'.$this->fullRef.'&action=methods">';
			$ret .= $this->getTableHeader();
			$ret .= '<tr>';
			$ret .= '<th>Макет</th>';
			$ret .= '<th>Шаблон</th>';
			$ret .= '<th>Обработчик</th>';
			$ret .= '<th align=center><img alt="Действия" src="'.$THEME_REF.'/img/action_head.gif" border=0></td></tr>'."\n";
			$component = $GLOBALS['rtti']->getComponent(CUtils::_getVar('unit'));
			$methods = $GLOBALS['db']->getItems('config_methods',
				'SELECT cm.*,tt.name as maket_name FROM config_methods cm LEFT JOIN templates_templates tt ON cm.maket=tt.id WHERE cm.seniorid='.$component['id']);
			foreach ($methods as $method) {
				$ret .= '<tr><td class=left align=left width=250>'.$method['maket_name'].'</td>';
				$ret .= '<td>'.($method['code'] ? $method['code'] : 'нет').'</td>';
				$ret .= '<td class="right">'.($method['template'] ? $method['template'] : 'нет').'</td>';
				$ret .= $this->getUpdateDelete($method['id']).'</tr>'."\n";
			} 
            $ret .= '</table></form>';
			return $ret;
        }
    }
?>