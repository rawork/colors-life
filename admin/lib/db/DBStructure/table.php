<?php
	
    $table_tables = array();
    $table_tables[] = array(
		'name'			=> 'tables',
		'component'		=> 'table',
		'title'			=> '�������',
		'order_by'		=> 'module_id,ord,name',
		'is_sort'		=> true,
		'is_publish'	=> true,
		'fieldset'		=> array (
			'title' => array (
				'name'	=> 'title',
				'title' => '���������',
				'type'	=> 'string',
				'width' => '10%',
				'search'=> true
			),
			'name' => array (
				'name'	=> 'name',
				'title' => '����. ���',
				'type'	=> 'string',
				'width' => '10%',
				'help'	=> '����. ��� ��������',
				'search' => true
			),
			'module_id' => array (
				'name'	=> 'module_id',
				'title' => '���������',
				'type'	=> 'select',
				'help'	=> '������ �������',
				'l_table' => 'config_modules',
				'l_field' => 'title',
				'width' => '20%'//,
				//'group_update' => true
			),
			'order_by' => array (
				'name' => 'order_by',
				'title' => '����������',
				'type' => 'string'
			),
			'is_view'	=> array (
				'name'	=> 'is_view',
				'title' => '���',
				'type'	=> 'checkbox'
			),
			'is_lang'	=> array (
				'name'	=> 'is_lang',
				'title' => '������� �� �����',
				'type'	=> 'checkbox',
				'width' => '1%',
				'group_update' => true
			),
			'is_sort'	=> array (
				'name'	=> 'is_sort',
				'title'	=> '���� ����.',
				'type'	=> 'checkbox',
				'width' => '1%',
				'group_update' => true
			),
			'is_publish' => array (
				'name'	=> 'is_publish',
				'title'	=> '���� ���.',
				'type'	=> 'checkbox',
				'width' => '1%',
				'group_update' => true
			),
			'no_insert'	=> array (
				'name'	=> 'no_insert',
				'title' => 'no_add',
				'type'	=> 'checkbox',
				'width' => '1%'
			),
			'no_update' => array (
				'name'	=> 'no_update',
				'title' => 'no_edit',
				'type'	=> 'checkbox',
				'width' => '1%'
			),
			'no_delete' => array (
				'name'	=> 'no_delete',
				'title' => 'no_del',
				'type'	=> 'checkbox',
				'width' => '1%'
			),
			'is_search' => array (
				'name'	=> 'is_search',
				'title' => '�����',
				'type'	=> 'checkbox',
				'width' => '1%',
				'group_update' => true
			),
			'show_credate'	=> array (
				'name'	=> 'show_credate',
				'title' => '���������� ���� ��������',
				'type'	=> 'checkbox'
			),
			'multifile' => array (
				'name'	=> 'multifile',
				'title' => '���. �����',
				'type'	=> 'checkbox'
			),
			'search_prefix' => array (
				'name'	=> 'search_prefix',
				'title' => '����� �����',
				'type'	=> 'string',
			)
		)
	);
   
   $table_tables[] = array(
		'name'		=> 'attributes',
		'component' => 'table',
		'title'		=> '����',
		'order_by'	=> 'table_id,ord',
		'is_sort'	=> true,
		'is_publish' => true,
		'fieldset'	=> array (
        'title'		=> array (
            'name'  => 'title',
            'title' => '���������',
            'type'  => 'string',
            'width' => '21%',
            'search'=> true
        ),
		'name' => array (
            'name'		=> 'name',
            'title'		=> '����. ���',
            'search'	=> true,
            'type'		=> 'string',
        	'help'		=> '����. �������� ����',
            'width'		=> '21%',
            'search'	=> true
        ),
        'table_id' => array (
            'name'		=> 'table_id',
            'title'		=> '�������',
            'type'		=> 'select',
            'l_table'	=> 'table_tables',
            'l_field'	=> 'title',
        	'width'		=> '21%',
            'search'	=> true
        ),
		'type' => array (
            'name'		=> 'type',
            'title'		=> '��� ����',
            'type'		=> 'enum',
        	'select_values' => 'HTML|html;�����|select;����� �� ������|select_tree;����� ���������|select_list;����|date;���� � �����|datetime;������|currency;�����|text;������|password;������������|enum;�������|image;������|string;����|file;������|checkbox;����� �����|number;������|template',
			'defvalue'	=> 'string',
			'width'		=> '21%'
        ),
        'select_values' => array (
            'name'  => 'select_values',
            'title' => '��������',
            'type'  => 'string',
			'help'  => '����-����������� &laquo;;&raquo;'
        ),
        'params' => array (
            'name'  => 'params',
            'title' => '���������',
            'type'  => 'string'
        ),
        'width' => array (
            'name'  => 'width',
            'title' => '������',
            'type'  => 'string',
            'width' => '10%',
			'defvalue' => '95%',
            'group_update' => true
        ),
        'group_update' => array (
            'name'  => 'group_update',
            'title' => 'G',
            'type'  => 'checkbox',
        	'width' => '1%',
        	'group_update' => true,
			'help'  => '��������� ����������'
        ),
        'readonly' => array (
            'name'  => 'readonly',
            'title' => 'R',
            'type'  => 'checkbox',
        	'width' => '1%',
        	'group_update' => true,
			'help' => '������ ������'
        ),
        'search' => array (
            'name'  => 'search',
            'title' => 'S',
            'type'  => 'checkbox',
        	'width' => '1%',
        	'group_update' => true,
			'help' => '�����'
        ),
		'not_empty' => array (
            'name' => 'not_empty',
            'title' => '����.',
            'type' => 'checkbox',
            'group_update'  => true,
            'width' => '1%'
        ),
		'defvalue' => array (
            'name'  => 'defvalue',
            'title' => '�������� �� ���������',
            'search' => true,
            'type'  => 'string'
        )
    ));
?>