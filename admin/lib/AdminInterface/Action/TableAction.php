<?php

namespace AdminInterface\Action;

class TableAction extends Action {
	function __construct(&$adminController) {	
		parent::__construct($adminController);
	}

	function getTableUpdate() {
		$unit = $this->get('router')->getParam('module');
		$table = $this->get('router')->getParam('table');
		$types = array();
		$svalues = explode(';', 'HTML|html;Булево|checkbox;Вещественное число|float;Выбор|select;Выбор из дерева|select_tree;Выбор множества|select_list;Дата|date;Дата и время|datetime;Мемо|text;Пароль|password;Перечисление|enum;Рисунок|image;Строка|string;Файл|file;Целое число|number;Шаблон|template');
			foreach ($svalues as $a) {
				$types[] = explode('|', $a);
			}
		$this->get('smarty')->assign('types', $types);
		$this->get('smarty')->assign('fields', $this->dataTable->fields);
		$this->get('smarty')->assign('groups', $this->get('container')->getItems('users_groups'));
		$this->get('smarty')->assign('rights', array(
			'' => 'По-умолчанию (чтение)',
			'D' => 'Закрыт',
			'R' => 'Чтение',
			'W' => 'Чтение и запись',
			'X' => 'Полный доступ'
		));
		$this->get('smarty')->assign('a', $this->get('container')->getClass($unit.'_'.$table));
		return $this->get('smarty')->fetch('admin/table.edit.tpl');
	}

	function getText() {
		$links = array(
			array(
				'ref' => $this->fullRef,
				'name' => 'Список элементов'
			),
			array(
				'ref' => $this->fullRef.'/create',
				'name' => 'Создать таблицу'
			),
			array(
				'ref' => $this->fullRef.'/alter',
				'name' => 'Обновить таблицу'
			)
		);
		$ret = $this->getOperationsBar($links);
		$ret .= $this->getTableUpdate();
		return $ret;
	}

}
