<?php

    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class dropUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
            $this->messageAction($GLOBALS['rtti']->deleteClass($this->t->getDBTableName()) ? 'Таблица удалена' : 'Ошибка удаления таблицы');
        }
    }

?>