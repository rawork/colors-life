<?php

    $maillist_tables = array();
    $maillist_tables[] = array(
		'name' => 'lists',
		'component' => 'maillist',
		'title' => '������� ��������',
		'order_by' => 'date',
		'fieldset' => array (
        'rubrics' => array (
            'name' => 'rubrics',
            'title' => '������ ��������',
            'type' => 'select_list',
			'l_table' => 'maillist_rubric',
			'l_field' => 'name',
            'width' => '30%'
        ),
		'subj' => array (
            'name' => 'subj',
            'title' => '����',
            'type' => 'string',
            'width' => '35%',
            'search'=> true
        ),
        'body' => array (
            'name' => 'body',
            'title' => '�����',
            'type' => 'html'
        ),
        'file' => array (
            'name' => 'file',
            'title' => '����',
            'type' => 'file',
            'path' => '/mailfiles',
            'width' => '20%'
            
        ),
        'date' => array (
            'name' => 'date',
            'title' => '����',
            'type' => 'datetime',
            'width' => '15%',
            'search'=> true
        )
    ));
    	
    $maillist_tables[] = array(
		'name' => 'users',
		'component' => 'maillist',
		'title' => '����������',
		'order_by' => 'name',
		'fieldset' => array (
        'lastname' => array (
            'name'  => 'lastname',
            'title' => '�������',
            'type'  => 'string',
            'width' => '24%',
            'search'=> true
        ),
		'name' => array (
            'name'  => 'name',
            'title' => '���',
            'type'  => 'string',
            'width' => '24%',
            'search'=> true
        ),
        'email' => array (
            'name'  => 'email',
            'title' => '�����',
            'type'  => 'string',
            'width' => '24%',
            'search'=> true
        ),
        'date' => array (
            'name'  => 'date',
            'title' => '���� �����������',
            'type'  => 'datetime',
            'width' => '24%',
            'search'=> true
        ),
        'is_active' => array (
            'name' => 'is_active',
            'title' => '��������',
            'type' => 'checkbox',
            'search' => true,
            'group_update'  => true,
            'width' => '1%'
        )
    ));
	
	$maillist_tables[] = array(
		'name' => 'rubric',
		'component' => 'maillist',
		'title' => '������ ��������',
		'order_by' => 'name',
		'fieldset' => array (
        'name' => array (
            'name'  => 'name',
            'title' => '���',
            'type'  => 'string',
            'width' => '95%',
            'search'=> true
        )
    ));

?>