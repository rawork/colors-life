<?php
    inc_lib('components/Unit.php');
    class SearchUnit extends Unit {
        public $text;
		function __construct($props = array()) {
            parent::__construct('search', $props);
        }

        function getResults($text) {
            $ret = array();
			$props = $this->props;
			$trees = $GLOBALS['utree']->tables['tree']->getArraysWhere("publish='on' AND module_id<>0");
			if (is_array($trees)) {
            	foreach ($trees as $tr) {
                	if (!empty($tr['module_id_name'])) {
						$props['dir_uri'] = $props['uri'] = $tr['name'];
						$props['dir_id'] = $tr['id'];
						if (inc_u($tr['module_id_name'])) {
							$moduleName = ucfirst($tr['module_id_name']).'Unit';
           	    			$unit = new $moduleName($props);
           					$results = $unit->getSearchResults($text);
	    		       		foreach ($results as $a)
   			            		$ret[] = $a;
						} else {
		       	        	$unit = new Unit($tr['module_id_name'], $props);
							foreach ($unit->tables as $t) {
								if (!$t->props['is_search']) continue;
								$results = $unit->getTableSearchResults($text, $t->name, "publish='on'", $t->props['search_prefix']);
								foreach ($results as $a)
		            				$ret[] = $a;
							}
						}
					}
				}
           		$results = $GLOBALS['utree']->getSearchResults($text);
	    	    foreach ($results as $a) {
   		        	$ret[] = $a;
	       	    }
			} 
            return $ret;
        }
		
        function getBody() {
        	$ret = $this->smarty->fetch('service/'.$this->props['lang'].'/search.form.tpl');
			$max_per_page = 20;
        	$this->page = CUtils::_getVar('page', true, 1);
            if (CUtils::_getVar('text')) {
				$search_array = array();
				$simple_search_array = (explode(' ', CUtils::_getVar('text')));
				foreach ($simple_search_array as $ssa) {
					inc_lib('tools/stemming/stemming.php');
					$ssa = PorterStem::stemming($ssa);
					$search_array[] = $ssa;
				}
				if (count($search_array)){
					$results = $this->getResults($search_array);
				} else {
					$results = $this->getResults(addslashes($this->text));
				}
                $this->smarty->assign('search_text', addslashes(CUtils::_getVar('text')));
                if (sizeof($results) > 0) {
                	$pages_cnt = ceil(sizeof($results)/$max_per_page);
                	if ($pages_cnt > 1){
                		$pages = '<div>';
						if ($this->page > 1) {
							$ref = '?text='.urlencode(CUtils::_getVar('text')).'&page='.($this->page-1);
							$pages .= '<a title="назад" href="'.$ref.'">&larr;</a>';
               	    	}
    	            	for ($i = 1; $i<=$pages_cnt; $i++){
                	    	$pages .= $i == $this->page ? ' '.$i.' ' : ' <a href="?text='.urlencode(CUtils::_getVar('text')).'&page='.$i.'">'.$i.'</a> ';
	                    }
						if ($this->page < $pages_cnt) {
							$ref = '?text='.urlencode(CUtils::_getVar('text')).'&page='.($this->page+1);
							$pages .= '<a title="вперед" href="'.$ref.'">&rarr;</a>';
		                }
	                    $pages .= '</div>';
	                    $this->smarty->assign('ptext', $pages);
                    }
                    if ($this->page == $pages_cnt &&  (sizeof($results) % $max_per_page) > 0) {
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
                   	$this->smarty->assign('items', $items);
                    $ret .= $this->smarty->fetch('service/'.$this->props['lang'].'/search.list.tpl');
                } else {
                    $ret .= $this->smarty->fetch('service/'.$this->props['lang'].'/search.no.tpl');
                }
            }
			return $ret;
        }
    }
?>