<?php

    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class deleteUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
			$q = 'id='.CUtils::_getVar('id', true, 0);
            $this->messageAction($GLOBALS['rtti']->deleteItem($this->t->getDBTableName(), $q) ? 'Удалено' : 'Ошибка удаления');
        }
    }

?>