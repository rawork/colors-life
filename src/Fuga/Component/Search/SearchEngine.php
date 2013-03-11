<?php

namespace Fuga\Component\Search;

class SearchEngine {
	
	private $container;
	private $options;
	private $pages;
	
	public function __construct($container, $options = array()) {
		$this->container = $container;
		$this->options = array(
			'catalog' => array(
				'catalog_product' => array(
					'fields' => array('name', 'preview', 'description', 'tags'),
					'link' => '/%s/stuff.%s.htm',
					'where' => "publish=1",
					'title' => 'name'
				),
				'catalog_category' => array(
					'fields' => array('title', 'name', 'description'),
					'link' => '/%s/index.%s.htm',
					'where' => "publish=1",
					'title' => 'title'
				),
				'catalog_producer' => array(
					'fields' => array('name'),
					'link' => '/%s/brand.%s.htm',
					'where' => "publish=1",
					'title' => 'name'
				),
			),
			'article' => array(
				'article_article' => array(
					'fields' => array('name', 'preview', 'body', 'tags', 'termin'),
					'link' => '/%s/read.%s.htm',
					'where' => "publish=1",
					'title' => 'name'

				)
			),
			'news' => array(
				'news_news' => array(
					'fields' => array('name', 'preview', 'body'),
					'link' => '/%s/read.%s.htm',
					'where' => "publish=1",
					'title' => 'name'
				)
			)
		);
		$this->pages = array(
			'fields' => array('title', 'name', 'body'),
			'link' => '/%s.htm',
			'where' => "publish=1",
			'title' => 'title'
		);	
	}
	
	function getSearchResultRef($node, $action = 'index') {
		return $this->container->href(!empty($node['node_id']) ? $node['node_id_name'] : $this->name, $action, array($node['id']));
	}
	
	public function createCriteria($words, $fields) {
		$query = '';
		foreach ($fields as $field) {
			if (count($words) > 1) {
				$query0 = '';
				foreach ($words as $word) {
					$query0 .=  ($query0 ? ' AND ' : '').'('.$field." LIKE '%".$word."%')";
				}
				$query .=  ($query ? ' OR ' : '').($query0 ? '('.$query0.')' : '');
			} elseif (count($words) > 0) {
				$query .=  ($query ? ' OR ' : '').$field." LIKE '%".$words[0]."%'";
			}
		}
		return $query ? '('.$query.')' : '';
	}

	public function getTableSearchResults($words, $table, $options) {
		$ret = array();
		if (!$words) {
			return $ret;
		}
		$fields_text = implode(',',$options['fields']);
		$where = !empty($options['where']) ? ' AND '.$options['where'] : '';
		$search_query = $this->createCriteria($words, $options['fields']);
		$sql = "SELECT id,".$fields_text." FROM ".$table." WHERE ".$search_query.$where.' ORDER BY id';
		$items = $this->container->get('connection')->getItems('get_search_items', $sql);
		foreach ($items as $item) {
			if ($table == 'page_page') {
				$link = $this->container->getManager('Fuga:Common:Page')->getUrl($item);
			} else {
				$link = vsprintf($options['link'], array($options['nodeName'], $item['id']));
			}
			$ret[] = array (
				'link' => $link,
				'title' => $this->container->get('util')->cut_text(strip_tags($item[$options['title']], 300))
			);
		}
		return $ret;
	}

	public function getMorphoForm($text) {
		$morfWords = array();
		$words = explode(' ', $text);
		foreach ($words as $word) {
			$className = 'Fuga\\Component\\Search\\Stem'.ucfirst($this->container->get('router')->getParam('lang'));
			$stem = new $className();
			$word = $stem->russian($word);
			if (strlen($word) > 2) 
				$morfWords[] = $word;
		}
		return $morfWords;
	}
	
	function getResults($text) {
		$text = $this->getMorphoForm($text);
		$ret = array();
		$pages = $this->container->getItems('page_page', "publish=1 AND module_id<>0");
		if (is_array($pages)) {
			foreach ($pages as $node) {
				if (isset($this->options[$node['module_id_name']])) {
					$tables = $this->options[$node['module_id_name']];
					foreach ($tables as $tableName => $options) {
						$options['nodeName'] = $node['name']; 
						$results = $this->getTableSearchResults($text, $tableName, $options);
						$ret = array_merge($ret, $results);
					}
				}
			}
			$results = $this->getTableSearchResults($text, 'page_page', $this->pages);
			foreach ($results as $a) {
				$ret[] = $a;
			}
		} 
		return $ret;
	}
	
}
