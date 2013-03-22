<?php

namespace Fuga\AdminBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;
use Fuga\AdminBundle\Controller\AdminController;
use Fuga\AdminBundle\Admin\Admin;
use Fuga\Component\Archive\GZipArchive;

class AdminAjaxController extends Controller {
	
	/** 
	 * Смена Меню компонентов при выборе группы функций
	 * @param string $state
	 * @param string $moduleName
	 * @return string 
	 */
	function getComponentList($state, $moduleName = '') 
	{
		$this->get('router')->setParam('state', $state);
		$this->get('router')->setParam('module', $moduleName);
		
		$modules = array();
		if ($this->get('util')->_sessionVar('user')) {
			$modulesAll = $this->get('container')->getModulesByState($state);
			foreach ($modulesAll as $module) {
				$modules[] = array(
					'name' => $module['name'],
					'title' => $module['title']
				);	
			}
		} else {
			return json_encode(array('alertText' => 'Сессия окончилась. Перезагрузите страницу'));
		}
		$text = $this->render('admin/mainmenu.tpl', compact('state', 'moduleName', 'modules'));
		
		return json_encode(array('content' => $text));
	}
	
	// Показать Меню таблиц для модуля
	function getTableList($state, $moduleName) {
		if ($this->get('util')->_sessionVar('user')) {
			$this->get('router')->setParam('state', $state);
			$this->get('router')->setParam('module', $moduleName);
			$uai = new AdminController(new Admin($moduleName), '', array($this->get('util')->_sessionVar('user') => 1));
			$text = $uai->getTableMenu();

			return json_encode(array('content' => $text));
		} else {
			return json_encode(array('alertText' => 'Сессия окончилась. Перезагрузите страницу'));
		}
	}
	
	// Выбор из списка разделов
	function showSelectPopup($inputId, $tableName, $fieldName, $entityId, $title) {
		$table = $this->get('container')->getTable($tableName);
		$fieldName = str_replace($entityId, '', $fieldName);
		$fieldName = str_replace('search_filter_', '', $fieldName);
		$field = $table->fields[$fieldName];
		$text = '<input type="hidden" id="popupChoiceId" value="'.$entityId.'">
Выбранный элемент:  <span id="popupChoiceTitle">'.$title.'</span>
<div id="selectlist">
<table class="table table-condensed">
<thead><tr>
<th>Название</th>
</tr></thead>';
		$where = '';
		if (!empty($field['l_lang'])) {
			$where .= "locale='".$this->get('router')->getParam('locale')."'";
		}
		$paginator = $this->get('paginator');
		$paginator->paginate($this->get('container')->getTable($field['l_table']), 'javascript:showPage(\'selectlist\',\''.$tableName.'\', \''.$fieldName.'\', '.$entityId.', ###)', $where, 8, 1, 6);
		$items = $this->get('container')->getItems($field['l_table'], $where, $field['l_field'], $paginator->limit);
		$fields = explode(',', $field['l_field']);
		foreach ($items as $item) {
			$fieldTitle = ''; 
			foreach ($fields as $fieldName)
				if (isset($item[$fieldName]))
					$fieldTitle .= ($fieldTitle ? ' ' : '').$item[$fieldName];
			$fieldTitle .= ' ('.$item['id'].')';
			$text .= '<tr>
<td><a href="javascript:void(0)" rel="'.$item['id'].'" class="popup-item">'.$fieldTitle.'</a></td>
</tr>';
		}
		$text .= '</table>';
		$text .= $paginator->render();
		$text .= '</div>';
		return json_encode( array(
			'title' => 'Выбор: '.$field['title'], 
			'button' => '<a class="btn btn-success" onclick="makePopupChoice(\''.$inputId.'\')">Выбрать</a>',
			'content' => $text
		));
	}
	
	function showPage($divId, $tableName, $fieldName, $entityId, $page = 1) {
		$table = $this->get('container')->getTable($tableName);
		$field = $table->fields[$fieldName];
		$text = '<table class="table table-condensed">
<thead><tr>
<th>Название</th>
</tr></thead>';
		$where = '';
		if (!empty($field['l_lang'])) {
			$where = "locale='".$this->get('router')->getParam('locale')."'";
		}
		$paginator = $this->get('paginator');
		$paginator->paginate($this->get('container')->getTable($field['l_table']), 'javascript:showPage(\''.$divId.'\',\''.$tableName.'\', \''.$fieldName.'\', '.$entityId.', ###)', $where, 8, $page, 6);
		$items = $this->get('container')->getItems($field['l_table'], $where, $field['l_field'], $paginator->limit);
		$fields = explode(',', $field['l_field']);
		foreach ($items as $item) {
			$fieldTitle = ''; 
			foreach ($fields as $fieldName)
				if (isset($item[$fieldName]))
					$fieldTitle .= ($fieldTitle ? ' ' : '').$item[$fieldName];
			$fieldTitle .= ' ('.$item['id'].')';
			$text .= '<tr>
<td><a href="javascript:void(0)" rel="'.$item['id'].'" class="popup-item">'.$fieldTitle.'</a></td>
</tr>';
		}
		$text .= '</table>';
		$text .= $paginator->render();
		return json_encode( array(
			'content' => $text
		));
	}
	
	// Выбор из дерева разделов
	function showTreePopup($inputId, $tableName, $fieldName, $entityId, $title) {
		$table = $this->get('container')->getTable($tableName);
		$fieldName = str_replace($entityId, '', $fieldName);
		$fieldName = str_replace('search_filter_', '', $fieldName);
		$field = $table->fields[$fieldName];
		$text = '<input type="hidden" id="popupChoiceId" value="'.$entityId.'">
Выбранный элемент: <span id="popupChoiceTitle">'.$title.'</span>
<ul id="navigation">
<li><a href="javascript:void(0)" rel="0" class="popup-item">Не выбрано</a></li>';
		if (!empty($field['l_lang'])) {
			$lang_where = "locale='".$this->get('router')->getParam('locale')."'";
		} else {
			$lang_where = '';
		}
		$field['l_sort'] = !empty($field['l_sort']) ? $field['l_sort'] : $field['l_field'];
		
		$nodes = $this->get('container')->getItems($field['l_table'], $lang_where, $field['l_sort']);
		$rootNodes = array();
		$readyNodes = array();
		foreach ($nodes as $node) {
			$node['children'] = array();
			$readyNodes[$node['id']] = $node;
		}
		foreach ($readyNodes as $node) {
			if ($node['parent_id'] == 0) {
				$rootNodes[$node['id']] = $node;
			} elseif (isset($readyNodes[$node['parent_id']])) {
				$readyNodes[$node['parent_id']]['children'][$node['id']] = $node;
			}
			
		}
		foreach ($rootNodes as $node) {
			$text .= $this->buildTree($node, $readyNodes, $field);
		}
		$text .= '</ul>';
		return json_encode( array(
			'title' => 'Выбор: '.$field['title'], 
			'button' => '<a class="btn btn-success" onclick="makePopupChoice(\''.$inputId.'\')">Выбрать</a>',
			'content' => $text
		));
	}
	
	private function buildTree($node, $nodes, $field) {
		$fields = explode(',', $field['l_field']);
		$vname = '';
		foreach ($fields as $fieldName)
			if (isset($node[$fieldName]))
				$vname .= ($vname ? ' ' : '').$node[$fieldName];
		$text = '<li><a rel="'.$node['id'].'" href="javascript:void(0)" class="popup-item">'.$vname.' ('.$node['id'].')</a>';
		$this->counter++;
		$children = $nodes[$node['id']]['children'];
		if (count($children)) {
			$text .= '<ul>'; 
			foreach($children as $child) {
				$text .= $this->buildTree($child, $nodes, $field);
			}
			$text .= '</ul>';
		}	
		$text .= '</li>';
		return $text;
	}
	
	// Множественный выбор
	function showListPopup($inputId, $tableName, $fieldName, $value) {
		$values = explode(',', $value);
		$table = $this->get('container')->getTable($tableName);
		$field = $table->fields[$fieldName];
		$text = '_'.$value.'_'.'<table class="table table-condensed">
<thead><tr>
<th>Название</th>
<th><i class="icon icon-align-justify"></i></th>
</tr></thead>';
		$text .= $this->getPopupList($field, $values);
		$text .= '</table>';
		
		return json_encode( array(
			'title' => 'Выбор: '.$field['title'], 
			'button' => '<a class="btn btn-success" onclick="makeListChoice('."'".$inputId."'".')">Выбрать</a>',
			'content' => $text
		));
	}
	
    function getPopupList($field, $values) {
		$content = '';
		$lang_where = !empty($field['l_lang']) ? "locale='".$this->get('router')->getParam('locale')."'" : '';
		if (!empty($field['query'])) {
			$lang_where .= ($lang_where ? ' AND ' : '').'('.$field['query'].')';
		}
		$field['l_sort'] = !empty($field['l_sort']) ? $field['l_sort'] : $field['l_field'];
        $items = $this->get('container')->getItems($field["l_table"], $lang_where, $field["l_sort"]);
		$fields = explode(",", $field["l_field"]);
        foreach ($items as $item) {
			$fullName = '';
			foreach ($fields as $fieldName) {
				if (array_key_exists($fieldName, $item)) {
					$fullName .= ($fullName ? ' ' : '').$item[$fieldName];
				}
			}
			$content .= '
<tr>
<td width="93%" valign="center"><span id="itemTitle'.$item['id'].'">'.$fullName.' ('.$item['id'].')</span></td>
<td width="3%"><input class="popup-item" value="'.$item['id'].'" type="checkbox"';
			if (in_array($item['id'], $values)) {
				$content .= ' checked';
			}
        $content .= '></td>
</tr>';
        }
		return $content;
    }
	
	// Окно с версиями шаблона
	function showTemplateVersion($versionId) {
		global $PRJ_DIR;
		$version = $this->get('container')->getItem('template_version', $versionId);
		$text = @file_get_contents($PRJ_DIR.$version['file']);
		return json_encode( array(
			'title' => 'Версия шаблона', 
			'button' => '<a class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</a>',
			'content' => '<textarea wrap="off" name="mytemplatetemp" readonly style="height:99%; width:100%" rows="15" cols="45">'.htmlspecialchars($text).'</textarea>'
		));
	}
	
	function showCopyDialog($id) {
		return json_encode( array(
			'title' => 'Копирование элемента', 
			'button' => '<a class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</a><a class="btn btn-success" onclick="goCopy(\'/copy/'.$id.'\')">Копировать</a>',
			'content' => '
<div class="control-group" id="copyInput">
  <label class="control-label" for="inputError">Количество новых (1-10)</label>
  <div class="controls">
    <input type="text" id="copyQuantity" value="1">
    <span class="help-inline" id="copyHelp"></span>
  </div>
</div>'
		));
	}
	
	// старая новая разработка - неживое
	function editField($fieldId, $formdata) {
		if (count($formdata)) {
			return json_encode( array(
				'title' => 'Редактирование поля: '.$field['title'], 
				'button' => '<a class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</a><a class="btn btn-success" onclick="updateField()">Сохранить</a>',
				'content' => '<textarea wrap="off" name="mytemplatetemp" readonly style="height:99%; width:100%" rows="15" cols="45">'.htmlspecialchars($text).'</textarea>'
			));
		} else {
			return json_encode(array('alertText' => 'Все плохо :)'));
		}
	}
	
	public function createArchive() {
		$my_time = time();
		$my_key = $this->get('util')->genKey(8);
		
		$filename = date('YmdHi',$my_time).'_'.$my_key.'.tar.gz';
		$filename_sql = date('YmdHi',$my_time).'_'.$my_key.'.sql';
		$filename_sql2 = date('YmdHi',$my_time).'_'.$my_key.'_after_connect.sql';
		$f = fopen($GLOBALS['BACKUP_DIR'].DIRECTORY_SEPARATOR.$filename_sql2, "a");
		fwrite($f, "/*!41000 SET NAMES 'utf8' */;");
		fclose($f);
		set_time_limit(0);
		$this->get('container')->backupDB($GLOBALS['BACKUP_DIR'].DIRECTORY_SEPARATOR.$filename_sql);
		$cwd = getcwd();
		chdir($GLOBALS['PRJ_DIR'].'/');
		system('tar -czf '.$GLOBALS['BACKUP_DIR'].'/'.$filename.' --exclude=*.lock --exclude=autoload.php --exclude=*.tar.gz --exclude=./vendor/bin --exclude=./vendor/composer --exclude=./vendor/doctrine --exclude=./vendor/symfony --exclude=./vendor/twig --exclude=./.git --exclude=*.tpl.php ./');
		chdir($cwd);
		if (file_exists($GLOBALS['BACKUP_DIR'].'/'.$filename)) {
			chmod($GLOBALS['BACKUP_DIR'].'/'.$filename, 0664);
		}
//		$archive = new GZipArchive($GLOBALS['BACKUP_DIR'].DIRECTORY_SEPARATOR.$filename);
//		$archive->set_options(array('basedir' => $GLOBALS['PRJ_DIR'].DIRECTORY_SEPARATOR, 'overwrite' => 1, 'level' => 5));
//		$archive->addFiles(array('*.*'));
//		$archive->excludeFiles(array('*.tar.gz'));
//		$cfiles = 0;
//		$sfiles = 0;
//		
//		foreach ($archive->files as $key => $current) {
//			if (stristr($current['name'], '.tar.gz')) {
//				unset($archive->files[$key]);
//			} else {
//				$sfiles += $current['stat'][7];
//				$cfiles++;
//			}
//		}
//		
//		$archive->createArchive();
		
		$text = '';
		$text .= '<strong>Архив создан</strong><br>';
//		$text .= 'Количество файлов: '.$cfiles;
//		$text .= '<br>';
//		$text .= 'Размер неупакованых файлов: '.$this->get('util')->getSize($sfiles, 2);
//		$text .= '<br>';
		$text .= 'Размер архива: '.$this->get('filestorage')->size($GLOBALS['BACKUP_REF'].'/'.$filename);
		@unlink($GLOBALS['BACKUP_DIR'].'/'.$filename_sql);
		@unlink($GLOBALS['BACKUP_DIR'].'/'.$filename_sql2);
		$_SESSION['archiveReport'] = $text;
		return json_encode(array('content' => $text));
	}
	
	public function clearCache() {
		$this->get('templating')->clearTpl();
//		$this->get('templating')->clearCache();
		return json_encode(array('content' => 'Кэш очищен'));
	}
	
	
	function delFile($id) {
		$sql = "SELECT file FROM system_files WHERE id= :id ";
		$stmt = $this->get('connection1')->prepare($sql);
		$smtm->bindValue('id', $id);
		$stmt->execute();
		$file = $stmt->fetch();
		if ($file) {
			$this->get('filestorage')->remove($file['file']);
			$this->get('connection1')->delete('system_files', array('id' => $id));
			return json_encode(array('status' => 'ok'));
		} else {
			return json_encode(array('alertText' => 'Ошибка удаления файла'));
		}
	}
	
	function updateFileList($table, $id) {
		$sql = "SELECT * FROM system_files WHERE table_name= :table AND entity_id= :id ";
		$stmt = $this->get('connection1')->prepare($sql);
		$smtm->bindValue('table', $table);
		$smtm->bindValue('id', $id);
		$stmt->execute();
		$items = $stmt->fetchAll();
		$ret = '';
		$ret .= '<table class="table table-condensed">';
		$ret .= '<thead><tr>';
		$ret .= '<th width="85%">Файл</th>';
		$ret .= '<th width="10%">Размер</th>';
		$ret .= '<th><i class="icon-align-justify"></i></th>';
		$ret .= '</tr></thead>';
		foreach ($items as $item) {
			$ret .= '<tr id="file_'.$item['id'].'">';
			$ret .= '<td><a href="'.$item['file'].'">'.$item['name'].'</a></td>';
			$ret .= '<td>'.$item['filesize'].' байт</td>';
			$ret .= '<td><a href="#" class="btn btn-small btn-danger" onClick="delFile(\''.$item['id'].'\',\''.$item['name'].'\',\''.$table.'\',\''.$id.'\'); return false"><i class="icon-trash icon-white"></i></a></td>'."\n";
			$ret .= '</tr>';	
		}
		$ret .= '</table>';
		return json_encode(array('content' => $ret));
	}
	
	function getPriceList($productId) {
		$content = '<table class="table table-condensed">
<thead><tr>
<th width="30%">Размер</th>
<th width="30%">Цвет</th>
<th width="30%">Цена</th>
<th width="5%">Порядок</th>
<th width="1%">Акт</th>
<th><i class="icon-align-justify"></i></th>
</tr></thead>';
				
		$sql = "SELECT p.id, s.name as size_id_name, c.name as color_id_name, p.price, p.sort, p.publish 
			FROM catalog_price p JOIN catalog_size s ON p.size_id=s.id 
			JOIN catalog_color c ON p.color_id=c.id 
			WHERE p.product_id= :id ORDER BY p.price";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->bindValue('id', $productId);
		$stmt->execute();
		$items = $stmt->fetchAll();
		foreach ($items as $item) {
			$content .= '<tr id="price_'.$item['id'].'">';
			$content .= '<td>'.$item['size_id_name'].'</td>';
			$content .= '<td>'.$item['color_id_name'].'</td>';
			$content .= '<td><input type="text" class="input-mini right" name="price_'.$item['id'].'" value="'.$item['price'].'" /></td>';
			$content .= '<td><input type="text" class="input-mini" name="sort_'.$item['id'].'" value="'.$item['sort'].'" /></td>';
			$content .= '<td><input type="checkbox" name="publish_'.$item['id'].'" value="on"'.($item['publish'] ? ' checked' : '').'></td>';
			$content .= '<td><a href="javascript:void(0)" class="btn btn-small btn-danger" onClick="delPrice('.$item['id'].')"><i class="icon-trash icon-white"></i></a></td>'."\n";
			$content .= '</tr>';	
		}
		$content .= '</table>';
		return $content;
	}
	
	function addPrice($formdata) {
		parse_str($formdata);
		$this->get('connection1')->insert('catalog_price', array(
			'product_id' => $product_id,
			'size_id' => $size_id,
			'color_id' => $color_id,
			'price' => $price,
			'sort' => $sort,
			'publish' => isset($publish) ? 1 : 0,
			'created' => date('Y-m-d H:i:s')
		));

		return json_encode(array('content' => $this->getPriceList($product_id)));
	}
	
	function delPrice($priceId) {
		$this->get('connection1')->delete('catalog_price', array('id' => $priceId));
		
		return json_encode(array('status' => 'ok'));
	}
	
	function updatePrices($formdata){
		parse_str($formdata);
		$sql = "SELECT p.id, p.product_id FROM catalog_price p JOIN catalog_size s ON p.size_id=s.id JOIN catalog_color c ON p.color_id=c.id WHERE p.product_id= :id ORDER BY p.price";
		$stmt = $this->get('connection1')->prepare($sql);
		$smtm->bindValue('id', $product_id);
		$stmt->execute();
		$items = $stmt->fetchAll();
		foreach ($items as $item) {
			$priceName = 'price_'.$item['id'];
			$sortName = 'sort_'.$item['id'];
			$publishName = 'publish_'.$item['id'];
			$price = isset($$priceName) ? $$priceName : 0;
			$sort = isset($$sortName) ? $$sortName : 0;
			$publish = isset($$publishName) ? 1 : 0;
			$this->get('connection1')->update('catalog_price', 
				array('price' => $price, 'sort' => $sort, 'publish' => $publish),
				array('id' => $item['id'])
			);
		}
		
		return json_encode(array('content' => $this->getPriceList($product_id)));
	}
	
	function updateRpp($tableName, $rpp = 25) {
		$_SESSION[$tableName.'_rpp'] = $rpp;
			
		return json_encode(array('status' => $_SESSION[$tableName.'_rpp']));
	}
	
}