<?php

namespace Fuga\Component\Storage;

class ImageStorageDecorator implements StorageInterface {
	
	private $storageEngine;
	private $options;
	
	public function __construct($storageEngine, $options = array()) {
		$this->storageEngine = $storageEngine;
		$this->setOptions($options);
	}
	
	public function setOptions($options) {
		foreach ($options as $name => $value) {
			$this->options[$name] = $value;
		}
	}
	
	public function hasOption($name) {
		return isset($this->options[$name]);
	}
	
	public function getOption($name) {
		return $this->options[$name];
	}
	
	public function save($filename, $sorcePath) {
		$createdFileName = $this->storageEngine->save($filename, $sorcePath);
		$this->afterSave($createdFileName);
		return $createdFileName;
	}
	
	public function copy($filename, $sorcePath) {
		return $this->storageEngine->copy($filename, $sorcePath);
	}
	
	public function remove($filename) {
		if ($this->hasOption('sizes') && $filename) {
			$pathParts = pathinfo($filename);
			$sizes = explode(',', $this->getOption('sizes'));
			foreach ($sizes as $size) {
				$sizeParams = explode('|', $size);
				if (count($sizeParams) == 2) {
					$this->storageEngine->remove($pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0].'.'.$pathParts['extension']);
				}
			}

		}
		return $this->storageEngine->remove($filename);
	}
	
	public function exists($filename) {
		return $this->storageEngine->exists($filename);
	}
	
	public function realPath($filename){
		return $this->storageEngine->realPath($filename);
	}
	
	public function path($filename){
		return $this->storageEngine->path($filename);
	}
	
	public function size($filename, $precision = 2){
		return $this->storageEngine->size($filename, $precision);
	}
	
	public function additionalFiles($filename, $options = array()) {
		$this->setOptions($options);
		$files = array();
		if ($this->hasOption('sizes') && $filename) {
			$pathParts = pathinfo($filename);
			$sizes = explode(',', $this->getOption('sizes'));
			foreach ($sizes as $sizeData) {
				$sizeParams = explode('|', $sizeData);
				if (count($sizeParams) == 2) {
					$path = $pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0].'.'.$pathParts['extension'];
					$files[] = array(
						'name' => $sizeParams[0], 
						'path' => $path,
						'size' => $this->size($path)
					);
				}
			}
		}	
		return $files;
	}
	
	public function afterSave($filename, $options = array()) {
		$this->setOptions($options);
		if ($imageData = GetImageSize($this->realPath($filename))) {
			$old_img_width = $imageData[0];
			$old_img_height = $imageData[1];
			$resize = false;
			if ($this->hasOption('sizes')) {
				$sizes = explode(',', $this->getOption('sizes'));
				foreach ($sizes as $sizeData) { 	
					$img_width = $imageData[0];
					$img_height = $imageData[1];
					$sizeParams = explode('|', $sizeData);
					if (count($sizeParams) == 2) {
						$asizes2 = explode('x', $sizeParams[1]);
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
						$pathParts = pathinfo($filename);
						if ($resize) {
							ob_start();
							if ($imageData['mime'] == 'image/jpeg') {
								$thumb = imagecreatetruecolor($img_width, $img_height);
								$source = imagecreatefromjpeg($this->realPath($filename));
								imagecopyresampled($thumb, $source, 0, 0, 0, 0, $img_width, $img_height, $old_img_width, $old_img_height);
								imagejpeg($thumb);
							} elseif ($imageData['mime'] == 'image/gif') {
								$thumb = imagecreate($img_width, $img_height);
								$source = imagecreatefromgif($this->realPath($filename));
								$transparent = imagecolortransparent($source);
								imagepalettecopy($thumb, $source);
								imagefill($thumb, 0, 0, $transparent);
								imagecolortransparent($thumb, $transparent);
								imagecopyresized($thumb, $source, 0, 0, 0, 0, $img_width, $img_height, $old_img_width, $old_img_height);	
								imagegif($thumb);
							} elseif ($imageData['mime'] == 'image/png') {
								$thumb = imagecreatetruecolor($img_width, $img_height);
								$source = imagecreatefrompng($this->realPath($filename));
								imagealphablending($thumb, false);
								imagesavealpha($thumb, true);
								$transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
								imagefilledrectangle($thumb, 0, 0, $img_width, $img_height, $transparent);
								imagecolortransparent($thumb, $transparent);
								imagecopyresampled($thumb, $source, 0, 0, 0, 0, $img_width, $img_height, $old_img_width, $old_img_height);
								imagepng($thumb);
							}
							$data = ob_get_clean();
							$fh = fopen($this->realPath($pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0].'.'.$pathParts['extension']), 'w');
							fwrite ($fh, $data);
							fclose ($fh);
							imagedestroy($thumb);
							imagedestroy($source);
						} else {
							$this->copy($pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0].'.'.$pathParts['extension'], $this->realPath($filename));
						}
					}
				}
			}
		}
	}
	
}