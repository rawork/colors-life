<?php

namespace Fuga\CMSBundle\Model;

use Fuga\Component\Form\Widget\DiagramWidget;

class VoteManager extends ModelManager {
	
	protected $entityTable = 'vote_questions';
	protected $answerTable = 'vote_answers';
	protected $cacheTable = 'vote_cache';
	
	// TODO убрать отсюда
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
			return $this->get('templating')->render('service/vote/form.tpl', compact('vote', 'answers'));
		} else {
			return '';
		}

	}

	function updateData($data = null) {
		$error = null;
		if (!$data) {
			return $error;
		}
		$answerId = isset($data['answer']) ? intval($data['answer']) : 0;
		$voteName = isset($data['vote']) ? intval($data['vote']) : null;
		if ($voteName && $answerId){
			$cache = 0;
			$vote = $this->get('container')->getItem($this->entityTable, 'name="'.$voteName.'"');
			if ($vote['lmt'] == 1) {
				$cache = $this->get('container')->getCount($this->cacheTable, "sessionid='".session_id()."' AND time>".(time()-$vote['step']));
			} elseif ($vote['lmt'] == 2) {
				$cache = $this->get('container')->getCount($this->cacheTable, "(sessionid='".session_id()."' OR ip='".$_SERVER['REMOTE_ADDR']."') AND time>".(time()-$vote['step']));
			}
			if (!$cache) {
				$this->get('container')->addItem(
					$this->cacheTable, 
					'ip,sessionid,question_id,time', 
					"'".$_SERVER['REMOTE_ADDR']."','".session_id()."',".$vote['id'].",".time()
				);
				$this->get('container')->updateItem($this->entityTable, $vote['id'], ' quantity=quantity+1 ');
				$this->get('container')->updateItem($this->answerTable, $answerId, ' quantity=quantity+1 ');
			} else {
				$error = 'Количество голосований ограничено';
			}
		}
		return $error;
	}
	
	// TODO убрать отсюда 
	function getResult($voteName, $formData = null) {
		$error = $this->updateData($formData);
		$vote = $this->get('container')->getItem($this->entityTable, 'name="'.$voteName.'"');
		if ($vote) {
			$answers = $this->get('container')->getItems($this->answerTable, 'question_id='.$vote['id']." AND publish='on'");
			$rows = array();
			foreach ($answers as &$answer){
				$answer['percent'] = round($answer['quantity'] ? intval($answer['quantity'])/intval($vote['quantity'])*100 : 0, 2);
				if ($answer['quantity'])
					$rows[] = array(intval(360*$answer['percent']/100), $answer['color']); 
			}
			$diagram = null;
			if ($vote['is_dia']) {
				$diagram = new DiagramWidget();
				$diagram->bgcolor = 'FFFFFF';
				$diagram->draw($rows);
			}
			return $this->get('templating')->render('service/vote/result.tpl', compact('answers', 'vote', 'error', 'diagram'));
		} else {
			return '';
		}
	}

}
