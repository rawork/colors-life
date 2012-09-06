<?php
	$tree_tables = array();
	$tree_tables[] = array(
		'name' => 'tree',
		'component' => 'tree',
		'title' => 'Разделы',
		'order_by' => 'ord,name', 
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
		'p_id' => array (
            'name' => 'p_id',
            'title' => 'Находится в',
            'type' => 'select_tree',
            'l_table' => 'tree_tree',
            'l_field' => 'title',
        	'l_sort' => 'ord,title',
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
        'body' => array (
            'name' => 'body',
            'title' => 'Текст',
            'type' => 'html'
        ),
        'h1_img' => array (
            'name'  => 'h1_img',
            'title' => 'Картинка H1',
            'type' => 'image',
        )
    ));
	
	$tree_tables[] = array(
		'name' => 'blocks',
		'component' => 'tree',
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
		'body' => array (
            'name'  => 'body',
            'title' => 'Текст',
            'type' => 'html'
        )
    ));
?>