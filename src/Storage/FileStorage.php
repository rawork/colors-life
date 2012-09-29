<?php

namespace Storage;

class FileStorage implements StorageInterface {
	
	private $realpath;
	private $path;
	
	public function __construct() {
		global $PRJ_DIR, $PRJ_REF, $UPLOAD_REF;
		$this->uploadpath = $UPLOAD_REF;
		$this->realpath = $PRJ_DIR;
		$this->path = $PRJ_REF;
	}
	
	public function save($filename, $sourcePath) {
		$filename = $this->unique($filename);
		$createPath = $this->createPath();
		move_uploaded_file($sourcePath, $this->realPath($createPath.$filename));
		chmod($this->realPath($createPath.$filename), 0666);
		return $this->path($createPath.$filename);
	}
	
	public function copy($filename, $sourcePath) {
		@copy($sourcePath, $this->realPath($filename));
		return $this->path($filename);
	}
	
	public function remove($filename) {
		if ($filename) {
			@unlink($this->realPath($filename));
		}
		return true;
	}
	
	public function exists($filename) {
		return file_exists($this->realPath($filename));
	}
	
	private function createPath() {
		global $PRJ_DIR, $UPLOAD_REF;
		$date = new \Datetime();
		$path = $this->uploadpath.$date->format('/Y/m/d/');
		@mkdir($this->realpath.$path, 0777, true);
		return $path;
	}
	
	public function realPath($filename){
		return $this->realpath.$filename;
	}
	
	public function path($filename){
		return $this->path.$filename;
	}
	
	public function size($filename, $precision = 2) {
		$bytes = '';
		$units = array('б', 'Кб', 'Мб', 'Гб', 'Тб');
		if ($this->exists($filename)) {
			$bytes = filesize($this->realPath($filename));
			$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
			$pow = min($pow, count($units) - 1);
			$bytes /= pow(1024, $pow);
			$bytes = round($bytes, $precision) . '&nbsp;' . $units[$pow];
		}
		return $bytes;
	}

	private function unique($filename, $counter = 0) {
		if (!$counter) {
			$filename = strtolower($this->translit($filename));
			if (!$filename) {
				throw new Exception('Пустое имя сохраняемого файла');
			}
		}	
		$nameParts = explode('.', $filename);
		$nameParts[0] .= ($counter ? '_'.$counter : '');
		$filename = implode('.', $nameParts);

		return $this->exists($filename) ? $this->unique($filename, ++$counter) : $filename;
	}
	
	private function translit($s) {
		// Сначала заменяем "односимвольные" фонемы.
		$s=strtr($s,"абвгдеёзийклмнопрстуфхъыэ_ ", "abvgdeeziyklmnoprstufh_iei_");
		$s=strtr($s,"АБВГДЕЁЗИЙКЛМНОПРСТУФХЪЫЭ_ ", "ABVGDEEZIYKLMNOPRSTUFH_IEI_");
		// Затем - "многосимвольные".
		$s=strtr($s, 
			array(
				"ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh", 
				"щ"=>"shch","ь"=>"", "ю"=>"yu", "я"=>"ya",
				"Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH", 
				"Щ"=>"SHCH","Ь"=>"", "Ю"=>"YU", "Я"=>"YA",
				"ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye"
			)
		);
		return $s;
	}
}

