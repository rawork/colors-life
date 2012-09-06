<?php
    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class s_tableUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {	
            parent::__construct($unitAdminInterface);
        }
		
		function getTableUpdate() {
		global $smarty;
			$unit = CUtils::_getVar('unit');
			$table = CUtils::_getVar('table');
            $ret = '';
            $fields = '';
			$types = array();
			$svalues = explode(';', 'HTML|html;������|checkbox;������������ �����|float;�����|select;����� �� ������|select_tree;����� ���������|select_list;����|date;���� � �����|datetime;����|text;������|password;������������|enum;�������|image;������|string;����|file;����� �����|number;������|template');
        	    foreach ($svalues as $a) {
					$types[] = explode('|', $a);
	            }
			$smarty->assign('types', $types);
			$smarty->assign('fields', $this->t->fields);
			$smarty->assign('groups', $GLOBALS['rtti']->getItems('users_groups'));
			$smarty->assign('rights', array(
				'' => '��-��������� (������)',
				'D' => '������',
				'R' => '������',
				'W' => '������ � ������',
				'X' => '������ ������'
			));
			$smarty->assign('a', $GLOBALS['rtti']->getClass($unit.'_'.$table));
            return $smarty->fetch('admin/table.edit.tpl');
        }
		
        function getText() {
			$links = array(
				array(
					'ref' => $this->fullRef,
					'name' => '������ ���������'
				),
				array(
					'ref' => $this->fullRef.'&amp;action=create',
					'name' => '������� �������'
				),
				array(
					'ref' => $this->fullRef.'&amp;action=alter',
					'name' => '�������� �������'
				)
			);
			$ret = $this->getOperationsBar($links);
			$ret .= $this->getTableUpdate();
            return $ret;
		}
		
    }
?>