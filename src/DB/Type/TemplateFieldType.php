<?php
    
namespace DB\Type;

class TemplateFieldType extends FieldType {
	
	private $basepath;
	
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
		$this->basepath = isset($this->params['basepath']) ? $this->params['basepath'] : '';
	}
	
	public function getPath() {
		return '/app/Resources/views/'.$this->basepath;
	}
	
	public function getBackupPath() {
		return '/app/Resources/views/backup/';
	}

	public function getSQLValue($name = '') {
	global $VERSION_QUANTITY, $PRJ_DIR;
		$name = $name ? $name : $this->getName();
		$ret = $this->get('util')->_postVar($name.'_oldValue');
		$date_stamp = date('Y_m_d_H_i_s');
		$values = '';
		if ($ret && $this->get('util')->_postVar($name.'_delete')) {
			$backup_ret = str_replace($this->getPath(), $this->getBackupPath(), $ret);
			@copy($PRJ_DIR.$ret, $PRJ_DIR.$backup_ret.$date_stamp.'.bak');
			@unlink($PRJ_DIR.$ret);
			$values = "'".$this->params['cls']."','".$this->getName()."',".$this->dbId.",NOW(),'".$backup_ret.$date_stamp.'.bak'."'";
			$ret = '';
		} elseif ($ret && $this->get('util')->_postVar($name.'_version', true, 0)) {
			$backup_ret = str_replace($this->getPath(), $this->getBackupPath(), $ret);
			@copy($PRJ_DIR.$ret, $PRJ_DIR.$backup_ret.$date_stamp.'.bak');
			@unlink($PRJ_DIR.$ret);
			$values = "'".$this->params['cls']."','".$this->getName()."',".$this->dbId.",NOW(),'".$backup_ret.$date_stamp.'.bak'."'";
			$ver = $this->get('connection')->getItem('templates_version', "SELECT * FROM templates_version WHERE id=".$this->get('util')->_postVar($name.'_version', true, 0));
			@copy($PRJ_DIR.$ver['file'], $PRJ_DIR.$ret);
		} elseif ($ret) {
			$f = fopen($PRJ_DIR.$ret.'_new', 'w');
			fwrite($f, $_POST[$name.'_temp']);
			fclose($f);
			if (md5_file($PRJ_DIR.$ret.'_new') != md5_file($PRJ_DIR.$ret)) {
				$backup_ret = str_replace($this->getPath(), $this->getBackupPath(), $ret);
				@copy($PRJ_DIR.$ret, $PRJ_DIR.$backup_ret.$date_stamp.'.bak');
				$values = "'".$this->params['cls']."','".$this->getName()."',".$this->dbId.",NOW(),'".$backup_ret.$date_stamp.'.bak'."'";
				@copy($PRJ_DIR.$ret.'_new', $PRJ_DIR.$ret);
			}
			@unlink($PRJ_DIR.$ret.'_new');
		}
		if ($this->get('util')->_postVar($name.'_cre')) {
			$ret = $this->get('util')->_postVar($name);
			if (trim($ret) != '') {
				$dest = $this->get('util')->getNextFileName($this->getPath().$this->get('util')->translitStr($ret));
				$ret = $dest;
				$f = fopen($PRJ_DIR.$ret, 'w');
				fwrite($f, $_POST[$name."_temp"]);
				fclose($f);
				chmod($PRJ_DIR.$ret, 0666);
			}
		} elseif (is_array($_FILES) && sizeof($_FILES) > 0 && isset($_FILES[$name])
			&& $_FILES[$name]['name'] != '') {
			if ($ret) {
				$backup_ret = str_replace($this->getPath(), $this->getBackupPath(), $ret);
				@copy($PRJ_DIR.$ret, $PRJ_DIR.$backup_ret.$date_stamp.'.bak');
				@unlink($PRJ_DIR.$ret);
				$values = "'".$this->params['cls']."','".$this->getName()."',".$this->dbId.",NOW(),'".$backup_ret.$date_stamp.'.bak'."'";
			}
			$dest = $this->get('util')->getNextFileName($this->getPath().$_FILES[$name]['name']);
			move_uploaded_file($_FILES[$name]['tmp_name'], $PRJ_DIR.$dest);
			chmod($PRJ_DIR.$dest, 0666);
			$ret = $dest;
		}
		if ($values) {
			$vers = $this->get('connection')->getItems('select_version', "SELECT * FROM templates_version WHERE cls='".$this->params['cls']."' AND fld='".$this->getName()."' AND rc=".$this->dbId.' ORDRER BY id');
			if (sizeof($vers) >= __VERSION_QUANTITY)
				$this->get('connection')->execQuery('templates_version', 'DELETE FROM templates_version WHERE id='.$vers[0]['id']);
			$db_ret = $this->get('connection')->execQuery('add_version', 'INSERT INTO templates_version(cls,fld,rc,credate,file) VALUES('.$values.')');
		}
		return $ret;
	}

	public function getStatic() {
		$ret = '';
		if ($this->dbValue) {
			$path = pathinfo($this->dbValue); 
			$ret = $path['basename'].'&nbsp;'.$this->get('filestorage')->size($this->dbValue);
		}
		return $ret;
	}

	public function getInput($value = '', $name = '') {
	global $PRJ_DIR;
		$text = '';
		$content = '';
		$value = $value ? $value : $this->dbValue;
		$name = $name ? $name : $this->getName();
		$randomId = rand(0, getrandmax());
		if ($content = $this->getStatic()) {
			$content = '<span id="'.$name.'_delete">Текущая версия: '.$content.'<label for="del'.$randomId.'"><input name="'.$name.'_delete" type="checkbox" id="del'.$randomId.'"> удалить</label></span>';
			$versions = $this->get('connection')->getItems('templates_version', "SELECT * FROM templates_version WHERE cls='".$this->params['cls']."' AND fld='".$name."' AND rc=".$this->dbId);
			if (count($versions)) {
				$content .= '<span>Предудущие версии:</span> <select onChange="templateState(this, \''.$name.'\')" id="'.$name.'_version" name="'.$name.'_version"><option value="0">Не выбрано</option>'."\n";
				foreach ($versions as $version) {
					$content .= '<option value="'.$version['id'].'">'.$version['credate'].'</option>';
				}
				$content .= '</select> <input type="button" class="btn btn-success btn-large closed" id="'.$name.'_view" onClick="showTemplateVersion(\''.$name.'_version\')" value="Просмотр">';
			}
		}
		if (empty($value)){
			$content = '<label for="'.$randomId.'"><input name="'.$name.'_cre" type="checkbox" id="'.$randomId.'" onClick="chState(this, \''.$name.'\')"> Создать</label><br>
<div>
<input type="text" id="'.$name.'_create" name="'.$name.'" class="closed">
<textarea id="'.$name.'_temp" name="'.$name.'_temp" class="closed" style="width:95%" rows="10" cols="40"></textarea>
</div>';
			$text = '<input type="hidden" name="'.$name.'_oldValue" value="'.$this->dbValue.'">'.$content.'<div><input id="'.$name.'_load" type="file" name="'.$name.'"></div>';
		} else {
			$text = '<input type="hidden" name="'.$name.'_oldValue" value="'.$this->dbValue.'">'.$content.'&nbsp;<br><span id="'.$name.'_load">Новый: <input type="file" name="'.$name.'"></span>';
			$text = @file_get_contents($PRJ_DIR.$value);	
			$text .= '<textarea wrap="off" id="'.$name.'_temp" name="'.$name.'_temp" style="width:95%" rows="10" cols="40">'.htmlspecialchars($text).'</textarea>';
		}
		return $text;
	}

}
