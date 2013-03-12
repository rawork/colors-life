<?php

namespace Fuga\CommonBundle\Model;

class CategoryManager extends ModelManager {
	
	public function getCategoryTree($id = 0) {
		$cats = $this->get('container')->getItems('catalog_category', 'publish=1 AND parent_id='.$id);
		foreach ($cats as &$cat) {
			$subcats = $this->get('container')->getItems(
					'catalog_category', 
					'publish=1 AND parent_id='.$cat['id']
			);
			foreach ($subcats as &$subcat) {
				$subcat['children'] = $this->get('container')->getItems(
					'catalog_category', 
					'publish=1 AND parent_id='.$subcat['id']
				);
				
			}
			$cat['children'] = $subcats;
			$cat['per_column'] = ceil(count($cat['children'])/2);
		}
		return $cats;
	}
	
	public function getPathNodes($id = 0){
		$nodes = $this->get('container')->getTable('catalog_category')->getPrev($id);
		foreach ($nodes as &$node) {
			$node['ref'] = $this->get('container')->href($this->get('router')->getParam('node'), 'index', array($node['id']));
		}
		
		return $nodes;
	}
	
}