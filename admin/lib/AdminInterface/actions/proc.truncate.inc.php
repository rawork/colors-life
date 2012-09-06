<?php
    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class truncateUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
            $this->messageAction($GLOBALS['rtti']->truncateClass($this->t->getDBTableName()) ? 'Все записи таблицы удалены' : 'Ошибка удаления записей таблицы');
        }
    }
?>