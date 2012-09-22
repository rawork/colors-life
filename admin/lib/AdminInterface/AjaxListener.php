<?php

class AjaxListener {
	// Смена Меню компонентов при выборе группы функций
	function getComponentList($state, $unit = '') {
	global $smarty;
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
			return json_encode(array('alertText' => 'Сессия окончилась. Перезагрузите страницу'));
		}
		$smarty->assign('units', $units);
		$text = $smarty->fetch('admin/mainmenu.tpl');
		
		return json_encode(array('content' => $text));
	}
	
	// Показать Меню таблиц для модуля
	function getTableList($state, $unit) {
	global $smarty;
		inc_lib('AdminInterface/UnitAdminInterface.php');
		$_GET['state'] = $state;
		if (CUtils::_sessionVar('user')) {
			$ocomponent = $GLOBALS['rtti']->getComponent($unit);
			if (inc_u($unit)) {
				$className = ucfirst($unit).'Unit';
				$unit = new $className();
			} else {
				inc_lib('components/Unit.php');
				$unit = new Unit($unit);
			}
			$uai = new UnitAdminInterface($unit, '', array());
			$text = $uai->getTableMenu();

			return json_encode(array('content' => $text));
		} else {
			
			return json_encode(array('alertText' => 'Сессия окончилась. Перезагрузите страницу'));
		}
	}
	
	// Выбор из дерева разделов
	function showTreePopup($input_id, $table_name, $field_name, $dbid, $zero_title, $value) {
		$t = $GLOBALS['rtti']->getTable($table_name);
		$real_field_name = str_replace($dbid, '', $field_name);
		$real_field_name = str_replace('search_filter_', '', $real_field_name);
		$f = $t->fields[$real_field_name];
		$text = '<table class="tfields" align="center" border="0" width="95%" cellspacing="0" cellpadding="3">';
    	$text .= $this->tree_popup_showItem(0, $zero_title, 0, 18, $f, intval($value));
	    $text .= $this->tree_popup_show_subtree(0, 15, $f['l_table'] == $t->getDBTableName() ? $dbid : -1, $f, intval($value));
		$text .= '</table>';
		
		return json_encode( array(
			'title' => $f['title'], 
			'button' => '<input type="button" class="adm-btn" value="Выбрать" onclick="make_tree_choice('."'".$input_id."'".')">',
			'content' => $text
		));
	}
	
	// Множественный выбор
	function showListPopup($input_id, $table_name, $field_name, $dbid, $value) {
		$values = !empty($value) ? explode(',', $value) : array();
		$t = $GLOBALS['rtti']->getTable($table_name);
		$f = $t->fields[$field_name];
		$text = '<table class="tfields" align="center" border="0" width="95%" cellspacing="1" cellpadding="2">';
		$text .= $this->list_popup_show($f, $values);
		$text .= '</table>';
		
		return json_encode( array(
			'title' => $f['title'], 
			'button' => '<input type="button" class="adm-btn" value="Выбрать" onclick="make_list_choice('."'".$input_id."'".')">',
			'content' => $text
		));
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
	
    function tree_popup_show_subtree($p_id, $prefix_width, $exclude, $f, $value) {
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
                $ret .= $this->tree_popup_showItem($a['id'], $vname.' ['.$a['id'].']', $prefix_width, $prefix_width + 18, $f, $value);
                $ret .= $this->tree_popup_show_subtree($a['id'], $prefix_width + 15, $exclude, $f, $value);
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
            $ret .= $this->list_popup_showItem($a['id'], $vname.' ['.$a['id'].']', $f, $values);
        }
		return $ret;
    }
	
	// Окно с версиями шаблона
	function showTemplateVersion($versionId) {
		global $PRJ_DIR;
		$version = $GLOBALS['rtti']->getItem('templates_version', $versionId);
		$text = @file_get_contents($PRJ_DIR.$version['file']);
		return json_encode( array(
			'title' => 'Версия шаблона', 
			'button' => '<input type="button" class="adm-btn" value="Закрыть" onclick="hidePopup()">',
			'content' => '<textarea wrap="off" name="mytemplatetemp" readonly style="height:99%; width:100%" rows="15" cols="45">'.htmlspecialchars($text).'</textarea>'
		));
	}
	
	function showDuplicateSettings($ref) {
		return json_encode( array(
			'title' => 'Копирование записи', 
			'button' => '<input type="button" class="adm-btn" value="Копировать" onclick="goDuplicate(\''.$ref.'\')">',
			'content' => 'Количество новых <br /><input name="DuplicateQuantity" id="DuplicateQuantity" value="1" />'
		));
	}
	
	// старая новая разработка - неживое
	function editField($fieldId, $formdata) {
		if (count($formdata)) {
			return json_encode( array(
				'title' => 'Редактирование поля: '.$field['title'], 
				'button' => '<input type="button" class="adm-btn" value="Сохранить" onclick="updateField()">&nbsp;<input type="button" class="adm-btn" value="Закрыть" onclick="hidePopup()">',
				'content' => '<textarea wrap="off" name="mytemplatetemp" readonly style="height:99%; width:100%" rows="15" cols="45">'.htmlspecialchars($text).'</textarea>'
			));
		} else {
			return json_encode(array('alertText' => 'Все плохо :)'));
		}
	}
	
	function makeArchive() {
		$my_time = time();
		$my_key = CUtils::genKey(8);
		
		$filename = 'admin/backup/'.date('YmdHi',$my_time).'_'.$my_key.'.tar.gz';
		$filename_sql = 'admin/backup/'.date('YmdHi',$my_time).'_'.$my_key.'.sql';
		$filename_sql2 = 'admin/backup/'.date('YmdHi',$my_time).'_'.$my_key.'_after_connect.sql';
		
		$f = fopen($GLOBALS['PRJ_DIR'].'/'.$filename_sql2, "a");
		fwrite($f, "/*!41000 SET NAMES 'utf8' */;");
		fclose($f);
		set_time_limit(0);
		$GLOBALS['db']->backupDB($filename_sql);
		inc_lib('tools/CArchive.php');
		$test = new gzip_file($filename);
		$test->set_options(array('basedir' => $GLOBALS['PRJ_DIR'].'/', 'overwrite' => 1, 'level' => 5));
		$test->add_files(array("*.*"));
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

		return json_encode(array('content' => $text));
	}
	
	
	function delFile($name, $tableName, $recordId) {
		$sql = "SELECT name,file FROM system_files WHERE name='$name'";
		$file = $GLOBALS['db']->getItem('delfile', $sql);
		if ($file) {
			@unlink($GLOBALS['PRJ_DIR'].$file['file']);
			$sql = "DELETE FROM system_files WHERE name='$name' AND table_name='".$tableName."' AND record_id=".$recordId;
			$GLOBALS['db']->execQuery('delfile', $sql);
			return json_encode(array('status' => 'ok'));
		} else {
			return json_encode(array('alertText' => 'Ошибка удаления файла'));
		}
	}
	
	function updateFileList($table_name, $record_id) {
		global $THEME_REF;	
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
		return json_encode(array('content' => $ret));
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
	
	function addPrice($formdata) {
		parse_str($formdata);
		$sql = "INSERT INTO catalog_prices(stuff_id,size_id,color_id,price,ord,publish,credate) VALUES(".$stuff_id.",".$size_id.",".$color_id.",'".$price."','".$ord."','".(isset($publish) ? 'on' : '')."',NOW())";
		$GLOBALS['db']->execQuery('addprice', $sql);
		$text = $this->getPriceList($stuff_id);
		
		return json_encode(array('content' => $text));
	}
	
	function delPrice($priceId) {
		$sql = "DELETE FROM catalog_prices WHERE id=".$priceId;
		$GLOBALS['db']->execQuery('delprice', $sql);
		
		return json_encode(array('status' => 'ok'));
	}
	
	function updatePrices($formdata){
		parse_str($formdata);
		$sql = "SELECT p.id, p.stuff_id FROM catalog_prices p JOIN catalog_sizes s ON p.size_id=s.id JOIN catalog_color c ON p.color_id=c.id WHERE p.stuff_id=".$stuff_id." ORDER BY p.price";
		$items = $GLOBALS['db']->getItems('sizelist', $sql);
		foreach ($items as $item) {
			$priceName = 'price_'.$item['id'];
			$ordName = 'ord_'.$item['id'];
			$publishName = 'publish_'.$item['id'];
			$price = isset($$priceName) ? $$priceName : 0;
			$ord = isset($$ordName) ? $$ordName : 0;
			$publish = isset($$publishName) ? $$publishName : '';
			$sql = "UPDATE catalog_prices SET price='$price', ord='$ord', publish='$publish' WHERE id=".$item['id'];
			$GLOBALS['db']->execQuery('updateprice', $sql);
		}
		$text = $this->getPriceList($stuff_id);
		
		return json_encode(array('content' => $text));
	}
	
}