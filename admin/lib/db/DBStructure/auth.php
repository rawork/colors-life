<?php
	
	$auth_tables = array();
	$auth_tables[] = array(
		'name'		=> 'users',
		'component' => 'auth',
		'title'		=> '������������ �����',
		'order_by'	=> 'credate DESC, login',
		'show_credate' => true,
		'fieldset' => array (
        'login' => array (
            'name' => 'login',
            'title' => '�����',
            'type' => 'string',
            'not_empty' => true,
            'form' => true
        ),
		'password' => array (
            'name' => 'password',
            'title' => '������',
            'type' => 'string',
            'not_empty' => true,
			'is_check' => true,
            'form' => true
        ),
		'email' => array (
            'name' => 'email',
            'title' => 'E-Mail',
            'type' => 'string',
			'not_empty' => true,
            'form' => true,
            'width' => '15%'
        ),
        'name' => array (
            'name' => 'name',
            'title' => '���',
            'type' => 'string',
			'search' => true,
            'not_empty' => true,
            'form' => true,
            'width' => '20%'
        ),
		'lastname' => array (
            'name' => 'lastname',
            'title' => '�������',
            'type' => 'string',
			'search' => true,
            'width' => '20%'
        ),
        'phone' => array (
            'name' => 'phone',
            'title' => '�������',
            'type' => 'string',
            'width' => '15%'
        ),
		'birthday' => array (
            'name' => 'birthday',
            'title' => '���� ��������',
            'type' => 'string',
            'width' => '15%'
        ),
		'gender' => array (
            'name' => 'gender',
            'title' => '���',
            'type' => 'string',
            'width' => '5%'
        ),
        'address' => array (
            'name' => 'address',
            'title' => '����� ��������',
            'type' => 'text'
        ),
        'session_id' => array (
            'name'  => 'session_id',
            'title' => 'ID ������',
			'readonly' => true,
            'type'  => 'string'
        ),
		'logindate' => array (
            'name'  => 'logindate',
            'title' => '���� ���������� �����',
			'readonly' => true,
            'type'  => 'datetime',
			'width' => '10%'
        ),
		'pay_type' => array (
            'name' => 'pay_type',
            'title' => '������ ������',
            'type' => 'select',
			'l_table' => 'cart_pay_type',
			'l_field' => 'name',
        	'l_filter' => 'publish="on"',
			'not_empty' => true,
            'form' => true
        ),
        'delivery_type' => array (
            'name' => 'delivery_type',
            'title' => '������ ��������',
            'type' => 'select',
			'l_table' => 'cart_delivery_type',
			'l_field' => 'name',
        	'l_filter' => 'publish="on"',
			'not_empty' => true,
            'form' => true
        )
    ));

	$auth_tables[] = array(
		'name'		=> 'addresses',
		'component' => 'auth',
		'title'		=> '������ ��������',
		'order_by'	=> 'credate DESC, login',
		'show_credate' => true,
		'fieldset' => array (
        'name' => array (
            'name' => 'name',
            'title' => '��� ����������',
            'type' => 'string'
        ),
		'user_id' => array (
            'name' => 'user_id',
            'title' => '������������',
            'type' => 'select',
			'l_table' => 'auth_users',
			'l_field' => 'name,lastname'
        ),
        'town' => array (
            'name' => 'town',
            'title' => '�����',
            'type' => 'string',
			'search' => true,
            'not_empty' => true,
            'form' => true,
            'width' => '20%'
        ),
		'street' => array (
            'name' => 'street',
            'title' => '�����',
            'type' => 'string',
			'search' => true,
            'not_empty' => true,
            'form' => true,
            'width' => '20%'
        ),
        'house' => array (
            'name' => 'house',
            'title' => '���',
            'type' => 'string',
            'width' => '10%'
        ),
		'corpus' => array (
            'name' => 'corpus',
            'title' => '������',
            'type' => 'string',
            'width' => '10%'
        ),
		'building' => array (
            'name' => 'building',
            'title' => '��������',
            'type' => 'string',
            'width' => '10%'
        ),
		'apartment' => array (
            'name' => 'apartment',
            'title' => '��������',
            'type' => 'string',
            'width' => '10%'
        ),
		'comment' => array (
            'name' => 'comment',
            'title' => '�����������',
            'type' => 'text',
            'width' => '10%'
        ),
        'phone' => array (
            'name' => 'phone',
            'title' => '�������',
            'type' => 'string',
            'not_empty' => true,
            'form' => true,
            'width' => '15%'
        ),
        'phone_extra' => array (
            'name' => 'phone_extra',
            'title' => '������� (���.)',
            'type' => 'string',
            'not_empty' => true,
            'form' => true,
            'width' => '15%'
        )
    ));
?>