<?
	
$filename = $_SERVER['DOCUMENT_ROOT'].'/admin/lib/tools/restore.php';
$sfilename = 'restore.php';

if (!file_exists($filename)) {
  header ("HTTP/1.0 404 Not Found");
  die();
}
// �������� ������ �����
header( 'Content-Length: '.filesize($filename) );
// ���� ����������� ����� ��� �����������
header( 'Last-Modified: '.date("D, d M Y H:i:s T", filemtime($filename)) );
// �������� ��� ������ - zip-�����
header('Content-type: text/rtf');
// ���� ����� ������� � ������ $filename
header('Content-Disposition: attachment; filename="'.$sfilename.'"');
// �������� �������� ����������� �����
readfile($filename);