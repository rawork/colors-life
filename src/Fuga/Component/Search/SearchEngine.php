<?php

namespace Fuga\Component\Search;

class SearchEngine {
	
	private $modules;
	private $pages;
	
	public function __construct() {
		$this->modules = array(
			'catalog' => array(
				'catalog_stuff' => array(
					'fields' => array('name', 'articul', 'short_description', 'description', 'spec_description', 'tags'),
					'link' => '/%s/stuff.%s.htm',
					'where' => "publish='on'",
					'title' => 'name'
				),
				'catalog_categories' => array(
					'fields' => array('name', 'description'),
					'link' => '/%s/index.%s.htm',
					'where' => "publish='on'",
					'title' => 'name'
				),
				'catalog_producers' => array(
					'fields' => array('name', 'description', 'country'),
					'link' => '/%s/brand.%s.htm',
					'where' => "publish='on'",
					'title' => 'name'
				),
			),
			'articles' => array(
				'articles_articles' => array(
					'fields' => array('name', 'announce', 'body', 'tags', 'termin'),
					'link' => '/%s/read.%s.htm',
					'where' => "publish='on'",
					'title' => 'name'

				)
			),
			'news' => array(
				'news_news' => array(
					'fields' => array('name', 'announce', 'body'),
					'link' => '/%s/read.%s.htm',
					'where' => "publish='on'",
					'title' => 'name'
				)
			)
		);
		$this->pages = array(
			'fields' => array('title', 'name', 'body'),
			'link' => '/%s.htm',
			'where' => "publish='on'",
			'title' => 'title'
		);	
	}
	
	function getSearchResultRef($a, $methodName = '') {
		return $this->get('container')->href(!empty($a['dir_id']) ? $a['dir_id_name'] : $this->name, $methodName, array($a['id']));
	}

	private function getTableSearchResults($words, $tableName, $options) {
		$ret = array();
		if (!$words) {
			return $ret;
		}
		$fields_text = implode(',',$options['fields']);
		$where = !empty($options['where']) ? ' AND '.$options['where'] : '';
		$search_query = '';
		foreach ($options['fields'] as $field) {
			if (count($words) > 1) {
				$search_query0 = '';
				foreach ($words as $word) {
					$search_query0 .=  ($search_query0 ? ' AND ' : '').'('.$field." LIKE '%".$word."%')";
				}
				$search_query .=  ($search_query ? ' OR ' : '').($search_query0 ? '('.$search_query0.')' : '');
			} else {
				$search_query .=  ($search_query ? ' OR ' : '').$field." LIKE '%".$words[0]."%'";
			}
		}
		$sql = "SELECT id,".$fields_text." FROM ".$tableName." WHERE (".$search_query.') '.$where.' ORDER BY id';
		$items = $this->get('connection')->getItems('get_search_items', $sql);
		foreach ($items as $item) {
			if ($tableName == 'page_page') {
				$link = $this->get('page')->getUrl($item);
			} else {
				$link = vsprintf($options['link'], array($options['nodeName'], $item['id']));
			}
			$ret[] = array (
				'link' => $link,
				'title' => $this->get('util')->cut_text(strip_tags($item[$options['title']], 300))
			);
		}
		return $ret;
	}

	private function getMorphoForm($text) {
		$morfWords = array();
		$words = explode(' ', $text);
		foreach ($words as $word) {
			$className = 'Fuga\\Component\\Search\\Stem'.ucfirst($this->get('router')->getParam('lang'));
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
		$pages = $this->get('container')->getItems('page_page', "publish='on' AND module_id<>0");
		if (is_array($pages)) {
			foreach ($pages as $node) {
				if (isset($this->modules[$node['module_id_name']])) {
					$tables = $this->modules[$node['module_id_name']];
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
	
	public function get($name) 
	{
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
