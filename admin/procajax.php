<?php
    include_once('../loader.php');
	inc_lib('tools/xajax/xajax.inc.php');
    
	$xajax = new xajax('/admin/procajax.php');
	//$xajax->setFlag('debug',true);
	$xajax->registerFunction('getComponentList');
	$xajax->registerFunction('getTableList');
	
	/* popup Windows */
	$xajax->registerFunction('show_tree_popup');
	$xajax->registerFunction('show_list_popup');
	$xajax->registerFunction('showTemplateVersion');
	$xajax->registerFunction('showDuplicateSettings');
	$xajax->registerFunction('editField');
	
	$xajax->registerFunction('makeArchive');
	$xajax->registerFunction('delFile');
	$xajax->registerFunction('updateFileList');
	
	$xajax->registerFunction('addPrice');
	$xajax->registerFunction('updatePrices');
	$xajax->registerFunction('delPrice');
	
	// Смена Меню компонентов при выборе группы функций
	function getComponentList($state, $unit = '') {
	global $smarty;
		$objResponse = new xajaxResponse();
		$smarty->assign('state', $state);
		$smarty->assign('unit', $unit);
		$units = array();
		switch ($state) {
			case 'content': $stateLetter = 'C'; break;
			case 'settings': $stateLetter = 'A'; break;
			case 'service': $stateLetter = 'S'; break;
			default : $stateLetter = 'N';
		}
		if (CUtils::_sessionVar('user')) {
			$avail_units = $GLOBALS['rtti']->getComponents();
			foreach ($avail_units as $k => $u) {
				if ($u['ctype'] == $stateLetter) {
					$units[] = array(
						'name' => $u['name'],
						'title' => $u['title']
					);	
				}
			}
		} else {
			$objResponse->script("alert('Ошибка сессии')");
		}
		$smarty->assign('units', $units);
		$text = $smarty->fetch('admin/mainmenu.tpl');
		$objResponse->assign('componentMenu', 'innerHTML', $text);
		$objResponse->script("hideDiv('waiting');");
		return $objResponse;
	}
	
	// Показать Меню таблиц для модуля
	function getTableList($component_name, $state) {
	global $smarty;
		inc_lib('AdminInterface/UnitAdminInterface.php');
		$_GET['state'] = $state;
		$objResponse = new xajaxResponse();
		if (CUtils::_sessionVar('user')) {
			$ocomponent = $GLOBALS['rtti']->getComponent($component_name);
			if (inc_u($component_name)) {
				$className = ucfirst($component_name).'Unit';
				$unit = new $className();
			} else {
				inc_lib('components/Unit.php');
				$unit = new Unit($component_name);
			}
			$uai = new UnitAdminInterface($unit, '', array());
			$text = $uai->getTableMenu();
			$objResponse->assign('tableMenu_'.$component_name, 'innerHTML', $text);
			$objResponse->script("showDiv('tableMenu_".$component_name."', 0, 0);");
			$objResponse->script("hideDiv('waiting');");
		} else {
			$objResponse->script("alert('Ошибка сессии');");
		}
		return $objResponse;
	}
	
	function tree_popup_showItem($id, $name, $prefix_width, $div_left, $f, $value) {
	global $THEME_REF;
        $ret = '<tr onClick="$(\'#tree_val_'.$id.'\').attr(\'checked\', true);marked_choice('.$id.')"><td width="100%" valign="top"><span><img src="'.$THEME_REF.'/img/0.gif" width="'.$prefix_width.'" height="16">&rsaquo;&nbsp;</span><span id="tree_title_'.$id.'">'.$name.'</span></td>'."\n";
		$checked = '';
		if ($value == $id) {
			$checked = 'checked';
		}
        $ret .= '<td width="1%"><input class="one_check" onClick="marked_choice('.$id.')" name="one_name_for_all" type="radio" '.$checked.' id="tree_val_'.$id.'" value="'.$id.'"></td></tr>'."\n";
		return $ret; 
    }
    function tree_popup_show_subtree($p_id, $prefix_width = 0, $exclude, $f, $value) {
		$ret = '';
		if (!empty($f['l_lang'])) {
			$lang_where = " AND lang='".CUtils::_sessionVar('lang', false, 'ru')."'";
		} else {
			$lang_where = '';
		}
		$f['l_sort'] = !empty($f['l_sort']) ? $f['l_sort'] : $f['l_field'];
		$trees = $GLOBALS['rtti']->getItems($f['l_table'], 'p_id='.$p_id.$lang_where, $f['l_sort']);
		$fields = explode(',', $f['l_field']);
        foreach ($trees as $a) {
            // ограничение на себя, в качестве родителя
            if ($a['id'] != $exclude) {
				$vname = '';
				foreach ($fields as $fi)
					if (isset($a[$fi]))
						$vname .= ($vname ? ' ' : '').$a[$fi];
                $ret .= tree_popup_showItem($a['id'], $vname.' ['.$a['id'].']', $prefix_width, $prefix_width + 18, $f, $value);
                $ret .= tree_popup_show_subtree($a['id'], $prefix_width + 15, $exclude, $f, $value);
            }
        }
		return $ret;
    }
	
	function list_popup_showItem($id, $name, $f, $values) {
		$ret = '';
        $ret .= '<tr><td width="100%" valign="center"><span id="list_title_'.$id.'">'.$name.'</span></td>'."\n";
        $ret .= '<td align="center" width="1%"><input id="list_val_'.$id.'" class="check_list" name="one_name_for_all" value="'.$id.'" type="checkbox"';
        foreach ($values as $v) {
            if ($id == $v) {
                $ret .= ' checked';
                break;
            }
        }
        $ret .= '></td></tr>'."\n";
		return $ret;
    }
    function list_popup_show($f, $values) {
		$ret = '';
		$lang_where = !empty($f['l_lang']) ? "lang='".CUtils::_sessionVar('lang', false, 'ru')."'" : '';
		if (!empty($f['query'])) {
			$lang_where .= ($lang_where ? ' AND ' : '').'('.$f['query'].')';
		}
		$f['l_sort'] = !empty($f['l_sort']) ? $f['l_sort'] : $f['l_field'];
        $items = $GLOBALS['rtti']->getItems($f["l_table"], $lang_where, $f["l_sort"]);
		$fields = explode(",", $f["l_field"]);
        foreach ($items as $a) {
			$vname = '';
			foreach ($fields as $fi) {
				if (isset($a[$fi])) {
					$vname .= ($vname ? ' ' : '').$a[$fi];
				}
			}
            $ret .= list_popup_showItem($a['id'], $vname.' ['.$a['id'].']', $f, $values);
        }
		return $ret;
    }
	
	// Выбор из дерева разделов
	function show_tree_popup($input_id, $table_name, $field_name, $dbid, $zero_title, $value) {
		$t = $GLOBALS['rtti']->getTable($table_name);
		$real_field_name = str_replace($dbid, '', $field_name);
		$real_field_name = str_replace('search_filter_', '', $real_field_name);
		$f = $t->fields[$real_field_name];
		$text = '<table class="tfields" align="center" border="0" width="95%" cellspacing="0" cellpadding="3">';
    	$text .= tree_popup_showItem(0, $zero_title, 0, 18, $f, intval($value));
	    $text .= tree_popup_show_subtree(0, 15, $f['l_table'] == $t->getDBTableName() ? $dbid : -1, $f, intval($value));
		$text .= '</table>';
		$objResponse = new xajaxResponse();
		$objResponse->script("marked_choice($value)");
		$objResponse->assign('popup_title', 'innerHTML', $f['title']);
		$objResponse->assign('popup_button', 'innerHTML', '<input type="button" class="adm-btn" value="Выбрать" onclick="make_tree_choice('."'".$input_id."'".')">');
		$objResponse->assign('popup_body', 'innerHTML', $text);
		$objResponse->script("showPopup()");
		return $objResponse;
	}
	
	// Множественный выбор
	function show_list_popup($input_id, $table_name, $field_name, $dbid, $value) {
		$values = !empty($value) ? explode(',', $value) : array();
		$t = $GLOBALS['rtti']->getTable($table_name);
		$f = $t->fields[$field_name];
		$text = '<table class="tfields" align="center" border="0" width="95%" cellspacing="1" cellpadding="2">';
		$text .= list_popup_show($f, $values);
		$text .= '</table>';
		$objResponse = new xajaxResponse();
		$objResponse->assign('popup_title', 'innerHTML', $f['title']);
		$objResponse->assign('popup_button', 'innerHTML', '<input type="button" class="adm-btn" value="Выбрать" onclick="make_list_choice('."'".$input_id."'".')">');
		$objResponse->assign('popup_body', 'innerHTML', $text);
		$objResponse->script("showPopup()");
		return $objResponse;
	}
	
	// Окно с версиями шаблона
	function showTemplateVersion($ver_id) {
	global $PRJ_DIR;
		$ver = $GLOBALS['rtti']->getItem('templates_version', $ver_id);
		$text = @file_get_contents($PRJ_DIR.$ver['file']);
		$objResponse = new xajaxResponse();
		$objResponse->assign('popup_title', 'innerHTML', 'Версия шаблона');
		$objResponse->assign('popup_button', 'innerHTML', '<input type="button" class="adm-btn" value="Закрыть" onclick="hidePopup()">');
		$objResponse->assign('popup_body', 'innerHTML', '<textarea wrap="off" name="mytemplatetemp" readonly style="height:99%; width:100%" rows="15" cols="45">'.htmlspecialchars($text).'</textarea>');
		$objResponse->script("showPopup()");
		return $objResponse;
	}
	
	function showDuplicateSettings($ref) {
	global $PRJ_DIR;
		$objResponse = new xajaxResponse();
		$objResponse->assign('popup_title', 'innerHTML', 'Копирование записи');
		$objResponse->assign('popup_button', 'innerHTML', '<input type="button" class="adm-btn" value="Копировать" onclick="goDuplicate(\''.$ref.'\')">');
		$objResponse->assign('popup_body', 'innerHTML', 'Количество новых <br /><input name="DuplicateQuantity" id="DuplicateQuantity" value="1" />');
		$objResponse->script("showPopup()");
		return $objResponse;
	}
	
	function editField($field_id, $fD = array()) {
		$objResponse = new xajaxResponse();
		if (count($fD)) {
			$objResponse->assign('popup_title', 'innerHTML', 'Редактирование поля: '.$field['title']);
			$objResponse->assign('popup_button', 'innerHTML', '<input type="button" class="adm-btn" value="Сохранить" onclick="xajax_updateField(xajax)">&nbsp;<input type="button" class="adm-btn" value="Закрыть" onclick="hidePopup()">');
			$objResponse->assign('popup_body', 'innerHTML', $GLOBALS['rtti']->updateField($field_id, $fD));
			$objResponse->script("showPopup()");
		} else {
			
		}
		return $objResponse;
	}
	
	function makeArchive($fD) {
		$my_time = time();
		$my_key = CUtils::genKey(8);
		$objResponse = new xajaxResponse();
		$objResponse->assign("archive_info", 'innerHTML', '');
		$filename = 'admin/backup/'.date('YmdHi',$my_time).'_'.$my_key.'.tar.gz';
		$filename_sql = 'admin/backup/'.date('YmdHi',$my_time).'_'.$my_key.'.sql';
		$filename_sql2 = 'admin/backup/'.date('YmdHi',$my_time).'_'.$my_key.'_after_connect.sql';
		
		$f = fopen($GLOBALS['PRJ_DIR'].'/'.$filename_sql2, "a");
		fwrite($f, "/*!41000 SET NAMES 'cp1251' */;");
		fclose($f);
		set_time_limit(0);
		$GLOBALS['db']->backupDB($filename_sql);
		inc_lib('tools/CArchive.php');
		$test = new gzip_file($filename);
		$test->set_options(array('basedir' => $GLOBALS['PRJ_DIR'].'/', 'overwrite' => 1, 'level' => 5));
		$test->add_files(array("*.*"));
		//$test->exclude_files(array("admin/lib/templates_c/*.php", "*.gz"));
		$cfiles = 0;
		$sfiles = 0;
		
		foreach ($test->files as $key => $current) {
			if (stristr($current['name'], '.tar.gz')) {
				unset($test->files[$key]);
			} else {
				$sfiles += $current['stat'][7];
				$cfiles++;
			}
		}
		
		$test->create_archive();
		
		$text = '';
		$text .= 'Количество файлов: '.$cfiles;
		$text .= '<br>';
		$text .= 'Размер неупакованых файлов: '.CUtils::getSize($sfiles, 2);
		$text .= '<br>';
		$text .= 'Размер архива: '.CUtils::getFileSize('/'.$filename, 2);
		//$text = 'test';
		@unlink($GLOBALS['PRJ_DIR'].'/'.$filename_sql);
		@unlink($GLOBALS['PRJ_DIR'].'/'.$filename_sql2);
		$objResponse->assign("archive_info", 'innerHTML', $text);
		$objResponse->script("hideDiv('waiting');window.location.reload()");
		return $objResponse;
	}
	
	
	function delFile($name, $table_name, $record_id) {
		$objResponse = new xajaxResponse();
		$sql = "SELECT name,file FROM system_files WHERE name='$name'";
		$file = $GLOBALS['db']->getItem('delfile', $sql);
		if ($file) {
			@unlink($GLOBALS['PRJ_DIR'].$file['file']);
			$sql = "DELETE FROM system_files WHERE name='$name' AND table_name='".$table_name."' AND record_id=".$record_id;
			$GLOBALS['db']->execQuery('delfile', $sql);
		} else {
			$objResponse->alert("Ошибка удаления файла");
		}
		return $objResponse;
	}
	
	function updateFileList($table_name, $record_id) {
	global $THEME_REF;	
		$objResponse = new xajaxResponse();
		$sql = "SELECT * FROM system_files WHERE table_name='".$table_name."' AND record_id=".$record_id;
		$my_files = $GLOBALS['db']->getItems('filelist', $sql);
		$ret = '';
		$ret .= '<table width="100%" cellpadding="0" cellspacing="0" class="tfields">';
		$ret .= '<tr>';
    $ret .= '<th width="85%">Файл</th>';
		$ret .= '<th width="10%">Размер</th>';
		$ret .= '<th style="text-align:center;"><img alt="Действия" src="'.$THEME_REF.'/img/action_head.gif" border=0></th>';
    $ret .= '</tr>';
		foreach ($my_files as $fileitem) {
			$ret .= '<tr id="file_'.$fileitem['id'].'">';
		  $ret .= '<td><a href="'.$fileitem['file'].'">'.$fileitem['name'].'</a></td>';
			$ret .= '<td>'.$fileitem['filesize'].' байт</td>';
			$ret .= '<td><a href="#" onClick="delFile(\''.$fileitem['id'].'\',\''.$fileitem['name'].'\',\''.$table_name.'\',\''.$record_id.'\'); return false"><img src="'.$THEME_REF.'/img/icons/icon_delete.gif" border="0"></a></td>'."\n";
			$ret .= '</tr>';	
		}
		$ret .= '</table>';
		$objResponse->assign("filelist", 'innerHTML', $ret);
		return $objResponse;
	}
	
	function getPriceList($stuff_id) {
	global $THEME_REF;		
		$ret = '<table width="100%" cellpadding="0" cellspacing="0" class="tfields">';
		$ret .= '<tr>';
    	$ret .= '<th width="30%">Размер</th>';
		$ret .= '<th width="30%">Цвет</th>';
		$ret .= '<th width="30%">Цена</th>';
		$ret .= '<th width="5%">Порядок</th>';
		$ret .= '<th width="1%">Акт</th>';
		$ret .= '<th style="text-align:center;"><img alt="Действия" src="'.$THEME_REF.'/img/action_head.gif" border=0></th>';
    	$ret .= '</tr>';
				
		$sql = "SELECT p.id, s.name as size_id_name, c.name as color_id_name, p.price, p.ord, p.publish FROM catalog_prices p JOIN catalog_sizes s ON p.size_id=s.id JOIN catalog_color c ON p.color_id=c.id WHERE p.stuff_id=".$stuff_id." ORDER BY p.price";
		$items = $GLOBALS['db']->getItems('sizelist', $sql);
		foreach ($items as $sizeitem) {
			$ret .= '<tr id="price_'.$sizeitem['id'].'">';
			$ret .= '<td>'.$sizeitem['size_id_name'].'</td>';
			$ret .= '<td>'.$sizeitem['color_id_name'].'</td>';
			$ret .= '<td><input type="text" style="width:100%;text-align:right;" name="price_'.$sizeitem['id'].'" value="'.$sizeitem['price'].'" /></td>';
			$ret .= '<td><input type="text" style="width:100%" name="ord_'.$sizeitem['id'].'" value="'.$sizeitem['ord'].'" /></td>';
			$ret .= '<td><input type="checkbox" name="publish_'.$sizeitem['id'].'" value="on"'.($sizeitem['publish'] ? ' checked' : '').'></td>';
			$ret .= '<td><a href="#" onClick="delPrice('.$sizeitem['id'].'); return false"><img src="'.$THEME_REF.'/img/icons/icon_delete.gif" border="0"></a></td>'."\n";
			$ret .= '</tr>';	
		}
		$ret .= '</table>';
		return $ret;
	}
	
	function addPrice($fD) {
	
		$objResponse = new xajaxResponse();
		$sql = "INSERT INTO catalog_prices(stuff_id,size_id,color_id,price,ord,publish,credate) VALUES(".$fD['stuff_id'].",".$fD['size_id'].",".$fD['color_id'].",'".$fD['price']."','".$fD['ord']."','".(isset($fD['publish']) ? 'on' : '')."',NOW())";
		//$objResponse->alert($sql);
		$GLOBALS['db']->execQuery('addprice', $sql);
		$text = getPriceList($fD['stuff_id']);
		$objResponse->assign("sizelist", 'innerHTML', $text);
		$objResponse->script("hideDiv('waiting');");
		return $objResponse;
	}
	
	function delPrice($price_id) {
		$objResponse = new xajaxResponse();
		$sql = "SELECT id, stuff_id FROM catalog_prices WHERE id=".$price_id;
		$item = $GLOBALS['db']->execQuery('getprice', $sql);
		$sql = "DELETE FROM catalog_prices WHERE id=".$price_id;
		$GLOBALS['db']->execQuery('delprice', $sql);
		$text = getPriceList($item['stuff_id']);
		$objResponse->assign("sizelist", 'innerHTML', $text);
		$objResponse->script("hideDiv('waiting');");
		return $objResponse;
	}
	
	function updatePrices($fD){
		$objResponse = new xajaxResponse();
		$sql = "SELECT p.id, p.stuff_id FROM catalog_prices p JOIN catalog_sizes s ON p.size_id=s.id JOIN catalog_color c ON p.color_id=c.id WHERE p.stuff_id=".$fD['stuff_id']." ORDER BY p.price";
		$items = $GLOBALS['db']->getItems('sizelist', $sql);
		foreach ($items as $item) {
			$price = isset($fD['price_'.$item['id']]) ? $fD['price_'.$item['id']] : 0;
			$ord = isset($fD['ord_'.$item['id']]) ? $fD['ord_'.$item['id']] : 0;
			$publish = isset($fD['publish_'.$item['id']]) ? $fD['publish_'.$item['id']] : 0;
			$sql = "UPDATE catalog_prices SET price='$price', ord='$ord', publish='$publish' WHERE id=".$item['id'];
			$GLOBALS['db']->execQuery('updateprice', $sql);
		}
		$text = getPriceList($fD['stuff_id']);
		$objResponse->assign("sizelist", 'innerHTML', $text);
		$objResponse->script("hideDiv('waiting');");
		return $objResponse;
	}
	
	$xajax->processRequest();	

?>
