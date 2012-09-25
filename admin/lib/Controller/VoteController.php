<?php

namespace Controller;

class VoteController extends Controller {
	function __construct() {
		parent::__construct('vote');
	}

	function getForm() {
		$vote = $this->tables['questions']->getItem(!empty($this->params['frmname']) ? "name='".$this->params['frmname']."' AND date_beg<'".date('Y-m-d H:i:s')."' AND date_end>'".date('Y-m-d H:i:s')."'" : "publish='on' AND date_beg<'".date('Y-m-d H:i:s')."' AND date_end>'".date('Y-m-d H:i:s')."'");
		if (sizeof($vote) > 0) {
			$this->smarty->assign('a', $this->tables['answers']->getArraysWhere('question_id='.$vote['id']." AND publish='on'"));
			$this->smarty->assign('q', $vote);
			return $this->smarty->fetch('service/'.$this->lang.'/vote.form.tpl');
		} else {
			return '';
		}

	}

	function updateData() {
		$answer_id = $this->get('util')->_postVar('vote', true, 0);
		$vote_id = $this->get('util')->_postVar('vote_question', true, 0);
		if ($vote_id && $answer_id){
			$cache = 0;
			$q = $this->tables['questions']->getItem($vote_id);
			if ($q['lmt'] == 1) {
				$cache = $this->tables['cache']->getCount("sessionid='".session_id()."' AND time>".(time()-$q['step']));
			} elseif ($q['lmt'] == 2) {
				$cache = $this->tables['cache']->getCount("(sessionid='".session_id()."' OR ip='".$_SERVER['REMOTE_ADDR']."') AND time>".(time()-$q['step']));
			}
			if (!$cache) {
				$this->tables['cache']->insert('ip,sessionid,question_id,time', "'".$_SERVER['REMOTE_ADDR']."','".session_id()."',".$vote_id.",".time());
				$this->tables['questions']->update(' quantity=quantity+1 WHERE id='.$vote_id);
				$this->tables['answers']->update(' quantity=quantity+1 WHERE id='.$answer_id);          	
			} else {
				$this->smarty->assign('message', '<div style="color:red">Количество голосований ограничено</div>');
			}
		}

	}

	function getResult() {
		$this->updateData();
		$q = $this->tables['questions']->getItem($this->get('util')->_postVar('vote_question', true, 0) ? $this->get('util')->_postVar('vote_question', true, 0) : "publish='on' AND date_beg<'".date('Y-m-d H:i:s')."' AND date_end>'".date('Y-m-d H:i:s')."'");
		if ($q) {
			$a = $this->tables['answers']->getArraysWhere('question_id='.$q['id']." AND publish='on'");
			$rows = array();
			foreach ($a as $k => $v){
				$a[$k]['percent'] = round($v['quantity'] ? intval($v['quantity'])/intval($q['quantity'])*100 : 0, 2);
				if ($a[$k]['quantity'])
					$rows[] = array(intval(360*$a[$k]['percent']/100), $a[$k]['color']); 
			}
			if ($q['is_dia']) {
				$diagram = new Common\Diagram();
				$diagram->bgcolor = 'EAEAEA';
				if ($diagram->draw($rows))
					$this->smarty->assign('vote_dia', '<img src="'.$diagram->fname.'" width="'.$diagram->width.'" height="'.$diagram->height.'">');
			}
			$this->smarty->assign('a', $a);
			$this->smarty->assign('q', $q);
			return $this->smarty->fetch('service/'.$this->lang.'/vote.result.tpl');
		} else {
			return '';
		}
	}

	function getBody() {
		if (!empty($this->params['action'])) {
			switch ($this->params['action']) {
				case 'form':
					return $this->getForm();
				default:
					return $this->getResult();
			}
		} else {
			return $this->getResult();
		}	
	}

	function getSearchResults($text) {
		return $this->getTableSearchResults($text, 'questions', "publish='on'");
	}
}
