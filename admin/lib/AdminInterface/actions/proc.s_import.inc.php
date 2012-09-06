<?php
	inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class s_importUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
			$ret = '<b>Импорт CSV</b><br><table border="0" width="70%">
    <form enctype="multipart/form-data" action="'.$this->fullRef.'&action=import" method="post">
    <tr bgcolor="#fafafa">
      <th align="left" width="20%">CSV-файл <small>(макс '.get_cfg_var('upload_max_filesize').')</small></th>
      <td><input name="csv_file" type="file" style="width:100%"></td></tr>
    <tr><td colspan="2" align="right"><input type="submit" value="ИмпортироватьЭ></td></tr></form></table>';
			return $ret;
        }
    }
?>