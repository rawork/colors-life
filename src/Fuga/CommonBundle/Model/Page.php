<?php

namespace Fuga\CommonBundle\Model;

class Page {
	
	public $tables;

	public function __construct() {

		$this->tables = array();
		$this->tables[] = array(
			'name' => 'page',
			'component' => 'page',
			'title' => 'Разделы',
			'order_by' => 'sort,name', 
			'is_lang' => true,
			'is_publish' => true,
			'is_sort' => true,
			'is_view' => true,
			'is_search' => true,
			'search_prefix' => '',
			'fieldset' => array (
			'title' => array (
				'name' => 'title',
				'title' => 'Название',
				'type' => 'string',
				'width' => '70%',
				'search' => true
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Имя (англ.)',
				'type' => 'string',
				'width' => '25%',
				'help' => 'англ. буквы без пробелов',
				'search' => true,
				'group_update' => true
			),
			'url' => array (
				'name' => 'url',
				'title' => 'Ссылка',
				'type' => 'string'
			),
			'parent_id' => array (
				'name' => 'parent_id',
				'title' => 'Находится в',
				'type' => 'select_tree',
				'l_table' => 'page_page',
				'l_field' => 'title',
				'l_sort' => 'sort,title',
				'l_lang' => true
			),
			'module_id' => array (
				'name' => 'module_id',
				'title' => 'Компонент',
				'type' => 'select',
				'l_table' => 'config_modules',
				'l_field' => 'title',
				'query' => "id NOT IN(17)"
			),
			'content' => array (
				'name' => 'content',
				'title' => 'Текст',
				'type' => 'html'
			),
			'h1_img' => array (
				'name'  => 'h1_img',
				'title' => 'Картинка H1',
				'type' => 'image',
			),
			'left_key' => array (
				'name'  => 'left_key',
				'title' => 'Левый ключ',
				'type'  => 'number',
				'readonly' => true
			),
			'right_key' => array (
				'name'  => 'right_key',
				'title' => 'Правый ключ',
				'type'  => 'number',
				'readonly' => true
			),
			'level' => array (
				'name'  => 'level',
				'title' => 'Уровень',
				'type'  => 'number',
				'readonly' => true
			)
		));

		$this->tables[] = array(
			'name' => 'block',
			'component' => 'page',
			'title' => 'Инфоблоки',
			'order_by' => 'name', 
			'is_lang' => true,
			'is_publish' => true,
			'fieldset' => array (
			'title' => array (
				'name' => 'title',
				'title' => 'Название',
				'search' => true,
				'type' => 'string',
				'width' => '40%',
				'search'=> true
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Имя (англ.)',
				'type' => 'string',
				'width' => '40%',
				'search'=> true
			),
			'content' => array (
				'name'  => 'content',
				'title' => 'Текст',
				'type' => 'html'
			)
		));
		
		$this->tables[] = array(
			'name' => 'seo',
			'component' => 'page',
			'title' => 'SEO',
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