<?php
    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class group_updateUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
            $this->messageAction($this->t->group_update() ? 'Обновлено' : 'Ошибка обновления записей');
        }
    }
?>