<?php

namespace Fuga\CommonBundle\Model;

class User {
	
	public $tables;

	public function __construct() {

		$this->tables = array();

		$this->tables[] = array(
		'name' => 'user',
		'component' => 'user',
		'title' => 'Список пользователей',
		'order_by' => 'login',
		'fieldset' => array (
			'login' => array (
				'name' => 'login',
				'title' => 'Логин',
				'type' => 'string',
				'width' => '25%',
			),
			'password' => array (
				'name' => 'password',
				'title' => 'Пароль',
				'type' => 'password',
			),
			'hashkey' => array (
				'name' => 'hashkey',
				'title' => 'Ключ',
				'type' => 'string',
			),
			'name' => array (
				'name' => 'name',
				'title' => 'ФИО',
				'type' => 'string',
				'width' => '25%'
			),
			'email' => array (
				'name' => 'email',
				'title' => 'Эл. почта',
				'type' => 'string',
				'width' => '25%'
			),
			'group_id' => array (
				'name' => 'group_id',
				'title' => 'Группа',
				'type' => 'select',
				'l_table' => 'user_group',
				'l_field' => 'name',
				'width' => '25%'

			),
			'is_admin' => array (
				'name' => 'is_admin',
				'title' => 'Админ',
				'type' => 'checkbox',
				'width' => '1%',
				'group_update' => true
			),
			'is_active' => array (
				'name' => 'is_active',
				'title' => 'Активен',
				'type' => 'checkbox',
				'width' => '1%',
				'group_update' => true
			)	
		));

		$this->tables[] = array(
		'name' => 'group',
		'component' => 'user',
		'title' => 'Группы пользователей',
		'order_by' => 'name',
		'fieldset' => array (
			'name' => array (
				'name' => 'name',
				'title' => 'Название',
				'type' => 'string',
				'width' => '25%',
			),
			'rules' => array (
				'name' => 'rules',
				'title' => 'Права',
				'type' => 'select_list',
				'l_table' => 'config_modules',
				'l_field' => 'title',
				'search' => true,
				'width' => '70%'
			)
		));
		
		$this->tables[] = array(
			'name'		=> 'address',
			'component' => 'user',
			'title'		=> 'Адреса доставки',
			'order_by'	=> 'id DESC',
			'show_created' => true,
			'fieldset' => array (
			'name' => array (
				'name' => 'name',
				'title' => 'ФИО получателя',
				'type' => 'string'
			),
			'user_id' => array (
				'name' => 'user_id',
				'title' => 'Пользователь',
				'type' => 'select',
				'l_table' => 'auth_users',
				'l_field' => 'name,lastname'
			),
			'town' => array (
				'name' => 'town',
				'title' => 'Город',
				'type' => 'string',
				'search' => true,
				'not_empty' => true,
				'form' => true,
				'width' => '20%'
			),
			'street' => array (
				'name' => 'street',
				'title' => 'Улица',
				'type' => 'string',
				'search' => true,
				'not_empty' => true,
				'form' => true,
				'width' => '20%'
			),
			'house' => array (
				'name' => 'house',
				'title' => 'Дом',
				'type' => 'string',
				'width' => '10%'
			),
			'corpus' => array (
				'name' => 'corpus',
				'title' => 'Корпус',
				'type' => 'string',
				'width' => '10%'
			),
			'building' => array (
				'name' => 'building',
				'title' => 'Строение',
				'type' => 'string',
				'width' => '10%'
			),
			'apartment' => array (
				'name' => 'apartment',
				'title' => 'Квартира',
				'type' => 'string',
				'width' => '10%'
			),
			'comment' => array (
				'name' => 'comment',
				'title' => 'Комментарий',
				'type' => 'text',
				'width' => '10%'
			),
			'phone' => array (
				'name' => 'phone',
				'title' => 'Телефон',
				'type' => 'string',
				'not_empty' => true,
				'form' => true,
				'width' => '15%'
			),
			'phone_extra' => array (
				'name' => 'phone_extra',
				'title' => 'Телефон (доп.)',
				'type' => 'string',
				'not_empty' => true,
				'form' => true,
				'width' => '15%'
			)
		));
	}
}