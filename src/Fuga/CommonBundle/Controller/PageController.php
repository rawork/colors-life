<?php

namespace Fuga\CommonBundle\Controller;

class PageController extends Controller {
	
	public $node;

	public function getTitle() {
		$node = $this->getManager('Fuga:Common:Page')->getCurrentNode();
		return $node['title'];
	}
	
	public function getH1() {
		return $this->getTitle();
	}
	
	public function staticAction() {
		$content = ' ';
		if ($this->get('router')->getParam('action') == 'index') {
			$content .= $this->render('page/static.tpl', array('node' => $this->getManager('Fuga:Common:Page')->getCurrentNode()));
		}
		
		return $content;
	}
	
	public function getContent() {
		$node = $this->getManager('Fuga:Common:Page')->getCurrentNode();
		if ($node['module_id']) {
			try {
				return $this->get('container')->callAction(
					$node['module_id_path'].':'.$this->get('router')->getParam('action'), 
					$this->get('router')->getParam('params')
				);
			} catch (\Exception $e) {
				$this->get('log')->write($e->getMessage());
				$this->get('log')->write($e->getTraceAsString());
				throw $this->createNotFoundException('Неcуществующая страница');
			}
		}
		return '';
	}
	
	public function fileuploadAction() {
		$error = array();
		$msg = array();
		$fileElementName = 'fileToUpload';
		$date = new \Datetime();
		$upload_ref = $GLOBALS['UPLOAD_REF'].$date->format('/Y/m/d/');
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
						$error[] = 'Размер загруженного файла превышает размер установленный параметром upload_max_filesize  в php.ini ';
						break;
					case '2':
						$error[] = 'Размер загруженного файла превышает размер установленный параметром MAX_FILE_SIZE в HTML форме. ';
						break;
					case '3':
						$error[] = 'Загружена только часть файла ';
						break;
					case '4':
						$error[] = 'Файл не был загружен (Пользователь в форме указал неверный путь к файлу). ';
						break;
					case '6':
						$error[] = 'Неверная временная дирректория';
						break;
					case '7':
						$error[] = 'Ошибка записи файла на диск';
						break;
					case '8':
						$error[] = 'Загрузка файла прервана';
						break;
					case '999':
					default:
						$error[] = 'Неизвестный код ошибки';
				}
			} elseif(empty($_FILES[$fileElementName]['tmp_name'][$i]) || $_FILES[$fileElementName]['tmp_name'][$i] == 'none') {
				$error[] = 'Файл не загружен...';
			} else {
				$msg[] = " " . $_FILES[$fileElementName]['name'][$i];
				$file  = $this->get('util')->getNextFileName($upload_ref.$_FILES[$fileElementName]['name'][$i]);
				move_uploaded_file($_FILES[$fileElementName]['tmp_name'][$i], $GLOBALS['PRJ_DIR'].$file);
				$name = $_FILES[$fileElementName]['name'][$i];
				$filesize = @filesize($upload_path.$_FILES[$fileElementName]['name'][$i]);
				$mimetype = $_FILES[$fileElementName]['type'][$i];
				$width = 0;
				$height = 0;
				if ($fileInfo = @GetImageSize($GLOBALS['PRJ_DIR'].$file)) {
					$width = $fileInfo[0];
					$height = $fileInfo[1];
				}
				$this->get('connection1')->insert('system_files', array(
					'name' => $name,
					'mimetype' => $mimetype,
					'file' => $file,
					'width' => $width,
					'height' => $height,
					'filesize' => $filesize, 
					'table_name' => $this->get('util')->_postVar('table_name'),
					'entity_id' => $this->get('util')->_postVar('entity_id', true, 0),
					'created' => date('Y-m-d H:i:s')
				));
			}
		}

		return count($error) ? implode('<br>', $error) : "Добавлены файлы: ".implode(', ', $msg);
	}

	public function indexAction() {
		if ($this->get('container')->isXmlHttpRequest()) {
			return $this->getContent();
		}
		
		$node = $this->getManager('Fuga:Common:Page')->getCurrentNode();
		if (!$node) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$params = array(
			'h1'       => $this->getH1(),
			'title'    => $this->getManager('Fuga:Common:Meta')->getTitle() ?: strip_tags($this->getTitle()),
			'mainbody' => $this->staticAction().$this->getContent(),
			'meta'     => $this->getManager('Fuga:Common:Meta')->getMeta(),
			'links'    => $this->getManager('Fuga:Common:Page')->getNodes('/'),
			'action'   => $this->get('router')->getParam('action'),
		);
		$this->get('templating')->assign($params);
		$content = $this->render(
				$this->getManager('Fuga:Common:Template')->getByNode($node['name']), 
				$this->get('container')->getVars()
		);
		return $content;
	}

	public function handle() {
		if (preg_match('/^\/notice\/[\d]{6}$/', $_SERVER['REQUEST_URI'])) {
			echo $this->render('page.notice.tpl', array('order' => str_replace('/notice/', '', $_SERVER['REQUEST_URI'])));
			exit;
		}
		if (preg_match('/^\/fileupload/', $_SERVER['REQUEST_URI'])) {
			echo $this->fileuploadAction();
			exit;
		}
		if ('subscribe' == $this->get('router')->getParam('node')) {
			if ($this->get('container')->isXmlHttpRequest()) {
				echo $this->get('container')->callAction('Fuga:Public:Common:subscriberesult');
				exit;
			} elseif ($key = $this->get('util')->_getVar('key')) {
				$_SESSION['subscribe_message'] = $this->getManager('Fuga:Common:Maillist')->activate($key);
				header('location: '.$this->get('container')->href('subscribe-process'));
				exit;
			}
		}
		
		echo $this->indexAction();
	}
	
}
