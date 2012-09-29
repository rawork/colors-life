<?php

namespace DB\Type;

class ImageFieldType extends FileFieldType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
		if (!empty($this->params['sizes'])) {
			$this->params['sizes'] = 'default|50x50,'.$this->params['sizes'];
		} else {
			$this->params['sizes'] = '';
		}	
	}

	public function getStatic() {
		$fileName = $this->dbValue;
		$additionalFiles = $width = '';
		$this->get('imagestorage')->setOptions(array('sizes' => $this->params['sizes']));
		$files = $this->get('imagestorage')->additionalFiles($fileName);
		foreach ($files as $file) {
			if ($file['name'] == 'default') {
				$fileName = $file['path'];
			} else {
				$additionalFiles .= '<div><a target="_blank" href="'.$file['path'].'">'.ucfirst($file['name']).' image</a> ('.$file['size'].')</div>';
			}
		}
		if (!count($files)) {
			$width = 'width="50"';
		}
		return $fileName ? '<a target="_blank" href="'.$this->dbValue.'"><img '.$width.' border="0" src="'.$fileName.'"></a><div>('.$this->get('imagestorage')->size($this->dbValue).')</div>'.$additionalFiles : '';
	}

	public function getSQLValue($inputName = '') {
		$this->get('imagestorage')->setOptions(array('sizes' => $this->params['sizes']));
		$inputName = $inputName ? $inputName : $this->getName();
		$fileName = $this->get('util')->_postVar($inputName.'_oldValue');
		if ($fileName && $this->get('util')->_postVar($inputName.'_delete')) {
			$this->get('imagestorage')->remove($fileName);
			$fileName = '';
		}
		if (!empty($_FILES[$inputName]) && !empty($_FILES[$inputName]['name'])) {
			$this->get('imagestorage')->remove($fileName);
			$fileName = $this->get('imagestorage')->save($_FILES[$inputName]['name'], $_FILES[$inputName]['tmp_name']);
		}
		return $fileName;
	}

}
