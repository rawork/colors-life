<?php

namespace AdminInterface\Action;

class Action {
	public $uai;
	public $dataTable;
	public $baseRef;
	public $searchRef;
	public $fullRef;
	public $action;
	protected $search_url;
	protected $search_sql;
	protected $tableParams;
	
	public function __construct(&$adminController) {
		$this->uai = $adminController;
		$this->baseRef = $this->uai->getBaseTableRef();
		$this->searchRef = $this->baseRef;
		$this->fullRef = $this->searchRef.($this->get('util')->_getVar('page') ? '?page='.$this->get('util')->_getVar('page') : '');
		if (is_object($this->dataTable = $this->uai->getBaseTable())) {
			if ($_SERVER['REQUEST_METHOD'] != 'POST' && $tableParams = $this->get('util')->_sessionVar($this->dataTable->getDBTableName())) {
				$this->tableParams = json_decode(stripslashes($tableParams), true);
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				if ($this->get('util')->_postVar('cansel_filter')) {
					unset($_SESSION[$this->dataTable->getDBTableName()]);
				} elseif ($this->search_url = $this->dataTable->getSearchURL()) {
					parse_str($this->search_url, $this->tableParams);
					$_SESSION[$this->dataTable->getDBTableName()] = json_encode($this->tableParams);
				}
				header('location: '.$this->baseRef);
			}
			if (is_array($this->tableParams)) {
				foreach ($this->tableParams as $key => $value) {
					$_REQUEST[$key] = $value;
				}
			}
			$this->search_sql = $this->dataTable->getSearchSQL();
		}

	}
	protected function getTableHeader() {
		return '<table width="100%" cellpadding="0" cellspacing="0" class="tfields">';
	}
	protected function messageAction($msg) {
		return $this->uai->messageAction($msg, $this->fullRef);
	}
	protected function getTemplateName(&$v) {
		return $this->get('security')->isSuperuser()  ? ' <span class="sfnt">{'.strtolower($v['name']).'}</span>' : '';
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
