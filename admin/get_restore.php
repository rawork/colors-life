<?
	
$filename = $_SERVER['DOCUMENT_ROOT'].'/app/restore.php';
$sfilename = 'restore.php';

if (!file_exists($filename)) {
  header ("HTTP/1.0 404 Not Found");
  die();
}
// сообщаем размер файла
header( 'Content-Length: '.filesize($filename) );
// дата модификации файла для кеширования
header( 'Last-Modified: '.date("D, d M Y H:i:s T", filemtime($filename)) );
// сообщаем тип данных - zip-архив
header('Content-type: text/rtf');
// файл будет получен с именем $filename
header('Content-Disposition: attachment; filename="'.$sfilename.'"');
// начинаем передачу содержимого файла
readfile($filename);