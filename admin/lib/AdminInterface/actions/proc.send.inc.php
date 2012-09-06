<?php
	inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class sendUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
			$this->messageAction($this->uai->unit->everyMin() ? 'Ошибки при рассылке' : 'Рассылка сделана');
        }
    }
?>