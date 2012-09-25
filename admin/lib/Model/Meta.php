<?php

namespace Model;

class Meta {
	
	public $tables;

	public function __construct() {

		$this->tables = array();
		$this->tables[] = array(
			'name' => 'items',
			'component' => 'meta',
			'title' => 'Варианты',
			'fieldset' => array (
			'words' => array (
				'name' => 'words',
				'title' => 'Строки URI',
				'type' => 'text',
				'help' => 'Через запятую',
				'width' => '20%'
			),
			'keywords' => array (
				'name' => 'keywords',
				'title' => 'Подстроки URI',
				'type' => 'text',
				'help' => 'Через запятую',
				'width' => '20%'
			),
			'title' => array (
				'name' => 'title',
				'title' => 'Тайтл',
				'type' => 'text',
				'width' => '25%',
				'search' => true
			),
			'meta' => array (
				'name' => 'meta',
				'title' => 'Метатеги',
				'type' => 'text',
				'width' => '25%',
				'help' => 'Включая служебные символы',
				'search' => true
			)
		));
	}
}
