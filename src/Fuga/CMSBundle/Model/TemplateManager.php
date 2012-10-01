<?php

namespace Fuga\CMSBundle\Model;

class TemplateManager extends ModelManager {
	
	protected $entityTable = 'templates_templates';

	public function getByNode($nodeName) {
		$where = "(tr.type='0' AND tr.cond='')";
		$where .= " OR (tr.type='T' AND ((tr.date_beg > 0 AND tr.date_beg <= NOW()) OR tr.date_beg = 0) AND (tr.date_end >= NOW() OR tr.date_end = 0))";
		//$where .= " OR (tr.type='U' AND LOCATE(tr.cond,'".$this->get('connection')->escapeStr($_SERVER['REQUEST_URI'])."')>0)";
		$where .= " OR (tr.type='F' AND tr.cond='".$nodeName."')";
		$query = "SELECT tt.template FROM templates_templates tt JOIN templates_rules tr ON tt.id=tr.template_id WHERE tr.lang='".$this->get('router')->getParam('lang')."' AND (".$where.") ORDER BY ord DESC";
		if ($template = $this->get('connection')->getItem('template', $query)){
			if (!empty($template['template']) && file_exists($GLOBALS['PRJ_DIR'].$template['template'])) {
				return $GLOBALS['PRJ_DIR'].$template['template'];
			} else {
				throw new \Exception('Пустой шаблон страницы');
			}
		} else {
			throw new \Exception('Отсутствует активный шаблон для запрашиваемой страницы');
		}
	}
}