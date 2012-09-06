<?php
    $forms_tables = array();
    $forms_tables[] = array(
		'name' => 'forms',
		'component' => 'forms',
		'title' => '��� �����',
		'order_by' => 'name', 
		'is_lang' => true,
		'fieldset' => array (
        'title' => array (
            'name' => 'title',
            'title' => '��������',
            'type' => 'string',
        	'width' => '19%',
            'search' => true
        ),
		'name' => array (
            'name' => 'name',
            'title' => '��� (����.)',
            'type' => 'string',
        	'width' => '19%',
        ),
		'email' => array (
            'name' => 'email',
            'title' => 'E-mail',
            'type' => 'string',
        	'width' => '19%',
        ),
		'submit_text' => array (
            'name' => 'submit_text',
            'title' => 'Submit ������',
            'type' => 'string',
        	'width' => '19%'
        ),
		'template' => array (
            'name' => 'template',
            'title' => '������',
            'type' => 'template'
        ),
		'is_defense' => array (
            'name' => 'is_defense',
            'title' => 'CAPTCHA',
            'type' => 'checkbox',
        	'width' => '1%',
        	'group_update' => true
        )
    ));
    
    $forms_tables[] = array(
		'name' => 'fields',
		'component' => 'forms',
		'title' => '���� �����',
		'order_by' => 'form_id,ord', 
		'is_sort' => true, 
		'is_lang' => true,
		//'is_hidden' => true,
		'fieldset' => array (
        'title' => array (
            'name' => 'title',
            'title' => '��������',
            'type' => 'string',
            'width' => '45%'
        ),
        'name' => array (
            'name' => 'name',
            'title' => '��� (����.)',
            'type' => 'string',
            'width' => '30%',
            'search' => true
        ),
		'form_id' => array (
            'name' => 'form_id',
            'title' => '�����',
            'type' => 'select',
            'l_table' => 'forms_forms',
            'l_field' => 'title',
        	'l_lang' => true,
            'width' => '30%',
            'search' => true
        ),
        'type' => array (
            'name' => 'type',
            'title' => '���',
            'type' => 'enum',
            'select_values' => '������|string;�����|text;������|select;����|checkbox;����|file;������|password',
            'width' => '30%'
        ),
		'select_table' => array (
            'name' => 'select_table',
            'title' => '������� ��������',
            'type' => 'string',
			'help' => '������� ��������'
        ),
		'select_name' => array (
            'name' => 'select_name',
            'title' => '���� ���������',
            'type' => 'string',
        ),
		'select_value' => array (
            'name' => 'select_value',
            'title' => '���� ��������',
            'type' => 'string',
        ),
		'select_filter' => array (
            'name' => 'select_filter',
            'title' => '������',
            'type' => 'string',
        ),
		'select_values' => array (
            'name' => 'select_values',
            'title' => '��������',
            'type' => 'string'
        ),
		'not_empty' => array (
            'name' => 'not_empty',
            'title' => '����.',
            'type' => 'checkbox',
            'group_update'  => true,
            'width' => '1%'
        ),
		'is_check' => array (
            'name' => 'is_check',
            'title' => '�������� ����',
            'type' => 'checkbox',
            'group_update'  => true,
            'width' => '1%'
        )  
    ));

?>
