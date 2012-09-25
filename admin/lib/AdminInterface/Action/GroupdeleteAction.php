<?php

namespace AdminInterface\Action;    

class GroupdeleteAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	function getText() {
		$query = '';
		foreach ($_POST as $key => $entityId)
			if (stristr($key, 'cng'))
				$query .= ($query ? ',' : '').$entityId;
		$query = $query ? 'id IN('.$query.') ' : '';
		$this->messageAction($this->get('container')->deleteItem($this->dataTable->getDBTableName(), $query) ? 'Удалено' : 'Ошибка группового удаления');
	}
}
