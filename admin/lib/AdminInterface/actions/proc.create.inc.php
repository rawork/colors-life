<?php

    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class createUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
            $this->messageAction($this->t->create() ? 'Таблица создана' : 'Ошибка создания таблицы');
        }
    }

?>