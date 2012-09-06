<?php

    class Pages {
        public $limit;
		public $template;
		private $ref;
        private $pages_cnt;
		private $page;
        private $count;
        private $max_display;
		function __construct(&$table, $ref, $a, $rpp = '', $page = 1, $max_display = 15, $template = 'pages.admin.tpl') {
            $this->max_display = $max_display < 15 ? 15 : $max_display;
            $this->ref = stristr($ref, '?') == '?' ? str_replace('?', '', $ref) : $ref;
			$this->page = $page;
			$this->template = 'service/'.$template;
            if (!empty($rpp)) {
                $this->page = intval($this->page);
                $a['select'] = 'COUNT(*) as cnt';
                $table->select($a);
                if ($a = $table->getNextArray()) {
                    $this->count = $a['cnt'];
                    $this->pages_cnt = intval($this->count / $rpp);
                    if ($this->count % $rpp != 0) {
                        $this->pages_cnt++;
                    }
                    if ($this->page > $this->pages_cnt) {
                        $this->page = $this->pages_cnt;
                    }
                    if ($this->page < 1) {
                        $this->page = 1;
                    }
                    $this->limit = ($this->page - 1) * $rpp.', '.$rpp;
					$this->min_rec = ($this->page - 1)*$rpp+1;
					$this->max_rec = $this->page == $this->pages_cnt ? $this->count : ($this->page - 1)*$rpp+$rpp;
                }
            } else {
                $this->pages_cnt = 0;
                $this->limit = '';
            }
        }
		
        public function getText() {
		global $smarty;
			$ret = '';
			if ($this->pages_cnt > 1 && $this->page > 0 && $this->page <= $this->pages_cnt) {
				if ($this->page > 1) {
					$smarty->assign('prev_link', str_replace('###', $this->page-1, $this->ref));
					$smarty->assign('begin_link', str_replace('###', 1, $this->ref));
				}
				if ($this->page < $this->pages_cnt) {
					$smarty->assign('next_link', str_replace('###', $this->page+1, $this->ref));
					$smarty->assign('end_link', str_replace('###', $this->pages_cnt, $this->ref));
				}
				$min_page = ($this->page - ceil($this->max_display/2) > 1) ? $this->page - ceil($this->max_display/2) : 1;
				$max_page = ($this->page + ceil($this->max_display/2) < $this->pages_cnt) ? $this->page + ceil($this->max_display/2) : $this->pages_cnt;
				if ($min_page-1 > 0)
					$smarty->assign('prevblock_link', str_replace('###', $min_page-1, $this->ref));
				if ($max_page+1 < $this->pages_cnt)
					$smarty->assign('nextblock_link', str_replace('###', $max_page+1, $this->ref));
				$pages = array();
				for ($k = $min_page; $k <= $max_page; $k++)
					$pages[] = array('name' => $k, 'ref' => str_replace('###', $k, $this->ref));
				$smarty->assign('rec_count', $this->count);
				$smarty->assign('recs', $this->min_rec.' - '.$this->max_rec);
				$smarty->assign('page', $this->page);
				$smarty->assign('pages', $pages);
				$ret = $smarty->fetch($this->template);
			}
			return $ret;
        }
    }

?>