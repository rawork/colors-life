<?php

namespace Fuga\Component\DB\Field;
    
class FileType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	protected function afterUpload($fileName) {}
	
	protected function getPath() {
		global $PRJ_DIR, $UPLOAD_REF;
		$date = new \Datetime();
		$path = $UPLOAD_REF.$date->format('/Y/m/d/');
		@mkdir($PRJ_DIR.$path, 0755, true);
		return $path;
	}

	public function getSQLValue($inputName = '') {
		$inputName = $inputName ? $inputName : $this->getName();
		$fileName = $_POST[$inputName.'_oldValue'];
		if ($this->get('util')->_postVar($inputName.'_delete')) {
			$this->get('filestorage')->remove($fileName);
			$fileName = '';
		}
		if (!empty($_FILES[$inputName]) && !empty($_FILES[$inputName]['name'])) {
			$this->get('filestorage')->remove($fileName);
			$fileName = $this->get('filestorage')->save($_FILES[$inputName]['name'], $_FILES[$inputName]['tmp_name']);
		}
		return $fileName;
	}

	public function getStatic() {
	global $PRJ_REF;
		$ret = '';
		if ($this->dbValue)
			$ret = '<a href="'.$PRJ_REF.$this->dbValue.'">'.$this->dbValue.'</a>&nbsp;('.$this->get('filestorage')->size($this->dbValue).')';
		return $ret;
	}

	public function getInput($value = '', $name = '') {
		$name = $name ? $name : $this->getName();
		if ($s = $this->getStatic()) {
			$r = rand(0, getrandmax());
			$s = $s.'&nbsp;<label for="'.$r.'"><input name="'.$name.'_delete" type="checkbox" id="'.$r.'"> удалить</label>';
		}
		return '<input type="hidden" name="'.$name.'_oldValue" value="'.$this->dbValue.'">'.$s.'<input type="file" name="'.$name.'">';
	}

	public function free() {
		$this->get('filestorage')->remove($this->dbValue);
	}
}
