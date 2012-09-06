<?php

    $blog_tables = array();
    $blog_tables[] = array(
		'name'      => 'groups',
		'component' => 'blog',
		'title'     => '������ ������',
		'order_by'  => 'ord', 
		'is_lang'   => true,
		'is_publish'=> true,
		'is_sort'   => true,
		'fieldset'  => array (
        array (
            'name'   => 'name',
            'title'  => '��������',
            'type'   => 'string',
            'width'  => '95%',
            'search' => true
        )
    ));
	
	$vote_tables[] = array(
		'name'       => 'answers',
		'component'  => 'vote',
		'title'      => '�����',
		'order_by'   => 'ord,name', 
		'is_lang'    => true,
		'is_publish' => true,
		'is_sort'    => true,
		'fieldset'   => array (
        array (
            'name'   => 'name',
            'title'  => '�����',
            'type'   => 'string',
            'width'  => '45%',
            'search' => true
        ),
        array (
            'name'    => 'question_id',
            'title'   => '������',
            'type'    => 'select',
        	'l_table' => 'vote_questions',
        	'l_field' => 'title',
			'l_lang'  => true,
            'width'   => '45%',
        	//'group_update' => true,
            'search'  => true
        ),
        array (
            'name'  => 'color',
            'title' => '����',
            'type'  => 'color',
			'group_update' => true,
        	'width' => '5%'
        ),
        array (
            'name'  => 'quantity',
            'title' => '���-�� �������',
            'type'  => 'number',
			'readonly' => true,
        	'width' => '5%'
        )
    ));
	
	$vote_tables[] = array(
		'name'      => 'cache',
		'component' => 'vote',
		'title'     => '��� �������',
		'order_by'  => 'time DESC',
		'no_update'  => true,
		'no_delete'  => true,
		'no_insert'  => true,
		'fieldset'  => array (
        array (
            'name'   => 'question_id',
            'title'  => '�����',
            'type'   => 'select',
        	'l_table'=> 'vote_questions',
        	'l_field'=> 'title',
            'width'  => '40%',
            'search' => true
        ),
		array (
            'name'   => 'sessionid',
            'title'  => '������',
            'type'   => 'string',
            'width'  => '35%'
        ),
		array (
            'name'   => 'ip',
            'title'  => 'IP',
            'type'   => 'string',
            'width'  => '15%',
            'search' => true
        ),
        array (
            'name'  => 'time',
            'title' => '�����',
            'type'  => 'number',
        	'width' => '10%'
        )
    ));
	
?>