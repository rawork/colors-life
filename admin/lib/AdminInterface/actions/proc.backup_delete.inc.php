<?php
	inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class backup_deleteUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
        	$file = CUtils::_getVar('file');
        	@unlink($GLOBALS['PRJ_DIR'].$file);
			header('location: '.$this->fullRef.'&action=s_backup');
        }
    }
?>