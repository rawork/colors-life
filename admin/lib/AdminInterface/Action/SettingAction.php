<?php

namespace AdminInterface\Action;

	class SettingAction extends Action {
        function __construct(&$adminController) {
            parent::__construct($adminController);
        }
        function getText() {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$state = false;
				$params = $this->get('connection')->getItems('get_settings', "SELECT * FROM config_settings WHERE komponent='".$this->uai->module->name."'");
				foreach ($params as $param) {
					if ($this->get('util')->_postVar('param_'.$param['name']) && $value = $this->validParam($this->get('util')->_postVar('param_'.$param['name']), $param)) {
						$this->get('connection')->execQuery('set_settings', "UPDATE config_settings SET value='".$value."' WHERE name='".$param['name']."' AND komponent='".$param['komponent']."'");
						$state = true;
					} elseif ($param['type'] == 'bol') {
						$this->get('connection')->execQuery('set_settings', "UPDATE config_settings SET value='0' WHERE name='".$param['name']."' AND komponent='".$param['komponent']."'");
						$state = true;
					} 
				}
				$this->uai->messageAction($state ? 'Настройки изменены' : 'Ошибки при изменении', $this->uai->getBaseTableRef().'/setting');
			}
			
			$ret = '';
			$ret .= '<form enctype="multipart/form-data" method="post" name="frmInsert" id="frmInsert" action="'.$this->fullRef.'/setting">';
			$ret .= $this->getTableHeader();
			$ret .= '<tr>';
			$ret .= '<th nowrap><b>Настройки компонента</b></th>';
			$ret .= '<th><b>&nbsp;</b></th></tr>';
			$params = $this->get('connection')->getItems('get_settings', "SELECT * FROM config_settings WHERE komponent='".$this->uai->module->name."'");

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
    }
?>