<?php

namespace Fuga\Component;

class Paginator {

	public $limit			= '';
	
	private $container;
	private $template; 

	private $baseUrl		= './'; 
	private $quantity		= 0; 
	private $currentPage	= 0; 
	private $entityQuantity	= 0; 
	private $rowPerPage		= 25;
	private $maxDisplayPages= 15; 
	private $table			= null;

	private $content;
	
	public function __construct($container) {
		$this->container = $container;
	}
	
	public function paginate($table, $baseUrl, $where = '', $rowPerPage = 25, $currentPage = 1, $maxDisplayPages = 10, $templateName = 'default') {
		$this->content = null;
		$this->table			= $table;
		$this->maxDisplayPages	= $maxDisplayPages;
		$this->rowPerPage			= $rowPerPage;
		$this->baseUrl			= stristr($baseUrl, '?') == '?' ? str_replace('?', '', $baseUrl) : $baseUrl;
		$this->currentPage		= $currentPage;
		$this->setTemplate($templateName);
		if ($rowPerPage) {
			$this->currentPage = (int)$this->currentPage;
			$tableName = $this->table->dbName();
			try {
				$sql = "
					SELECT
						COUNT(id) as quantity
					FROM
						$tableName
					". ($where ? 'WHERE '.$where : '');
				$stmt = $this->container->get('connection1')->prepare($sql);
				$stmt->execute();
				$count = $stmt->fetch();
			} catch (\Exception $e) {
				$count = 0;
			}
			if ($count) {
				$this->entityQuantity = $count['quantity'];
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
		if (!$this->content) {
			if ($this->quantity > 1) {
				if ($this->currentPage > 1) {
					$prev_link = $this->getLink($this->currentPage-1);
					$begin_link = $this->getLink(1);
				}
				if ($this->currentPage < $this->quantity) {
					$next_link = $this->getLink($this->currentPage+1);
					$end_link = $this->getLink($this->quantity);
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
				$pages = array();
				for ($k = $min_page; $k <= $max_page; $k++) {
					$pages[] = array('name' => $k, 'ref' => $this->getLink($k));
				}
				$totalItems = $this->entityQuantity;
				$currentItems = $this->min_rec.' - '.$this->max_rec;
				$page = $this->currentPage;
				$max_page = $this->quantity;
				$this->content = $this->container->get('templating')->render(
					$this->template, 
					compact('prev_link', 'begin_link', 'next_link', 'end_link', 'totalItems', 'currentItems', 'page', 'pages', 'max_page')
				);
			} else {
				$this->content = '&nbsp;';
			}
		}
		return $this->content;
	}

	public function getLink($page, $url = '') {
		if(!$url) {
			$url = $this->baseUrl;
		}
		return str_replace('###', $page, $url);
	}

	public function setTemplate($name) {
		$this->template = 'paginator/'.$name.'.tpl';
	}
	
}
