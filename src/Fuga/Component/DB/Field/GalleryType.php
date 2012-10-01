<?php

namespace Fuga\Component\DB\Field;

class GalleryType extends FileType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
		if (isset($this->params['sizes'])) {
			$this->params['sizes'] = 'default:100x50'.(!empty($this->params['sizes']) ? ',' : '').$this->params['sizes'];
		}	
	}

	public function getStatic() {
		global $PRJ_REF;
		$ret = '';
		$photos = $this->get('connection')->getItems("SELECT * FROM system_gallery WHERE tbl='' AND fld='' AND rc=''");
		foreach ($photos as $ph) {
			if ($ph['file']) {
				$path_parts = pathinfo($ph['file']);
				$ret .= ($ret ? '&nbsp;' : '').'<img alt="'.$ph['name'].'" src="'.$PRJ_REF.$path_parts['dirname'].'/default_'.$path_parts['basename'].'">';
			}
		}
		return $ret;
	}

	public function getSQLValue() {
		global $PRJ_DIR;
		$ret = $this->get('util')->_postVar($this->getName().'_oldValue');
		if ($ret && $this->get('util')->_postVar($this->getName().'_delete')) {
			if ($ret != '/img/lib/empty_photo.gif' && $ret != ''){
				@unlink($PRJ_DIR.$ret);
				if (isset($this->params['sizes'])) {
					$path_parts = pathinfo($PRJ_DIR.$ret);
					$asizes = explode(',', $this->params['sizes']);
					foreach ($asizes as $sz) {
						$asz = explode('|', $sz);
						if (sizeof($asz) == 2) {
							@unlink($path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename']);
						}
					}

				}
			}
			$ret = '';
		}
		if (is_array($_FILES) && count($_FILES) && isset($_FILES[$this->getName()]) && $_FILES[$this->getName()]['name'] != '') {
			if ($ret && $ret != '/img/lib/empty_photo.gif') {
				@unlink($PRJ_DIR.$ret);
				if (isset($this->params['sizes'])) {
					$path_parts = pathinfo($PRJ_DIR.$ret);
					$asizes = explode(',', $this->params['sizes']);
					foreach ($asizes as $sz) {
						$asz = explode('|', $sz);
						if (sizeof($asz) == 2) {
							@unlink($path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename']);
						}
					}

				}
			}
			$dest = $this->get('util')->getNextFileName($this->getPath().strtolower($this->get('util')->translitStr($_FILES[$this->getName()]['name'])));
			@move_uploaded_file($_FILES[$this->getName()]['tmp_name'], $PRJ_DIR.$dest);
			$ret = $dest;
			$this->afterUpload($ret);
		}
		return $ret;
	}

	public function afterUpload($fileName) {
		global $PRJ_DIR;
		$fileName = $PRJ_DIR.$fileName;
		$i = @GetImageSize($fileName);
		$old_img_width = $i[0];
		$old_img_height = $i[1];
		$resize = false;
		if (isset($this->params['sizes'])) {
			$asizes = explode(',', $this->params['sizes']);
			foreach ($asizes as $sz) { 	
				$img_width = $i[0];
				$img_height = $i[1];
				$asz = explode('|', $sz);
				if (sizeof($asz) == 2) {
					$asizes2 = explode('x', $asz[1]);
					$max_width = $asizes2[0];
					$max_height = $asizes2[1];
					if ($max_width) {
						if ($img_width > $max_width) {
							$img_height = intval(($max_width / $img_width) * $img_height);
							$img_width = $max_width;
							$resize = true;
						}
					}
					if ($max_height) {
						if ($img_height > $max_height) {
							$img_width = intval(($max_height / $img_height) * $img_width);
							$img_height = $max_height;
							$resize = true;
						}
					}
					$path_parts = pathinfo($fileName);
					if ($resize) {
						if ($i['mime'] == 'image/jpeg') {
							$thumb = imagecreatetruecolor($img_width, $img_height);
							$source = imagecreatefromjpeg($fileName);
							imagecopyresampled($thumb, $source, 0, 0, 0, 0, $img_width, $img_height, $old_img_width, $old_img_height);
							imagejpeg($thumb, $path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename']);
						} elseif ($i['mime'] == 'image/gif') {
							$thumb = imagecreate($img_width, $img_height);
							$source = imagecreatefromgif($fileName);
							imagecopyresized($thumb, $source, 0, 0, 0, 0, $img_width, $img_height, $old_img_width, $old_img_height);	
							imagegif($thumb, $path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename']);
						} elseif ($i['mime'] == 'image/png') {
							$thumb = imagecreatetruecolor($img_width, $img_height);
							$source = imagecreatefrompng($fileName);
							imagecopyresampled($thumb, $source, 0, 0, 0, 0, $img_width, $img_height, $old_img_width, $old_img_height);
							imagepng($thumb, $path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename']);
						}
						imagedestroy($thumb);
						imagedestroy($source);
					} else {
						@copy($fileName, $path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename']);
					}
				}
			}
		}
	}

	public function getInput($value = '', $name = '') {
	global $PRJ_REF;	
		return $this->getStatic().'<input class="butt" type="button" value="Изменить" onClick="open_window(\''.$PRJ_REF.'/admin/wnd_photo.php?table='.$this->params['l_table'].'&field='.$this->params['l_field'].'&id='.$this->dbId.'\')">';
	}

}
