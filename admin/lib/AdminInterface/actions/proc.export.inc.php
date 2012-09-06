<?php
	inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class exportUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
            $ret = $this->uai->unit->exportCSV();
			$ret = '<textarea width="90%" cols="50" rows="10" name="data" id="data">'.addslashes($ret).'</textarea>';
			return $ret;
        }
    }
?>