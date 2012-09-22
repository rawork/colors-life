<?php
require_once('../loader.php');
$error = "";
$msg = "";
$fileElementName = 'fileToUpload';
$upload_ref  = '/upload/';
$upload_path = $GLOBALS['PRJ_DIR'].$upload_ref;
$i = 0;
$files_count = isset($_FILES[$fileElementName]["name"]) ? count($_FILES[$fileElementName]["name"]) : 0;

for ($i = 0; $i < $files_count-1; $i++) {	
	if(!empty($_FILES[$fileElementName]['error'][$i])) {
		switch($_FILES[$fileElementName]['error'][$i]) {
			case '1':
				$error = 'Размер загруженного файла превышает размер установленный параметром upload_max_filesize  в php.ini ';
				break;
			case '2':
				$error = 'Размер загруженного файла превышает размер установленный параметром MAX_FILE_SIZE в HTML форме. ';
				break;
			case '3':
				$error = 'Загружена только часть файла ';
				break;
			case '4':
				$error = 'Файл не был загружен (Пользователь в форме указал неверный путь к файлу). ';
				break;
			case '6':
				$error = 'Неверная временная дирректория';
				break;
			case '7':
				$error = 'Ошибка записи файла на диск';
				break;
			case '8':
				$error = 'Загрузка файла прервана';
				break;
			case '999':
			default:
				$error = 'Не известный код ошибки';
		}
	} elseif(empty($_FILES[$fileElementName]['tmp_name'][$i]) || $_FILES[$fileElementName]['tmp_name'][$i] == 'none') {
		$error = 'Файлы не загружены..';
	} else {
		 /*if (file_exists($upload_path . $_FILES[$fileElementName]['name'][$i])){
  			$error = $_FILES[$fileElementName]['name'][$i] . " уже существует. ";
  		} else {*/
    	$msg = " " . $_FILES[$fileElementName]['name'][$i] . "<br/>";
		$fileref  = CUtils::getNextFileName($upload_ref.$_FILES[$fileElementName]['name'][$i]);
		move_uploaded_file($_FILES[$fileElementName]['tmp_name'][$i], $GLOBALS['PRJ_DIR'].$fileref);
		$filename = $_FILES[$fileElementName]['name'][$i];
		$filesize = @filesize($upload_path.$_FILES[$fileElementName]['name'][$i]);
		$filetype = $_FILES[$fileElementName]['type'][$i];
		$table_name = CUtils::_postVar('table_name');
		$record_id = CUtils::_postVar('record_id', true, 0);
		$filewidth = 0;
		$fileheight = 0;
		if (is_array($file_info = @GetImageSize($GLOBALS['PRJ_DIR'].$fileref))) {
        	$filewidth = $file_info[0];
       		$fileheight = $file_info[1];
	    }
			$sql = "INSERT INTO system_files(name,mimetype,file,width,height,filesize,table_name,record_id,credate) "
				." VALUES('$filename','$filetype','$fileref',$filewidth,$fileheight,'$filesize','$table_name','$record_id',NOW())";
			$GLOBALS['db']->execQuery('addfile', $sql);
			//$msg .= $sql;
		/*}*/
	//for security reason, we force to remove all uploaded file
	//@unlink($_FILES[$fileElementName][$i]);		
	}
	if ($error) {
		echo $error."<br/>"."\n";
	} else {
		echo "Добавлен файл: ".$msg."\n";	
	}
}
?>