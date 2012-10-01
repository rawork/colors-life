<?php
require_once('app/init.php');
$error = "";
$msg = "";
$fileElementName = 'fileToUpload';
$date = new \Datetime();
$upload_ref = $GLOBALS['UPLOAD_REF'].$date->format('/Y/d/m/');
@mkdir($GLOBALS['PRJ_DIR'].$upload_ref, 0755, true);
$upload_path = $GLOBALS['PRJ_DIR'].$upload_ref;
$files_count = isset($_FILES[$fileElementName]["name"]) ? count($_FILES[$fileElementName]["name"]) : 0;
if (!isset($_FILES[$fileElementName]["name"])) {
	echo 'Не выбраны файлы';
	exit;
}
foreach ($_FILES[$fileElementName]["name"] as $i => $file) {	
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
    	$msg = " " . $_FILES[$fileElementName]['name'][$i] . "<br/>";
		$fileref  = $GLOBALS['container']->get('util')->getNextFileName($upload_ref.$_FILES[$fileElementName]['name'][$i]);
		move_uploaded_file($_FILES[$fileElementName]['tmp_name'][$i], $GLOBALS['PRJ_DIR'].$fileref);
		$filepath = $_FILES[$fileElementName]['name'][$i];
		$filesize = @filesize($upload_path.$_FILES[$fileElementName]['name'][$i]);
		$filetype = $_FILES[$fileElementName]['type'][$i];
		$tableName = $GLOBALS['container']->get('util')->_postVar('table_name');
		$entityId = $GLOBALS['container']->get('util')->_postVar('entity_id', true, 0);
		$filewidth = 0;
		$fileheight = 0;
		if ($fileInfo = @GetImageSize($GLOBALS['PRJ_DIR'].$fileref)) {
        	$filewidth = $fileInfo[0];
       		$fileheight = $fileInfo[1];
	    }
		$sql = "INSERT INTO system_files(name,mimetype,file,width,height,filesize,table_name,entity_id,credate) "
			." VALUES('$filepath','$filetype','$fileref',$filewidth,$fileheight,'$filesize','$tableName','$entityId',NOW())";
		$GLOBALS['container']->get('connection')->execQuery('addfile', $sql);
	}
	if ($error) {
		echo $error."<br/>";
	} else {
		echo "Добавлен файл: ".$msg;	
	}
}
