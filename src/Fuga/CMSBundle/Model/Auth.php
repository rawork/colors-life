<?php

namespace Fuga\CMSBundle\Model;

class Auth {
	
	public $tables;

	public function __construct() {

		$this->tables = array();
		$this->tables[] = array(
			'name'		=> 'users',
			'component' => 'auth',
			'title'		=> 'Пользователи сайта',
			'order_by'	=> 'credate DESC, login',
			'show_credate' => true,
			'fieldset' => array (
			'login' => array (
				'name' => 'login',
				'title' => 'Логин',
				'type' => 'string',
				'not_empty' => true,
				'form' => true
			),
			'password' => array (
				'name' => 'password',
				'title' => 'Пароль',
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
				'title' => 'Имя',
				'type' => 'string',
				'search' => true,
				'not_empty' => true,
				'form' => true,
				'width' => '20%'
			),
			'lastname' => array (
				'name' => 'lastname',
				'title' => 'Фамилия',
				'type' => 'string',
				'search' => true,
				'width' => '20%'
			),
			'phone' => array (
				'name' => 'phone',
				'title' => 'Телефон',
				'type' => 'string',
				'width' => '15%'
			),
			'birthday' => array (
				'name' => 'birthday',
				'title' => 'День рождения',
				'type' => 'string',
				'width' => '15%'
			),
			'gender' => array (
				'name' => 'gender',
				'title' => 'Пол',
				'type' => 'string',
				'width' => '5%'
			),
			'address' => array (
				'name' => 'address',
				'title' => 'Адрес доставки',
				'type' => 'text'
			),
			'discount' => array (
				'name' => 'discount',
				'title' => 'Скидка',
				'type' => 'number',
				'width' => '3%'
			),	
			'session_id' => array (
				'name'  => 'session_id',
				'title' => 'ID сессии',
				'readonly' => true,
				'type'  => 'string'
			),
			'logindate' => array (
				'name'  => 'logindate',
				'title' => 'Дата последнего входа',
				'readonly' => true,
				'type'  => 'datetime',
				'width' => '10%'
			),
			'pay_type' => array (
				'name' => 'pay_type',
				'title' => 'Способ оплаты',
				'type' => 'select',
				'l_table' => 'cart_pay_type',
				'l_field' => 'name',
				'l_filter' => 'publish="on"',
				'not_empty' => true,
				'form' => true
			),
			'delivery_type' => array (
				'name' => 'delivery_type',
				'title' => 'Способ доставки',
				'type' => 'select',
				'l_table' => 'cart_delivery_type',
				'l_field' => 'name',
				'l_filter' => 'publish="on"',
				'not_empty' => true,
				'form' => true
			)
		));

		$this->tables[] = array(
			'name'		=> 'addresses',
			'component' => 'auth',
			'title'		=> 'Адреса доставки',
			'order_by'	=> 'credate DESC, login',
			'show_credate' => true,
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