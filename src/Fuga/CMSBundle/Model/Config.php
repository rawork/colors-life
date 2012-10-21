<?php

namespace Fuga\CMSBundle\Model;

class Config {
	
	public $tables;

	public function __construct() {

		$this->tables = array();
		$this->tables[] = array(
			'name' => 'modules',
			'component' => 'config',
			'title' => 'Модули',
			'order_by' => 'sort,title',
			'fieldset' => array (
			'title' => array (
				'name' => 'title',
				'title' => 'Название',
				'type' => 'string',
				'width' => '55%'
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Сист. имя',
				'type' => 'string',
				'width' => '40%',
				'search' => true
			),
			'sort' => array (
				'name' => 'sort',
				'title' => 'Сорт.',
				'type' => 'number',
				'group_update'  => true,
				'width' => '3%'
			)
		));

		$this->tables[] = array(
			'name' => 'methods',
			'component' => 'config',
			'title' => 'Методы',
			'order_by' => 'module_id,name',
			'fieldset' => array (
			'title' => array (
				'name' => 'title',
				'title' => 'Название',
				'type' => 'string',
				'width' => '25%'
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Сист. имя',
				'type' => 'string',
				'width' => '20%',
				'search' => true
			),
			'module_id' => array (
				'name' => 'module_id',
				'title' => 'Компонент',
				'type' => 'select',
				'l_table' => 'config_modules',
				'l_field' => 'title',
				'search' => true,
				'width' => '25%'
			),
			'template_id' => array (
				'name' => 'template_id',
				'title' => 'Макет',
				'type' => 'select',
				'l_table' => 'template_template',
				'l_field' => 'name',
				'search' => true,
				'width' => '25%'
			),	
			'processor' => array (
				'name' => 'processor',
				'title' => 'Процессор',
				'type' => 'template',
				'basepath' => 'methods/'
			),	
			'template' => array (
				'name' => 'template',
				'title' => 'Шаблон',
				'type' => 'template'
			)
		));

		$this->tables[] = array(
			'name' => 'variables',
			'component' => 'config',
			'title' => 'Переменные',
			'order_by' => 'name',
			'fieldset' => array (
			'title' => array (
				'name' => 'title',
				'title' => 'Название',
				'type' => 'string',
				'width' => '33%',
				'search' => true
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Имя (англ.)',
				'type' => 'string',
				'width' => '33%',
				'search'=> true
			),
			'value' => array (
				'name'  => 'value',
				'title' => 'Значение',
				'width' => '33%',
				'type' => 'string'
			)
		));

		$this->tables[] = array(
			'name' => 'languages',
			'component' => 'config',
			'title' => 'Языки',
			'order_by' => 'id',
			'fieldset' => array (
			'name' => array (
				'name' => 'name',
				'title' => 'Название',
				'type' => 'string',
				'width' => '95%',
				'search' => true
			)
		));
	}
}
