<?php
	inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class s_exportUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
            $ret = '<b>Экспорт CSV</b><br><table border="0" width="70%">
    <form enctype="multipart/form-data" action="'.$this->fullRef.'&action=export" method="post">
    <tr bgcolor="#fafafa"><td align="right"><input type="submit" value="Экспорт -&gt;"></td></tr>
    </form></table>';
			return $ret;
        }
    }
?>