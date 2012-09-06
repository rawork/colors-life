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
			$svalues = explode(';', 'HTML|html;Булево|checkbox;Вещественное число|float;Выбор|select;Выбор из дерева|select_tree;Выбор множества|select_list;Дата|date;Дата и время|datetime;Мемо|text;Пароль|password;Перечисление|enum;Рисунок|image;Строка|string;Файл|file;Целое число|number;Шаблон|template');
        	    foreach ($svalues as $a) {
					$types[] = explode('|', $a);
	            }
			$smarty->assign('types', $types);
			$smarty->assign('fields', $this->t->fields);
			$smarty->assign('groups', $GLOBALS['rtti']->getItems('users_groups'));
			$smarty->assign('rights', array(
				'' => 'По-умолчанию (чтение)',
				'D' => 'Закрыт',
				'R' => 'Чтение',
				'W' => 'Чтение и запись',
				'X' => 'Полный доступ'
			));
			$smarty->assign('a', $GLOBALS['rtti']->getClass($unit.'_'.$table));
            return $smarty->fetch('admin/table.edit.tpl');
        }
		
        function getText() {
			$links = array(
				array(
					'ref' => $this->fullRef,
					'name' => 'Список элементов'
				),
				array(
					'ref' => $this->fullRef.'&amp;action=create',
					'name' => 'Создать таблицу'
				),
				array(
					'ref' => $this->fullRef.'&amp;action=alter',
					'name' => 'Обновить таблицу'
				)
			);
			$ret = $this->getOperationsBar($links);
			$ret .= $this->getTableUpdate();
            return $ret;
		}
		
    }
?>