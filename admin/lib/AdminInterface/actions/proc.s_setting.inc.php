<?php
	inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class s_settingUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
		global $db;
			$ret = '';
			$ret .= '<form enctype="multipart/form-data" method="post" name="frmInsert" id="frmInsert" action="'.$this->fullRef.'&action=setting">';
			$ret .= $this->getTableHeader();
			$ret .= '<tr>';
			$ret .= '<th nowrap><b>Настройки компонента</b></th>';
			$ret .= '<th><b>&nbsp;</b></th></tr>';
			$params = $db->getItems('get_settings', "SELECT * FROM config_settings WHERE komponent='".$this->uai->unit->ocomponent['name']."'");

			foreach ($params as $param) {
				$ret .= '<tr><td align=left width="250"><strong>'.$param["title"].'</strong><br>{'.$param["name"].'}</td><td>';
				if ($param['type'] == 'bol') {
					if (intval($param['value']) > 0) {
						$text = 'checked';
					} else {
						$text = '';
					}
					$ret .= '<input type="checkbox" name="param_'.$param["name"].'" value="1" '.$text.'>';
				} elseif ($param['type'] == 'txt') {
					$ret .= '<textarea rows="5" style="width:50%;" name="param_'.$param["name"].'">'.$param["value"].'</textarea>';
				} else {
					$ret .= '<input style="width:50%;" type="text" name="param_'.$param["name"].'" value="'.$param["value"].'">';
				}
				$ret .= '</td></tr>';
			} 
            $ret .= '</table><input type="submit" value="Изменить"></form>';
			return $ret;
        }
    }
?>