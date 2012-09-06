<?php

    class CPageNavigation {

		public $limit				= '';
		
		private $_sTemplate; //template

		private $_sBaseURL			= './'; //ref
        private $_iPagesQuantity	= 0; //pages_cnt
		private $_iCurrentPage		= 0; //page
        private $_iEntityQuantity	= 0; //count
		private $_iRowPerPage		= 25;
        private $_iMaxDisplayPages	= 15; //max_display
		private $_oDBTable			= null;

		private $_sPageNavigationText = null;

		function __construct(&$oDBTable, $sBaseURL, $sCondition = '', $iRowPerPage = 25, $iCurrentPage = 1, $iMaxDisplayPages = 15, $sTemplateName = 'default') {
			global $db;
			$this->_oDBTable			= $oDBTable;
            $this->_iMaxDisplayPages	= $iMaxDisplayPages < 15 ? 15 : $iMaxDisplayPages;
			$this->_iRowPerPage			= $iRowPerPage;
            $this->_sBaseURL			= stristr($sBaseURL, '?') == '?' ? str_replace('?', '', $sBaseURL) : $sBaseURL;
			$this->_iCurrentPage		= $iCurrentPage;
			$this->setTemplate($sTemplateName);
            if ($iRowPerPage) {
                $this->_iCurrentPage = (int)$this->_iCurrentPage;
				$sDBTableName = $this->_oDBTable->getDBTableName();
				$sQuery = "
					SELECT
						COUNT(id) as quantity
					FROM
						$sDBTableName
					". ($sCondition ? 'WHERE '.$sCondition : '');
				$aItemCount = $db->getItem('get_count_entities', $sQuery);
                if ($aItemCount) {
                    $this->_iEntityQuantity = $aItemCount['quantity'];
                    $this->_iPagesQuantity = ceil($this->_iEntityQuantity / $this->_iRowPerPage);
					if ($this->_iPagesQuantity > 0) {
						if ($this->_iCurrentPage > $this->_iPagesQuantity) {
							$this->_iCurrentPage = 1;
							//throw new Exception('Ошибка обращения к странице. <a href="/">Начать с главной страницы</a>');
						}
						if ($this->_iCurrentPage < 1) {
							$this->_iCurrentPage = 1;
						}
					}
                    $this->limit = ($this->_iCurrentPage - 1) * $this->_iRowPerPage.', '.$this->_iRowPerPage;
					$this->min_rec = ($this->_iCurrentPage - 1) * $this->_iRowPerPage + 1;
					$this->max_rec = $this->_iCurrentPage == $this->_iPagesQuantity ? $this->_iEntityQuantity : ($this->_iCurrentPage - 1) * $this->_iRowPerPage + $this->_iRowPerPage;
                }
            }
        }
		
        public function getText() {
			global $smarty;
			$sReturn = '';
			if (!$this->_sPageNavigationText) {
				if ($this->_iPagesQuantity > 1) {
					if ($this->_iCurrentPage > 1) {
						$smarty->assign('prev_link', $this->getLinkURL($this->_iCurrentPage-1));
						$smarty->assign('begin_link', $this->getLinkURL(1));
					}
					if ($this->_iCurrentPage < $this->_iPagesQuantity) {
						$smarty->assign('next_link', $this->getLinkURL($this->_iCurrentPage+1));
						$smarty->assign('end_link', $this->getLinkURL($this->_iPagesQuantity-1));
					}
					$min_page = ($this->_iCurrentPage - ceil($this->_iMaxDisplayPages/2) > 1) ? $this->_iCurrentPage - ceil($this->_iMaxDisplayPages/2) : 1;
					$max_page = ($this->_iCurrentPage + ceil($this->_iMaxDisplayPages/2) < $this->_iPagesQuantity) ? $this->_iCurrentPage + ceil($this->_iMaxDisplayPages/2) : $this->_iPagesQuantity;
					if ($min_page-1 > 0)
						$smarty->assign('prevblock_link', $this->getLinkURL( $min_page-1));
					if ($max_page+1 < $this->_iPagesQuantity)
						$smarty->assign('nextblock_link', $this->getLinkURL( $min_page+1));
					$aPages = array();
					for ($k = $min_page; $k <= $max_page; $k++) {
						$aPages[] = array('name' => $k, 'ref' => $this->getLinkURL($k));
					}
					$smarty->assign('rec_count', $this->_iEntityQuantity);
					$smarty->assign('recs', $this->min_rec.' - '.$this->max_rec);
					$smarty->assign('page', $this->_iCurrentPage);
					$smarty->assign('pages', $aPages);
					$this->_sPageNavigationText = $smarty->fetch($this->_sTemplate);
				} else {
					$this->_sPageNavigationText = '&nbsp;';
				}
			}
			return $this->_sPageNavigationText;
        }

		public function getLinkURL($iPage, $sURLTemplate = '') {
			if(!$sURLTemplate) {
				$sURLTemplate = $this->_sBaseURL;
			}
			return str_replace(NAVIGATION_TEMPLATE_LINK_MASK, $iPage, $sURLTemplate);
		}

		public function setTemplate($sTemplateName) {
			$this->_sTemplate = 'service/pagenavigation/'.$sTemplateName.TEMPLATE_EXTENSION;
		}
    }

?>