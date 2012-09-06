<?php

    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class duplicateUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
			set_time_limit(0);
            $this->messageAction($GLOBALS['rtti']->dublicateItem($this->t->getDBTableName(), CUtils::_getVar('id'), CUtils::_getVar('quantity', true, 5)) ? 'Скопировано' : 'Ошибка копирования');
        }
		
    }

?>