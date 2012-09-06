<?php
	
    $templates_tables = array();
    $templates_tables[] = array(
	'name' => 'templates',
	'component' => 'templates',
	'title' => '�������',
	'order_by' => 'name',
	'is_lang' => true,
	'fieldset' => array (
        'name' => array (
            'name' => 'name',
            'title' => '�������� ������',
            'type' => 'string',
            'width' => '95%'
        ),
        'template' => array (
            'name' => 'template',
            'title' => '������ HTML',
            'type' => 'template'
        )
    ));
    $templates_tables[] = array(
	'name' => 'version',
	'component' => 'templates',
	'title' => '���������������',
	'order_by' => 'credate',
	'is_hidden' => true,
	'fieldset' => array (
		'cls' => array (
            'name' => 'cls',
            'title' => '�������',
            'type' => 'string',
            'width' => '20%',
            'search'=> true
        ),
        'fld' => array (
            'name' => 'fld',
            'title' => '����',
            'type' => 'string',
            'width' => '25%',
            'search'=> true
        ),
        'rc' => array (
            'name' => 'rc',
            'title' => '������',
            'type' => 'number',
            'width' => '25%',
            'search' => true
        ),
		'file' => array (
            'name'  => 'file',
            'title' => '����-������',
            'type' => 'file',
            'width' => '25%'
        )
    ));
	$templates_tables[] = array(
	'name' => 'rules',
	'component' => 'templates',
	'title' => '������� ��������',
	'order_by' => 'ord',
	'is_lang' => true,
	'is_sort' => true,
	'fieldset' => array (
        'template_id' => array (
            'name' => 'template_id',
            'title' => '������',
            'type' => 'select',
			'l_table' => 'templates_templates',
			'l_field' => 'name',
			'l_lang' => true,
			'width' => '31%',
            'group_update' => true
        ),
        'type' => array (
            'name' => 'type',
            'title' => '��� �������',
            'type' => 'enum',
            'select_values' => '������|F;�������� URL|U;������ �������|T',
            'width' => '20%',
            'group_update' => true
        ),
        'cond' => array (
            'name' => 'cond',
            'title' => '�������',
            'type' => 'string',
            'width' => '20%',
            'group_update' => true
        ),
		'date_beg' => array (
            'name' => 'date_beg',
            'title' => '������ ������',
            'type' => 'datetime',
            'width' => '12%'
        ),
		'date_end' => array (
            'name' => 'date_end',
            'title' => '����� ������',
            'type' => 'datetime',
            'width' => '12%'
        )
    ));
?>