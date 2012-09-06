<?php

    $vote_tables = array();
    $vote_tables[] = array(
		'name'      => 'questions',
		'component' => 'vote',
		'title'     => '�����',
		'order_by'  => 'date_beg DESC', 
		'is_lang'   => true,
		//'is_publish'=> true,
		'fieldset'  => array (
        'title' => array (
            'name'   => 'title',
            'title'  => '������',
            'type'   => 'string',
            'width'  => '45%',
            'search' => true
        ),
        'name' => array (
            'name'   => 'name',
            'title'  => '����. ���',
            'type'   => 'string',
            'width'  => '15%',
            'search' => true
        ),
        'date_beg' => array (
            'name'  => 'date_beg',
            'title' => '������ ������',
            'type'  => 'datetime',
            'width' => '15%',
            'search'=> true
        ),
        'date_end' => array (
            'name'  => 'date_end',
            'title' => '����� ������',
            'type'  => 'datetime',
            'width' => '15%',
            'search'=> true
        ),
        'quantity' => array (
            'name'  => 'quantity',
            'title' => '���-�� �������',
            'type'  => 'number',
			'readonly' => true,
        	'width' => '5%'
        ),
		'step' => array (
            'name'  => 'step',
            'title' => '��� �����������',
            'type'  => 'number',
			'help'  => '� ��������',
			'group_update' => true,
        	'width' => '5%'
        ),
		'lmt' => array (
            'name'  => 'lmt',
            'title' => '�����������',
            'type'  => 'enum',
			'select_values' => '��� �����������|0;�� ������|1;�� ������ � IP|2',
			'dir' => true,
			'help'  => '� ��������',
			'group_update' => true,
        	'width' => '5%'
        ),
		'is_dia' => array (
            'name'  => 'is_dia',
            'title' => '���������',
            'type'  => 'checkbox',
			'help'  => '��������� � ���� ���������',
			'group_update' => true,
        	'width' => '1%'
        ),
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
        'name' => array (
            'name'   => 'name',
            'title'  => '�����',
            'type'   => 'string',
            'width'  => '45%',
            'search' => true
        ),
        'question_id' => array (
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
        'color' => array (
            'name'  => 'color',
            'title' => '����',
            'type'  => 'color',
			'group_update' => true,
        	'width' => '5%'
        ),
        'quantity' => array (
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
		'title'     => '�����������',
		'order_by'  => 'time DESC',
		'no_update'  => true,
		'no_delete'  => true,
		'no_insert'  => true,
		'fieldset'  => array (
        'question_id' => array (
            'name'   => 'question_id',
            'title'  => '�����',
            'type'   => 'select',
        	'l_table'=> 'vote_questions',
        	'l_field'=> 'title',
            'width'  => '40%',
            'search' => true
        ),
		'sessionid' => array (
            'name'   => 'sessionid',
            'title'  => '������',
            'type'   => 'string',
            'width'  => '35%'
        ),
		'ip' => array (
            'name'   => 'ip',
            'title'  => 'IP',
            'type'   => 'string',
            'width'  => '15%',
            'search' => true
        ),
        'time' => array (
            'name'  => 'time',
            'title' => '�����',
            'type'  => 'number',
        	'width' => '10%'
        )
    ));
	
?>