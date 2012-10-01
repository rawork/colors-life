<?php
	
$filepath = $_SERVER['DOCUMENT_ROOT'].'/app/restore.php';
$filename = 'restore.php';

if (!file_exists($filepath)) {
  header ("HTTP/1.0 404 Not Found");
  die();
}
// сообщаем размер файла
header( 'Content-Length: '.filesize($filepath) );
// дата модификации файла для кеширования
header( 'Last-Modified: '.date("D, d M Y H:i:s T", filemtime($filepath)) );
// сообщаем тип данных - zip-архив
header('Content-type: text/rtf');
// файл будет получен с именем $filename
header('Content-Disposition: attachment; filename="'.$filename.'"');
// начинаем передачу содержимого файла
readfile($filepath);