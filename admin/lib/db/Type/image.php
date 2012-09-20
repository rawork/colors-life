<?php

    inc_lib('db/Type/file.php');
    class imageFieldType extends fileFieldType {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
			if (!empty($this->props['sizes'])) {
				$this->props['sizes'] = 'default|100x50,'.$this->props['sizes'];
			}	
        }
        
		public function getStatic() {
        global $PRJ_REF;
			$photo = $this->dbValue;
			$width = '';
			$extra_text = '';
			if (isset($this->props['sizes'])) {
				if ($this->dbValue) {
					$path_parts = pathinfo($this->dbValue);
					$photo = $path_parts['dirname'].'/default_'.$path_parts['basename'];
					$asizes = explode(',', $this->props['sizes']);
					foreach ($asizes as $k => $sz) {
						$asz = explode('|', $sz);
						if ($k && sizeof($asz) == 2) {
							$extra_text .= ($extra_text ? ' | ' : '').'<a target="_blank" href="'.$path_parts['dirname'].'/'.$asz[0].'_'.$path_parts['basename'].'">'.$asz[1].'</a>';
						}
					}
					$extra_text = $extra_text ? '<span class="imageinfo">'.$extra_text.'</span>' : '';
				}
			} else {
				$width = ' width="50"';
			}
			return $photo ? '<a target="_blank" href="'.$this->dbValue.'"><img '.$width.' border="0" src="'.$PRJ_REF.$photo.'"></a><span class="imageinfo">('.CUtils::getFileSize($this->dbValue).')</span>'.$extra_text : '';
        }
        
        public function getSQLValue($name = '') {
            global $PRJ_DIR;
			$name = $name ? $name : $this->getName();
            $ret = CUtils::_postVar($name.'_oldValue');
            if ($ret && CUtils::_postVar($name.'_delete')) {
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
            if (is_array($_FILES) && sizeof($_FILES) > 0 && isset($_FILES[$name]) && $_FILES[$name]['name'] != '') {
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
                $dest = CUtils::getNextFileName('/upload/'.strtolower(CUtils::translitStr($_FILES[$name]['name'])));
                @move_uploaded_file($_FILES[$name]['tmp_name'], $PRJ_DIR.$dest);
                $ret = $dest;
                $this->afterUpload($ret);
            }
            return $ret;
        }
        
        protected function afterUpload($fileName) {
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
		
    }

?>