<?php

    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class group_deleteUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
            $sQuery = '';
            foreach ($_POST as $sKey => $iEntityId)
                if (stristr($sKey, 'cng'))
                    $sQuery .= ($sQuery ? ',' : '').$iEntityId;
            $sQuery = $sQuery ? 'id IN('.$sQuery.') ' : '';
            $this->messageAction($GLOBALS['rtti']->deleteItem($this->t->getDBTableName(), $sQuery) ? 'Удалено' : 'Ошибка группового удаления');
        }
    }

?>