<?php

namespace Fuga\AdminBundle\Controller;

use Fuga\CMSBundle\Controller\Controller;
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
			$where .= " AND lang='".$this->get('router')->getParam('lang')."'";
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
			$where = "lang='".$this->get('router')->getParam('lang')."'";
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
<li><a href="javascript:void(0)" rel="0" class="popup-item">Корень</a></li>';
		if (!empty($field['l_lang'])) {
			$lang_where = "lang='".$this->get('router')->getParam('lang')."'";
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
			if ($node['p_id'] == 0) {
				$rootNodes[$node['id']] = $node;
			} elseif (isset($readyNodes[$node['p_id']])) {
				$readyNodes[$node['p_id']]['children'][$node['id']] = $node;
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
		$lang_where = !empty($field['l_lang']) ? "lang='".$this->get('util')->_sessionVar('lang', false, 'ru')."'" : '';
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
		$version = $this->get('container')->getItem('templates_version', $versionId);
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
	
	function makeArchive() {
		$my_time = time();
		$my_key = $this->get('util')->genKey(8);
		
		$filename = date('YmdHi',$my_time).'_'.$my_key.'.tar.gz';
		$filename_sql = date('YmdHi',$my_time).'_'.$my_key.'.sql';
		$filename_sql2 = date('YmdHi',$my_time).'_'.$my_key.'_after_connect.sql';
		
		$f = fopen($GLOBALS['BACKUP_DIR'].'/'.$filename_sql2, "a");
		fwrite($f, "/*!41000 SET NAMES 'utf8' */;");
		fclose($f);
		set_time_limit(0);
		$this->get('connection')->backupDB($GLOBALS['BACKUP_DIR'].'/'.$filename_sql);
		$archive = new GZipArchive($GLOBALS['BACKUP_DIR'].'/'.$filename);
		$archive->set_options(array('basedir' => $GLOBALS['PRJ_DIR'].'/', 'overwrite' => 1, 'level' => 5));
		$archive->addFiles(array('*.*'));
		$archive->excludeFiles(array('*.tar.gz', 'conf.db.php'));
		$cfiles = 0;
		$sfiles = 0;
		
		foreach ($archive->files as $key => $current) {
			if (stristr($current['name'], '.tar.gz')) {
				unset($archive->files[$key]);
			} else {
				$sfiles += $current['stat'][7];
				$cfiles++;
			}
		}
		
		$archive->createArchive();
		
		$text = '';
		$text .= '<strong>Архив создан</strong><br>';
		$text .= 'Количество файлов: '.$cfiles;
		$text .= '<br>';
		$text .= 'Размер неупакованых файлов: '.$this->get('util')->getSize($sfiles, 2);
		$text .= '<br>';
		$text .= 'Размер архива: '.$this->get('filestorage')->size($GLOBALS['BACKUP_REF'].'/'.$filename);
		@unlink($GLOBALS['BACKUP_DIR'].'/'.$filename_sql);
		@unlink($GLOBALS['BACKUP_DIR'].'/'.$filename_sql2);
		$_SESSION['archiveReport'] = $text;
		return json_encode(array('content' => $text));
	}
	
	
	function delFile($id) {
		$sql = "SELECT name,file FROM system_files WHERE id=$id";
		$file = $this->get('connection')->getItem('delfile', $sql);
		if ($file) {
			@unlink($GLOBALS['PRJ_DIR'].$file['file']);
			$sql = "DELETE FROM system_files WHERE id=".$id;
			$this->get('connection')->execQuery('delfile', $sql);
			return json_encode(array('status' => 'ok'));
		} else {
			return json_encode(array('alertText' => 'Ошибка удаления файла'));
		}
	}
	
	function updateFileList($tableName, $entityId) {
		$sql = "SELECT * FROM system_files WHERE table_name='".$tableName."' AND entity_id=".$entityId;
		$my_files = $this->get('connection')->getItems('filelist', $sql);
		$ret = '';
		$ret .= '<table class="table table-condensed">';
		$ret .= '<thead><tr>';
		$ret .= '<th width="85%">Файл</th>';
		$ret .= '<th width="10%">Размер</th>';
		$ret .= '<th><i class="icon-align-justify"></i></th>';
		$ret .= '</tr></thead>';
		foreach ($my_files as $fileitem) {
			$ret .= '<tr id="file_'.$fileitem['id'].'">';
			$ret .= '<td><a href="'.$fileitem['file'].'">'.$fileitem['name'].'</a></td>';
			$ret .= '<td>'.$fileitem['filesize'].' байт</td>';
			$ret .= '<td><a href="#" class="btn btn-small btn-danger" onClick="delFile(\''.$fileitem['id'].'\',\''.$fileitem['name'].'\',\''.$tableName.'\',\''.$entityId.'\'); return false"><i class="icon-trash icon-white"></i></a></td>'."\n";
			$ret .= '</tr>';	
		}
		$ret .= '</table>';
		return json_encode(array('content' => $ret));
	}
	
	function getPriceList($stuff_id) {
		global $THEME_REF;		
		$ret = '<table class="table table-condensed">';
		$ret .= '<thead><tr>';
    	$ret .= '<th width="30%">Размер</th>';
		$ret .= '<th width="30%">Цвет</th>';
		$ret .= '<th width="30%">Цена</th>';
		$ret .= '<th width="5%">Порядок</th>';
		$ret .= '<th width="1%">Акт</th>';
		$ret .= '<th><i class="icon-align-justify"></i></th>';
    	$ret .= '</tr></thead>';
				
		$sql = "SELECT p.id, s.name as size_id_name, c.name as color_id_name, p.price, p.ord, p.publish FROM catalog_prices p JOIN catalog_sizes s ON p.size_id=s.id JOIN catalog_color c ON p.color_id=c.id WHERE p.stuff_id=".$stuff_id." ORDER BY p.price";
		$prices = $this->get('connection')->getItems('sizelist', $sql);
		foreach ($prices as $priceitem) {
			$ret .= '<tr id="price_'.$priceitem['id'].'">';
			$ret .= '<td>'.$priceitem['size_id_name'].'</td>';
			$ret .= '<td>'.$priceitem['color_id_name'].'</td>';
			$ret .= '<td><input type="text" class="input-mini right" name="price_'.$priceitem['id'].'" value="'.$priceitem['price'].'" /></td>';
			$ret .= '<td><input type="text" class="input-mini" name="ord_'.$priceitem['id'].'" value="'.$priceitem['ord'].'" /></td>';
			$ret .= '<td><input type="checkbox" name="publish_'.$priceitem['id'].'" value="on"'.($priceitem['publish'] ? ' checked' : '').'></td>';
			$ret .= '<td><a href="javascript:void(0)" class="btn btn-small btn-danger" onClick="delPrice('.$priceitem['id'].')"><i class="icon-trash icon-white"></i></a></td>'."\n";
			$ret .= '</tr>';	
		}
		$ret .= '</table>';
		return $ret;
	}
	
	function addPrice($formdata) {
		parse_str($formdata);
		$sql = "INSERT INTO catalog_prices(stuff_id,size_id,color_id,price,ord,publish,credate) VALUES(".$stuff_id.",".$size_id.",".$color_id.",'".$price."','".$ord."','".(isset($publish) ? 'on' : '')."',NOW())";
		$this->get('connection')->execQuery('addprice', $sql);
		$text = $this->getPriceList($stuff_id);
		
		return json_encode(array('content' => $text));
	}
	
	function delPrice($priceId) {
		$sql = "DELETE FROM catalog_prices WHERE id=".$priceId;
		$this->get('connection')->execQuery('delprice', $sql);
		
		return json_encode(array('status' => 'ok'));
	}
	
	function updatePrices($formdata){
		parse_str($formdata);
		$sql = "SELECT p.id, p.stuff_id FROM catalog_prices p JOIN catalog_sizes s ON p.size_id=s.id JOIN catalog_color c ON p.color_id=c.id WHERE p.stuff_id=".$stuff_id." ORDER BY p.price";
		$items = $this->get('connection')->getItems('sizelist', $sql);
		foreach ($items as $item) {
			$priceName = 'price_'.$item['id'];
			$ordName = 'ord_'.$item['id'];
			$publishName = 'publish_'.$item['id'];
			$price = isset($$priceName) ? $$priceName : 0;
			$ord = isset($$ordName) ? $$ordName : 0;
			$publish = isset($$publishName) ? $$publishName : '';
			$sql = "UPDATE catalog_prices SET price='$price', ord='$ord', publish='$publish' WHERE id=".$item['id'];
			$this->get('connection')->execQuery('updateprice', $sql);
		}
		$text = $this->getPriceList($stuff_id);
		
		return json_encode(array('content' => $text));
	}
	
	function updateRpp($tableName, $rpp = 25) {
		$_SESSION[$tableName.'_rpp'] = $rpp;
			
		return json_encode(array('status' => $_SESSION[$tableName.'_rpp']));
	}
	
}