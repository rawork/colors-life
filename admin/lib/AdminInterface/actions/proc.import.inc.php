<?php
	inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class importUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
			$this->uai->messageAction($this->uai->unit->importCSV() ? '������ ��������' : '������ ��� �������', $this->uai->getBaseRef().'&action=s_import');
        }
    }
?>