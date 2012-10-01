<?php

namespace Fuga\CMSBundle\Model;

class ModelManager implements ModelManagerInterface {
	
	protected $entityTable;
	
	public function findAll() {
		return $this->findBy($this->entityTable);
	}
	
	public function findBy($query = array(), $sort = array(), $limit = null, $offset = null) {
		$this->get('container')->getItems($this->entityTable, $condition, $sort, $select);
	}
	
	public function get($name) {
		global $container, $security;
		if ($name == 'container') {
			return $container;
		} elseif ($name == 'security') {
			return $security;
		} else {
			return $container->get($name);
		}
	}
}