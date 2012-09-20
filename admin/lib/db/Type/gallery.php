<?php

    inc_lib('db/Type/file.php');
    class galleryFieldType extends fileFieldType {
		public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
			if (isset($this->props['sizes'])) {
				$this->props['sizes'] = 'default:100x50'.(!empty($this->props['sizes']) ? ',' : '').$this->props['sizes'];
			}	
        }
        
		public function getStatic() {
        global $db, $PRJ_REF;
			$ret = '';
			$photos = $db->getItems("SELECT * FROM system_gallery WHERE tbl='' AND fld='' AND rc=''");
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
            $ret = CUtils::_postVar($this->getName().'_oldValue');
            if ($ret && CUtils::_postVar($this->getName().'_delete')) {
                if ($ret != '/img/lib/empty_photo.gif' && $ret != ''){
                	@unlink($PRJ_DIR.$ret);
					if (isset($this->props['sizes'])) {
						$path_parts = pathinfo($PRJ_DIR.$ret);
						$asizes = explode(',', $this->props['sizes']);
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
            if (is_array($_FILES) && sizeof($_FILES) > 0 && isset($_FILES[$this->getName()]) && $_FILES[$this->getName()]['name'] != '') {
                if ($ret && $ret != '/img/lib/empty_photo.gif') {
               		@unlink($PRJ_DIR.$ret);
					if (isset($this->props['sizes'])) {
						$path_parts = pathinfo($PRJ_DIR.$ret);
						$asizes = explode(',', $this->props['sizes']);
						foreach ($asizes as $sz) {
							$asz = explode('|', $sz);
							if (sizeof($asz) == 2) {
								@unlink($path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename']);
							}
						}
						
					}
                }
                $dest = CUtils::getNextFileName('/upload/'.strtolower(CUtils::translitStr($_FILES[$this->getName()]['name'])));
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
			if (isset($this->props['sizes'])) {
				$asizes = explode(',', $this->props['sizes']);
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
            return $this->getStatic().'<input class="butt" type="button" value="Изменить" onClick="open_window(\''.$PRJ_REF.'/admin/wnd_photo.php?table='.$this->props['l_table'].'&field='.$this->props['l_field'].'&id='.$this->dbId.'\')">';
        }
		
    }

?>