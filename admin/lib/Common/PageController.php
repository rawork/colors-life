<?php

namespace Common;
	
class PageController extends Controller {
	
	private $template;
	
	public function __construct() {
		$this->template = $this->getTemplate();
	}

	public function show() {
		$this->setData();
		$this->setTemplateParams();
		$this->setSEOData();
		if ($data = $this->get('smarty')->fetch($this->template)) {
//			$GLOBALS['cache']->save($data, $GLOBALS['cur_page_id']);
			echo $data;
		} else {
			throw new \Exception('Template calculate error');
		}

	}

	private function setTemplateParams() {
		$vars = $this->get('container')->getVars();
		foreach ($vars as $name => $value) {
			$this->get('smarty')->assign($name, $value);
		}
	}

	private function setData() {
		if ($this->get('router')->hasParam('error')) {
			header($_SERVER['SERVER_PROTOCOL'].' '.$this->get('router')->getParam('error').' Not Found');
			$this->get('smarty')->assign('mainbody', $this->get('smarty')->fetch('service/'.$this->get('router')->getParam('lang').'/errorpage.'.$this->get('router')->getParam('error').'.tpl'));
			$this->get('smarty')->assign('title', 'Ошибка '.$this->get('router')->getParam('error'));
			$this->get('smarty')->assign('h1', 'Ошибка '.$this->get('router')->getParam('error'));
		} else {
			$this->get('smarty')->assign('mainbody', $this->get('tree')->getBody().' ');
			$this->get('smarty')->assign('title', strip_tags($this->get('tree')->getTitle()));
			if ($h1 = $this->get('tree')->getH1()) {
				$this->get('smarty')->assign('h1', $h1);
//				$this->get('smarty')->assign('h1_collage', $this->get('tree')->getparams['node']['h1_img']);
//				$this->get('smarty')->assign('h1_collage_width', isset($this->get('tree')->record['h1_img_width']) ? $this->get('tree')->record['h1_img_width'] : '');
//				$this->get('smarty')->assign('h1_collage_height', isset($this->get('tree')->record['h1_img_height']) ? $this->get('tree')->record['h1_img_height'] : '');
			}
		}
	}

	private function setSEOData() {
		if ($title = $this->get('meta')->getTitle()) 
		{
			$this->get('smarty')->assign('title', $title);
		}
		
		$this->get('smarty')->assign('meta', $this->get('meta')->getMeta());
	}

	private function getTemplate() {
		$where = "(tr.type='0' AND tr.cond='')";
		$where .= " OR (tr.type='T' AND ((tr.date_beg > 0 AND tr.date_beg <= NOW()) OR tr.date_beg = 0) AND (tr.date_end >= NOW() OR tr.date_end = 0))";
		//$where .= " OR (tr.type='U' AND LOCATE(tr.cond,'".$this->get('connection')->escapeStr($_SERVER['REQUEST_URI'])."')>0)";
		if ($this->get('router')->hasParam('nodeName')) {
			$where .= " OR (tr.type='F' AND tr.cond='".$this->get('router')->getParam('nodeName')."')";
		}
		$q = "SELECT tt.template FROM templates_templates tt JOIN templates_rules tr ON tt.id=tr.template_id WHERE tr.lang='".$this->get('router')->getParam('lang')."' AND(".$where.") ORDER BY ord DESC";
//		var_dump($q);
		if ($template = $this->get('container')->getNativeItem($q)){
			if (!empty($template['template']) && file_exists($GLOBALS['PRJ_DIR'].$template['template'])) {
				return $GLOBALS['PRJ_DIR'].$template['template'];
			} else {
				throw new \Exception('Template file error');
			}
		} else {
			throw new \Exception('Template settings error');
		}
	}

}
