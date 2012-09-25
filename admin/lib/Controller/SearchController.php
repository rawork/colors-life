<?php

namespace Controller;

class SearchController extends Controller {
	public $text;
	function __construct() {
		parent::__construct('search');
		$this->modules = array(
			'catalog' => array(
				'catalog_stuff' => array(
					'fields' => array('name', 'articul', 'short_description', 'description', 'spec_description', 'tags'),
					'link' => '/catalog/stuff.###.htm',
					'where' => "publish='on'",
					'title' => 'name'
				),
				'catalog_categories' => array(
					'fields' => array('name', 'description'),
					'link' => '/catalog/index.###.htm',
					'where' => "publish='on'",
					'title' => 'name'
				),
				'catalog_producers' => array(
					'fields' => array('name', 'description', 'country'),
					'link' => '/catalog/brand.###.htm',
					'where' => "publish='on'",
					'title' => 'name'
				),
			),
			'articles' => array(
				'articles_articles' => array(
					'fields' => array('name', 'announce', 'body', 'tags', 'termin'),
					'link' => '/articles/read.###.htm',
					'where' => "publish='on'",
					'title' => 'name'

				)
			),
			'news' => array(
				'news_news' => array(
					'fields' => array('name', 'announce', 'body'),
					'link' => '/news/read.###.htm',
					'where' => "publish='on'",
					'title' => 'name'
				)
			)
		);
	}

	function getResults($text) {
		$ret = array();
		$trees = $this->get('tree')->tables['tree']->getArraysWhere("publish='on' AND module_id<>0");
		if (is_array($trees)) {
			foreach ($trees as $node) {
				if (!empty($node['module_id_name']) && isset($this->modules[$node['module_id_name']])) {
					$tables = $this->modules[$node['module_id_name']];

					foreach ($tables as $tableName => $options) {
						$results = $this->getTableSearchResults($text, $tableName, $options);
						foreach ($results as $a)
							$ret[] = $a;
					}
				}
			}
			$results = $this->get('tree')->getSearchResults($text);
			foreach ($results as $a) {
				$ret[] = $a;
			}
		} 
		return $ret;
	}

	function getTableSearchResults($words, $tableName, $options) {
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
		foreach ($items as $a) {
			$ret[] = array (
				'ref' => str_replace('###', $a['id'], $options['link']),
				'text' => $this->get('util')->cut_text(strip_tags($a[$options['title']], 150))
			);
		}
		return $ret;
	}

	function getMorfSearchText($text) {
		$morfWords = array();
		$words = explode(' ', $text);
		foreach ($words as $word) {
			$stem = new \Common\Search\Stem();
			$word = $stem->russian($word);
			if (strlen($word) > 2) 
				$morfWords[] = $word;
		}
		return $morfWords;
	}

	function getBody() {
		$html = $this->get('smarty')->fetch('service/'.$this->lang.'/search.form.tpl');
		$searchText = $this->get('util')->_getVar('text', false, '');
		if ($searchText) {
			$results = $this->getResults($this->getMorfSearchText($searchText));
			if (count($results)) {
				$this->get('smarty')->assign('search_text', addslashes($searchText));
				$this->page = $this->get('util')->_getVar('page', true, 1);
				$max_per_page = 20;
				$pages_quantity = ceil(count($results)/$max_per_page);
				if ($pages_quantity > 1){
					$pages = '<div>';
					if ($this->page > 1) {
						$ref = '?text='.urlencode($this->get('util')->_getVar('text')).'&page='.($this->page-1);
						$pages .= '<a title="назад" href="'.$ref.'">&larr;</a>';
					}
					for ($i = 1; $i<=$pages_quantity; $i++){
						$pages .= $i == $this->page ? ' '.$i.' ' : ' <a href="?text='.urlencode($this->get('util')->_getVar('text')).'&page='.$i.'">'.$i.'</a> ';
					}
					if ($this->page < $pages_quantity) {
						$ref = '?text='.urlencode($this->get('util')->_getVar('text')).'&page='.($this->page+1);
						$pages .= '<a title="вперед" href="'.$ref.'">&rarr;</a>';
					}
					$pages .= '</div>';
					$this->get('smarty')->assign('ptext', $pages);
				}
				if ($this->page == $pages_quantity &&  (sizeof($results) % $max_per_page) > 0) {
					$max_per_page_cur = count($results) % $max_per_page;
				} else {
					$max_per_page_cur = $max_per_page;
				}
				$items = array();
				for ($i = 1; $i <= $max_per_page_cur; $i++) {
					$j = $i+($this->page-1)*$max_per_page;
					$results[$j-1]['num'] = $j;
					$items[] = $results[$j-1];
				}
				$this->get('smarty')->assign('items', $items);
				$html .= $this->get('smarty')->fetch('service/'.$this->lang.'/search.list.tpl');
			} else {
				$html .= $this->get('smarty')->fetch('service/'.$this->lang.'/search.no.tpl');
			}
		}
		return $html;
	}
}
