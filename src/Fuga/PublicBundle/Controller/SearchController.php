<?php

namespace Fuga\PublicBundle\PublicController;

// TODO Cache of search results

class SearchController extends PublicController {
	
	function __construct() {
		parent::__construct('search');
	}

	function getContent() {
		$searchText = $this->get('util')->_getVar('text', false, '');
		$this->get('smarty')->assign('searchText', $searchText);
		$content = $this->get('smarty')->fetch('service/search/'.$this->lang.'/search.form.tpl');
		if ($searchText) {
			$results = $this->get('search')->getResults($searchText);
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
				$content .= $this->get('smarty')->fetch('service/search/'.$this->lang.'/search.list.tpl');
			} else {
				$content .= $this->get('smarty')->fetch('service/search/'.$this->lang.'/search.empty.tpl');
			}
		}
		return $content;
	}
}
