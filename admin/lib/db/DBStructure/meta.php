<?php

    $meta_tables = array();
    $meta_tables[] = array(
		'name' => 'items',
		'component' => 'meta',
		'title' => '��������',
		'fieldset' => array (
        'words' => array (
            'name' => 'words',
            'title' => '������ URI',
            'type' => 'text',
            'help' => '����� �������',
            'width' => '20%'
        ),
        'keywords' => array (
            'name' => 'keywords',
            'title' => '��������� URI',
            'type' => 'text',
            'help' => '����� �������',
            'width' => '20%'
        ),
        'title' => array (
            'name' => 'title',
            'title' => '�����',
            'type' => 'text',
            'width' => '25%',
            'search' => true
        ),
        'meta' => array (
            'name' => 'meta',
            'title' => '��������',
            'type' => 'text',
            'width' => '25%',
            'help' => '������� ��������� �������',
            'search' => true
        )
    ));

?>