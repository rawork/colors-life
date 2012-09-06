<?php

    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class alterUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        
        function getText() {
            $this->messageAction($this->t->alter() ? 'Структура таблицы синхронизирована' : 'Ошибка синхронизации структуры таблицы');
        }
    }

?>