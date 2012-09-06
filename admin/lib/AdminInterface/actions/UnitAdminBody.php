<?php
	inc_lib('Pages.php');
    class UnitAdminBody {
        public $uai;
        public $t;
        public $baseRef;
        public $searchRef;
        public $fullRef;
		public $action;
		protected $search_url;
		protected $search_sql;
        public function __construct(&$unitAdminInterface) {
            $this->uai = $unitAdminInterface;
            $this->baseRef = $unitAdminInterface->getBaseTableRef();
            if (is_object($this->t = $this->uai->getBaseTable())) {
                $this->search_url = $this->t->getSeachURL();
				$this->search_sql = $this->t->getSeachSQL();
				$this->searchRef0 = $this->baseRef.($this->search_url ? '&'.$this->search_url : '');
                $this->searchRef = $this->searchRef0.(CUtils::_getVar('rpp') ? '&rpp='.CUtils::_getVar('rpp') : '');
                $this->fullRef0 = $this->searchRef0.(CUtils::_getVar('page') ? '&page='.CUtils::_getVar('page') : '');
				$this->fullRef = $this->searchRef.(CUtils::_getVar('page') ? '&page='.CUtils::_getVar('page') : '');
            }
			
        }
        protected function getTableHeader() {
            return '<table width="100%" cellpadding="0" cellspacing="0" class="tfields">';
        }
        protected function messageAction($msg) {
            return $this->uai->messageAction($msg, $this->fullRef);
        }
        protected function getTemplateName(&$v) {
            return $GLOBALS['auth']->isSuperuser()  ? ' <span class="sfnt">{'.strtolower($v['name']).'}</span>' : '';
        }
		/* Default method  */
		public function getText() {
			$body = 'Вызов неизвестной функции';
			return $body;
		}
		/* Formed help-link */
		protected function getHelpLink($k, $v) {
        	return !empty($v['help']) ? '&nbsp;<img src="'.$GLOBALS['THEME_REF'].'/img/icons/icon_help.gif" border="0" alt="'.$v['help'].'" title="'.$v['help'].'">' : '';
    	}
		
		protected function getOperationsBar ($links = array()) {
			$ret = '';
			if (sizeof($links) > 0) {
			$ret = '<table align="center" cellpadding="0" cellspacing="0" border="0" class="contextmenu2">
	<tr class="top">
<td class="left"><div class="empty"></div></td>
<td><div class="empty"></div></td>
<td class="right"><div class="empty"></div></td>
</tr>
<tr>
<td class="left"><div class="empty"></div></td>
<td class="content">
<table cellpadding="2" cellspacing="0" border="0">
<tr>';
			foreach ($links as $link) {
				$ret .= '<td><div class="section-separator"></div></td>
<td align="center"><a href="'.$link['ref'].'" class="context-button">'.$link['name'].'</a></td>';
			}
            $ret .= '</tr></table></td>
<td class="right"><div class="empty"></div></td></tr>
<tr class="bottom">
<td class="left"><div class="empty"></div></td>
<td><div class="empty"></div></td>
<td class="right"><div class="empty"></div></td>
</tr>
<tr class="bottom-all">
<td class="left"><div class="empty"></div></td>
<td><div class="empty"></div></td>
<td class="right"><div class="empty"></div></td>
</tr></table><br>';
			}
            return $ret;
			
		}
    }
?>