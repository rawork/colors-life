<?php

    $users_tables = array();
    $users_tables[] = array(
	'name' => 'users',
	'component' => 'users',
	'title' => '������ �������������',
	'order_by' => 'syslogin',
	'fieldset' => array (
        'syslogin' => array (
            'name' => 'syslogin',
            'title' => '�����',
            'type' => 'string',
            'width' => '25%',
        ),
        'syspassword' => array (
            'name' => 'syspassword',
            'title' => '������',
            'type' => 'password',
        ),
        'hashkey' => array (
            'name' => 'hashkey',
            'title' => '����',
            'type' => 'string',
        ),
        'name' => array (
            'name' => 'name',
            'title' => '���',
            'type' => 'string',
            'width' => '25%'
        ),
        'email' => array (
            'name' => 'email',
            'title' => '��. �����',
            'type' => 'string',
            'width' => '25%'
        ),
        'group_id' => array (
            'name' => 'group_id',
            'title' => '������',
            'type' => 'select',
            'l_table' => 'users_groups',
            'l_field' => 'name',
            'width' => '25%'
        	
        ),
	'is_admin' => array (
            'name' => 'is_admin',
            'title' => '�����',
            'type' => 'checkbox',
            'width' => '1%',
            'group_update' => true
        ),
        'is_active' => array (
            'name' => 'is_active',
            'title' => '�������',
            'type' => 'checkbox',
            'width' => '1%',
            'group_update' => true
        )	
    ));
    
    $users_tables[] = array(
	'name' => 'groups',
	'component' => 'users',
	'title' => '������ �������������',
	'order_by' => 'name',
	'fieldset' => array (
        'name' => array (
            'name' => 'name',
            'title' => '��������',
            'type' => 'string',
            'width' => '25%',
        ),
        'rules' => array (
            'name' => 'rules',
            'title' => '�����',
            'type' => 'select_list',
            'l_table' => 'config_modules',
            'l_field' => 'title',
            'width' => '70%'
        )
    ));

?>