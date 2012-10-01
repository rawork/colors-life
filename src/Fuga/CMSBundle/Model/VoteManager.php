<?php

namespace Fuga\CMSBundle\Model;

use Fuga\Component\Form\Widget\DiagramWidget;

class VoteManager extends ModelManager {
	
	protected $entityTable = 'vote_questions';
	protected $answerTable = 'vote_answers';
	protected $cacheTable = 'vote_cache';
	

	function getForm($voteName) {
		$vote = $this->get('container')->getItem(
				$this->entityTable, 
				"name='".$voteName."' AND date_beg<'".date('Y-m-d H:i:s')."' AND date_end>'".date('Y-m-d H:i:s')."'"
		);
		if ($vote) {
			$answers = $this->get('container')->getItems(
				$this->answerTable,
				'question_id='.$vote['id']." AND publish='on'"
			);		
			$this->get('smarty')->assign('a', $answers);
			$this->get('smarty')->assign('q', $vote);
			return $this->get('smarty')->fetch('service/'.$this->get('router')->getParam('lang').'/vote.form.tpl');
		} else {
			return '';
		}

	}

	function updateData() {
		$answerId = $this->get('util')->_postVar('vote', true, 0);
		$voteId = $this->get('util')->_postVar('vote_question', true, 0);
		if ($voteId && $answerId){
			$cache = 0;
			$q = $this->get('container')->getItem($this->entityTable, $voteId);
			if ($q['lmt'] == 1) {
				$cache = $this->get('container')->getCount($this->cacheTable, "sessionid='".session_id()."' AND time>".(time()-$q['step']));
			} elseif ($q['lmt'] == 2) {
				$cache = $this->get('container')->getCount($this->cacheTable, "(sessionid='".session_id()."' OR ip='".$_SERVER['REMOTE_ADDR']."') AND time>".(time()-$q['step']));
			}
			if (!$cache) {
				$this->get('container')->addItem(
					$this->cacheTable, 
					'ip,sessionid,question_id,time', 
					"'".$_SERVER['REMOTE_ADDR']."','".session_id()."',".$voteId.",".time()
				);
				$this->get('container')->updateItem($this->entityTable, $voteId, ' quantity=quantity+1 ');
				$this->get('container')->updateItem($this->answerTable, $answerId, ' quantity=quantity+1 ');
			} else {
				$this->get('smarty')->assign('message', '<div style="color:red">Количество голосований ограничено</div>');
			}
		}

	}

	function getResult() {
		$this->updateData();
		$voteId = $this->get('util')->_postVar('vote_question', true, 0);
		$q = $this->get('container')->getItem($this->entityTable, $voteId);
		if ($q) {
			$a = $this->get('container')->getItems($this->answerTable, 'question_id='.$q['id']." AND publish='on'");
			$rows = array();
			foreach ($a as $k => $v){
				$a[$k]['percent'] = round($v['quantity'] ? intval($v['quantity'])/intval($q['quantity'])*100 : 0, 2);
				if ($a[$k]['quantity'])
					$rows[] = array(intval(360*$a[$k]['percent']/100), $a[$k]['color']); 
			}
			if ($q['is_dia']) {
				$diagram = new DiagramWidget();
				$diagram->bgcolor = 'EAEAEA';
				if ($diagram->draw($rows))
					$this->get('smarty')->assign('vote_diagram', '<img src="'.$diagram->fname.'" width="'.$diagram->width.'" height="'.$diagram->height.'">');
			}
			$this->get('smarty')->assign('a', $a);
			$this->get('smarty')->assign('q', $q);
			return $this->get('smarty')->fetch('service/'.$this->get('router')->getParam('lang').'/vote.result.tpl');
		} else {
			return '';
		}
	}

}
