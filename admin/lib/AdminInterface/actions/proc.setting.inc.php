<?php
	inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class settingUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
		
		function validParam($value, $param = array()) {
			$ret = null;
			if ($param['type'] == 'bool') {
				if (intval($value) >= intval($param['minvalue']) && intval($value) <= intval($param['maxvalue'])) {
					$ret = intval($value);
				} else {
					$ret = intval($param['defaultvalue']);
				}
			} elseif ($param['type'] == 'int') {
				if (intval($value) >= intval($param['minvalue']) && intval($value) <= intval($param['maxvalue'])) {
					$ret = intval($value);
				} else {
					$ret = intval($param['defaultvalue']);
				}
			} else {
				$ret = $value;
			}
			return $ret;
		}
		
        function getText() {
		global $db;
			$state = false;
			$params = $db->getItems('get_settings', "SELECT * FROM config_settings WHERE komponent='".$this->uai->unit->ocomponent['name']."'");
			foreach ($params as $param) {
				if (CUtils::_postVar('param_'.$param['name']) && $value = $this->validParam(CUtils::_postVar('param_'.$param['name']), $param)) {
					$db->execQuery('set_settings', "UPDATE config_settings SET value='".$value."' WHERE name='".$param['name']."' AND komponent='".$param['komponent']."'");
					$state = true;
				} elseif ($param['type'] == 'bol') {
					$db->execQuery('set_settings', "UPDATE config_settings SET value='0' WHERE name='".$param['name']."' AND komponent='".$param['komponent']."'");
					$state = true;
				} 
			}
			$this->uai->messageAction($state ? 'Настройки изменены' : 'Ошибки при изменении', $this->uai->getBaseRef().'&action=s_setting');
        }
    }
?>