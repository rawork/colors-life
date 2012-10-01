<?php

namespace Fuga\Component;

class Paginator {

	public $limit			= '';

	private $template; //template

	private $baseUrl		= './'; //ref
	private $quantity		= 0; //pages_cnt
	private $currentPage	= 0; //page
	private $entityQuantity	= 0; //count
	private $rowPerPage		= 25;
	private $maxDisplayPages= 15; //max_display
	private $table			= null;

	private $navigationContent = null;
	
	public function paginate($table, $baseUrl, $where = '', $rowPerPage = 25, $currentPage = 1, $maxDisplayPages = 10, $templateName = 'default') {
		$this->table			= $table;
		$this->maxDisplayPages	= $maxDisplayPages;
		$this->rowPerPage			= $rowPerPage;
		$this->baseUrl			= stristr($baseUrl, '?') == '?' ? str_replace('?', '', $baseUrl) : $baseUrl;
		$this->currentPage		= $currentPage;
		$this->setTemplate($templateName);
		if ($rowPerPage) {
			$this->currentPage = (int)$this->currentPage;
			$tableName = $this->table->getDBTableName();
			$query = "
				SELECT
					COUNT(id) as quantity
				FROM
					$tableName
				". ($where ? 'WHERE '.$where : '');
			$aItemCount = $this->get('connection')->getItem('get_count_entities', $query);
			if ($aItemCount) {
				$this->entityQuantity = $aItemCount['quantity'];
				$this->quantity = ceil($this->entityQuantity / $this->rowPerPage);
				if ($this->quantity > 0) {
					if ($this->currentPage > $this->quantity) {
						$this->currentPage = 1;
						//throw new \Exception('Ошибка обращения к странице. <a href="/">Начать с главной страницы</a>');
					}
					if ($this->currentPage < 1) {
						$this->currentPage = 1;
					}
				}
				$this->limit = ($this->currentPage - 1) * $this->rowPerPage.', '.$this->rowPerPage;
				$this->min_rec = ($this->currentPage - 1) * $this->rowPerPage + 1;
				$this->max_rec = $this->currentPage == $this->quantity ? $this->entityQuantity : ($this->currentPage - 1) * $this->rowPerPage + $this->rowPerPage;
			}
		}
	}

	public function render() {
		if (!$this->navigationContent) {
			if ($this->quantity > 1) {
				if ($this->currentPage > 1) {
					$this->get('smarty')->assign('prev_link', $this->getLinkURL($this->currentPage-1));
					$this->get('smarty')->assign('begin_link', $this->getLinkURL(1));
				}
				if ($this->currentPage < $this->quantity) {
					$this->get('smarty')->assign('next_link', $this->getLinkURL($this->currentPage+1));
					$this->get('smarty')->assign('end_link', $this->getLinkURL($this->quantity-1));
				}
				if ($this->currentPage >= $this->quantity - ceil($this->maxDisplayPages/2) && $this->quantity > $this->maxDisplayPages) {
					$min_page = $this->quantity - $this->maxDisplayPages + 1;
					$max_page = $this->quantity;
				} elseif (($this->currentPage > ceil($this->maxDisplayPages/2)) 
						&& ($this->currentPage < $this->quantity - ceil($this->maxDisplayPages/2))) {
					$min_page = $this->currentPage - ceil($this->maxDisplayPages/2) + 1;
					$max_page = $this->currentPage + ceil($this->maxDisplayPages/2);
				} else {
					$min_page = 1;
					$max_page = $this->maxDisplayPages > $this->quantity ? $this->quantity : $this->maxDisplayPages;
				}
				$aPages = array();
				for ($k = $min_page; $k <= $max_page; $k++) {
					$aPages[] = array('name' => $k, 'ref' => $this->getLinkURL($k));
				}
				$this->get('smarty')->assign('totalItems', $this->entityQuantity);
				$this->get('smarty')->assign('currentItems', $this->min_rec.' - '.$this->max_rec);
				$this->get('smarty')->assign('page', $this->currentPage);
				$this->get('smarty')->assign('pages', $aPages);
				$this->navigationContent = $this->get('smarty')->fetch($this->template);
			} else {
				$this->navigationContent = '&nbsp;';
			}
		}
		return $this->navigationContent;
	}

	public function getLinkURL($page, $urlTemplate = '') {
		if(!$urlTemplate) {
			$urlTemplate = $this->baseUrl;
		}
		return str_replace('###', $page, $urlTemplate);
	}

	public function setTemplate($templateName) {
		$this->template = 'service/paginator/'.$templateName.'.tpl';
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
