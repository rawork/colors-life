<?php
	
class Page {
	protected $unit;
	protected $smarty;
	protected $template;
	public function __construct($unit) {
	global $smarty;
		$this->template = $this->getTemplate();
		$this->smarty = $smarty;
		$this->unit = $unit;
		$this->setData();
		$this->setTempVars();
		$this->setSEOData();
	}

	public function show() {
		if ($data = $this->smarty->fetch($this->template)) {
//			$GLOBALS['cache']->save($data, $GLOBALS['cur_page_id']);
			echo $data;
		} else {
			throw new Exception('Template calculate error');
		}

	}

	protected function setTempVars() {
		$vars = $GLOBALS['rtti']->getVars();
		foreach ($vars as $name => $value) {
			$this->smarty->assign($name, $value);
		}
	}

	protected function setData() {
		if (!empty($GLOBALS['urlprops']['error'])) {
			header('HTTP/1.1 '.$GLOBALS['urlprops']['error'].' Not Found');
			$this->smarty->assign('mainbody', $this->smarty->fetch('service/'.CUtils::_sessionVar('lang', false, 'ru').'/errorpage.'.$GLOBALS['urlprops']['error'].'.tpl'));
			$this->smarty->assign('title', 'Ошибка 404');
			$this->smarty->assign('h1', 'Ошибка 404');
		} elseif (is_object($this->unit)) {
			$this->smarty->assign('mainbody', $this->unit->getBody().' ');
			$this->smarty->assign('title', strip_tags($this->unit->getTitle()));
			if ($h1 = $this->unit->getH1()) {
				$this->smarty->assign('h1', $h1);
				$this->smarty->assign('h1_collage', $this->unit->props['node']['h1_img']);
				$this->smarty->assign('h1_collage_width', isset($this->unit->record['h1_img_width']) ? $this->unit->record['h1_img_width'] : '');
				$this->smarty->assign('h1_collage_height', isset($this->unit->record['h1_img_height']) ? $this->unit->record['h1_img_height'] : '');
			}
		}
	}

	protected function setSEOData() {
		inc_lib('components/MetaUnit.php');
		$meta = new MetaUnit('meta');
		if ($title = $meta->getTitle()) {
			$this->smarty->assign('title', $title);
		}
		$this->smarty->assign('meta', $meta->getMeta());

	}

	protected function getTemplate() {
		$where = "(tr.type='0' AND tr.cond='')";
		$where .= " OR (tr.type='T' AND ((tr.date_beg > 0 AND tr.date_beg <= NOW()) OR tr.date_beg = 0) AND (tr.date_end >= NOW() OR tr.date_end = 0))";
		//$where .= " OR (tr.type='U' AND LOCATE(tr.cond,'".$GLOBALS['db']->escapeStr($_SERVER['REQUEST_URI'])."')>0)";
		if (!empty($GLOBALS['urlprops']['node']['name'])) {
			$where .= " OR (tr.type='F' AND tr.cond='".$GLOBALS['urlprops']['node']['name']."')";
		}
		$q = "SELECT tt.template FROM templates_templates tt JOIN templates_rules tr ON tt.id=tr.template_id WHERE tr.lang='".$GLOBALS['urlprops']['lang']."' AND(".$where.") ORDER BY ord DESC";
//		var_dump($q);
		if ($_template = $GLOBALS['rtti']->getNativeItem($q)){
			if (!empty($_template['template']) && file_exists($GLOBALS['PRJ_DIR'].$_template['template'])) {
				return $GLOBALS['PRJ_DIR'].$_template['template'];
			} else {
				throw new Exception('Template file error');
			}
		} else {
			throw new Exception('Template settings error');
		}
	}

}
