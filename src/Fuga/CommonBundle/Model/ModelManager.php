<?php

namespace Fuga\CommonBundle\Model;

class ModelManager implements ModelManagerInterface {
	
	protected $entityTable;
	
	public function findAll() {
		return $this->findBy();
	}
	
	public function findBy($query = '', $sort = '', $limit = null, $offset = null) {
		$this->get('container')->getItems($this->entityTable, $query, $sort, $select);
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