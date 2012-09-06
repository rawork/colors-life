<?php
    
	inc_lib('db/DBField/FieldType.php');
	class templateFieldType extends FieldType {
		public function __construct(&$props, $dbArray = null) {
        	parent::__construct($props, $dbArray);
		}
		
		public function getSQLValue($name = '') {
		global $VERSION_QUANTITY, $PRJ_DIR;
			$name = $name ? $name : $this->getName();
			$ret = CUtils::_postVar($name.'_oldValue');
			$date_stamp = date('Y_m_d_H_i_s');
			$values = '';
			if ($ret && CUtils::_postVar($name.'_delete')) {
				$backup_ret = str_replace('/templates/', '/templates/backup/', $ret);
                @copy($PRJ_DIR.$ret, $PRJ_DIR.$backup_ret.$date_stamp.'.bak');
                @unlink($PRJ_DIR.$ret);
				$values = "'".$this->props['cls']."','".$this->getName()."',".$this->dbId.",NOW(),'".$backup_ret.$date_stamp.'.bak'."'";
                $ret = '';
            } elseif ($ret && CUtils::_postVar($name.'_version', true, 0)) {
				$backup_ret = str_replace('/templates/', '/templates/backup/', $ret);
                @copy($PRJ_DIR.$ret, $PRJ_DIR.$backup_ret.$date_stamp.'.bak');
                @unlink($PRJ_DIR.$ret);
				$values = "'".$this->props['cls']."','".$this->getName()."',".$this->dbId.",NOW(),'".$backup_ret.$date_stamp.'.bak'."'";
				$ver = $GLOBALS['db']->getItem('templates_version', "SELECT * FROM templates_version WHERE id=".CUtils::_postVar($name.'_version', true, 0));
				@copy($PRJ_DIR.$ver['file'], $PRJ_DIR.$ret);
			} elseif ($ret) {
                $f = fopen($PRJ_DIR.$ret.'_new', 'w');
				fwrite($f, $_POST[$name.'_temp']);
				fclose($f);
				if (md5_file($PRJ_DIR.$ret.'_new') != md5_file($PRJ_DIR.$ret)) {
					$backup_ret = str_replace('/templates/', '/templates/backup/', $ret);
	                @copy($PRJ_DIR.$ret, $PRJ_DIR.$backup_ret.$date_stamp.'.bak');
					$values = "'".$this->props['cls']."','".$this->getName()."',".$this->dbId.",NOW(),'".$backup_ret.$date_stamp.'.bak'."'";
					@copy($PRJ_DIR.$ret.'_new', $PRJ_DIR.$ret);
				}
				@unlink($PRJ_DIR.$ret.'_new');
            }
			if (CUtils::_postVar($name.'_cre')) {
                $ret = CUtils::_postVar($name);
				if (trim($ret) != '') {
					$dest = CUtils::getNextFileName('/templates'.(isset($this->props['basepath']) ? $this->props['basepath'] : '').'/'.CUtils::translitStr($ret));
            	    $ret = $dest;
					$f = fopen($PRJ_DIR.$ret, 'w');
					fwrite($f, $_POST[$name."_temp"]);
					fclose($f);
					chmod($PRJ_DIR.$ret, 0666);
				}
            } elseif (is_array($_FILES) && sizeof($_FILES) > 0 && isset($_FILES[$name])
				&& $_FILES[$name]['name'] != '') {
                if ($ret) {
                    $backup_ret = str_replace('/templates/', '/templates/backup/', $ret);
        	        @copy($PRJ_DIR.$ret, $PRJ_DIR.$backup_ret.$date_stamp.'.bak');
		            @unlink($PRJ_DIR.$ret);
					$values = "'".$this->props['cls']."','".$this->getName()."',".$this->dbId.",NOW(),'".$backup_ret.$date_stamp.'.bak'."'";
                }
                $dest = CUtils::getNextFileName('/templates'.(isset($this->props['basepath']) ? $this->props['basepath'] : '').'/'.$_FILES[$name]['name']);
                move_uploaded_file($_FILES[$name]['tmp_name'], $PRJ_DIR.$dest);
				chmod($PRJ_DIR.$dest, 0666);
                $ret = $dest;
            }
			if ($values) {
				$vers = $GLOBALS['db']->getItems('select_version', "SELECT * FROM templates_version WHERE cls='".$this->props['cls']."' AND fld='".$this->getName()."' AND rc=".$this->dbId.' ORDRER BY id');
				if (sizeof($vers) >= __VERSION_QUANTITY)
					$GLOBALS['db']->execQuery('templates_version', 'DELETE FROM templates_version WHERE id='.$vers[0]['id']);
				$db_ret = $GLOBALS['db']->execQuery('add_version', 'INSERT INTO templates_version(cls,fld,rc,credate,file) VALUES('.$values.')');
				//var_dump($db_ret, 'INSERT INTO templates_version(cls,fld,rc,credate,file) VALUES('.$values.')');
				//die();
			}
            return $ret;
        }
		
		public function getStatic() {
        global $PRJ_REF;
			$ret = '';
            if ($this->dbValue) {
				$path = pathinfo($this->dbValue); 
                $ret = $path['basename'].'&nbsp;'.CUtils::getFileSize($this->dbValue);
            }
            return $ret;
        }

        public function getInput($value = '', $name = '') {
		global $PRJ_DIR;
			$ret = '';
			$s = '';
            $value = $value ? $value : $this->dbValue;
            $name = $name ? $name : $this->getName();
			$r_ = rand(0, getrandmax());
	        if ($s = $this->getStatic()) {
				$s = '<span id="'.$name.'_delete">Текущая версия: '.$s.'&nbsp;<input name="'.$name.'_delete" type="checkbox" id="del'.$r_.'"><label for="del'.$r_.'" style="position:relative; top:-2px;">удалить</label>&nbsp;<br></span>'."\n";
				$versions = $GLOBALS['db']->getItems('templates_version', "SELECT * FROM templates_version WHERE cls='".$this->props['cls']."' AND fld='".$name."' AND rc=".$this->dbId);
				if (sizeof($versions)>0) {
					$s .= '<span>Предудущие версии:</span> <select onChange="templateState(this, \''.$name.'\')" id="'.$name.'_version" name="'.$name.'_version"><option value="0">Не выбрано</option>'."\n";
					foreach ($versions as $ver) {
						$s .= '<option value="'.$ver['id'].'">'.$ver['credate'].'</option>'."\n";
					}
					$s .= '</select>&nbsp;<input type="button" style="display: none;" id="'.$name.'_view" onClick="showTemplateVersion(\''.$name.'_version\')" value="Просмотр">'."\n";
				}
            }
			if (empty($value)){
				$s = '<nobr>&nbsp;<input name="'.$name.'_cre" type="checkbox" id="'.$r_.'" onClick="chState(this, \''.$name.'\')"><label for="'.$r_.'" style="position:relative; top:-2px;">Создать</label></nobr><br>
			<div><input type=text id="'.$name.'_create" name="'.$name.'" size="30" style="width:350px;display:none;"><textarea wrap="off" id="'.$name.'_temp" name="'.$name.'_temp" style="width:99%;display:none;" rows="10" cols="40"></textarea></div>';
				$ret = '<input type="hidden" name="'.$name.'_oldValue" value="'.$this->dbValue.'">'.$s.'<div><input id="'.$name.'_load" style="width:99%;display:block" type="file" name="'.$name.'" size="30"></div>';
			} else {
				$ret = '<nobr><input type="hidden" name="'.$name.'_oldValue" value="'.$this->dbValue.'">'.$s.'&nbsp;<br><span id="'.$name.'_load">Новый: <input type="file" name="'.$name.'" size=49 style="width:99%"></nobr></span>'."\n";
				$text = @file_get_contents($PRJ_DIR.$value);	
				$ret .= '<textarea wrap="off" id="'.$name.'_temp" name="'.$name.'_temp" style="width:99%" rows="15" cols="45">'.htmlspecialchars($text).'</textarea>'."\n";
			}
            return $ret;
        }
		
		public function free() {
            @unlink($GLOBALS['PRJ_DIR'].$this->dbValue);
        }
    }

?>