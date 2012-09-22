<?php

  inc_lib('AdminInterface/actions/UnitAdminBody.php');
	
  class s_updateUnitAdminBody extends UnitAdminBody {
    
		public $item;
		
		function __construct(&$unitAdminInterface) {
      parent::__construct($unitAdminInterface);
			$this->item = $this->t->getItem(CUtils::_getVar('id', true, 0)); 
    }
		
		function getUpdateForm() {
		global $PRJ_DIR, $THEME_REF, $smarty;		
			$ret = '';
			$a = $this->item;
			if (count($a)) {
			  if (file_exists($PRJ_DIR.'/templates/admin/components/'.CUtils::_getVar('unit').'.'.CUtils::_getVar('table').'.tpl')){
				  $smarty->assign('a', $a);
				  $svalues = explode(';', 'Строка|string;Текст|text;Булево|checkbox;Файл|file;Выбор|select');
        	foreach ($svalues as $a) {
					  $types[] = explode('|', $a);
	        }
				  $smarty->assign('types', $types);
				  return $ret.$smarty->fetch('admin/components/'.CUtils::_getVar('unit').'.'.CUtils::_getVar('table').'.tpl');
			  } else {
				  $ret .= '<form enctype="multipart/form-data" method="post" name="frmInsert" id="frmInsert" action="'.$this->fullRef.'&action=update">';
				  $ret .= '<input type="hidden" name="id" value="'.$a['id'].'">';
				  $ret .= '<input type="hidden" id="utype" name="utype" value="0">';
				  $ret .= $this->getTableHeader();
				  $ret .= '<tr>';
				  $ret .= '<th>Редактирование</th>';
				  $ret .= '<th>Запись: '.$a['id'].'</th></tr>';
				  foreach ($this->t->fields as $k => $v) {
            $ft = $this->t->createFieldType($v, $a);
            $ret .= '<tr><td align="left" width=150><strong>'.$v['title'].'</strong>'.$this->getHelpLink($k, $v).$this->getTemplateName($v).'</td><td>';
            $ret .= !empty($v['readonly']) ? $ft->getStatic() : $ft->getInput();
            $ret .= '</td></tr>';
          }
				  /* Реализация дополнительных параметров */
				  /*if ($this->t->getDBTableName() == 'catalog_stuff' && $a['c_id'] != 0) {
					
					  $features = $GLOBALS['db']->getItems('get_filters', 'SELECT id,name FROM catalog_features where id IN ('.$a['c_id_filters'].') order by name');
					  foreach ($features as $feature) {
						  $feature_variants = $GLOBALS['db']->getItems('get_feature_variants', "SELECT id,name FROM catalog_features_variants WHERE filter_id=".$feature['id']);
						  $feature_value_item = $GLOBALS['db']->getItem('get_feature_value', 'SELECT * from catalog_features_values where stuff_id='.$a['id'].' AND feature_id='.$feature['id']); 
						  $ret .= '<tr><td width="150" align=left>'.$feature['name'].'</td><td>';
							$ret .= '<select name="filter_'.$feature['id'].'">';
						  $ret .= '<option value="0">Выберите...</option>';
						  foreach ($feature_variants as $feature_variant) {
							  $sel = '';
							  if ($feature_value_item['feature_value_id'] == $feature_variant['id']) {
								  $sel = ' selected';
							  }
							  $ret .= '<option value="'.$feature_variant['id'].'"'.$sel.'>'.$feature_variant['name'].'</option>';
						  }
						  $ret .= '</select>';
						  $ret .= '</td></tr>'."\n";
					  }
				  }*/
          $ret .= '</table><div class="ctlbtns"><input type="button" class="adm-btn" onClick="preSubmit(\'frmInsert\', 1)" value="Применить"><input type="button" class="adm-btn" onClick="preSubmit(\'frmInsert\', 0)" value="Сохранить"><input type="button" class="adm-btn" onClick="window.location = \''.(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $this->fullRef).'\'" value="Отменить"></div></form>';
        }
			}
			return $ret;
		}
		
		function getSizesForm() {
		global $PRJ_DIR, $THEME_REF, $smarty;		
			$ret = '';
			$a = $this->item;
			$ret .= '<form method="post" name="frmUpdatePrice" id="frmUpdatePrice" action="">';
			$ret .= '<input type="hidden" name="stuff_id" value="'.$a['id'].'" />';
				$ret .= '<div id="sizelist">';
				$ret .= '<table width="100%" cellpadding="0" cellspacing="0" class="tfields">';
			  $ret .= '<tr>';
        $ret .= '<th width="30%">Размер</th>';
				$ret .= '<th width="30%">Цвет</th>';
				$ret .= '<th width="30%">Цена</th>';
				$ret .= '<th width="5%">Порядок</th>';
				$ret .= '<th width="1%">Акт</th>';
			  $ret .= '<th style="text-align:center;"><img alt="Действия" src="'.$THEME_REF.'/img/action_head.gif" border=0></th>';
        $ret .= '</tr>';
				
				$sql = "SELECT p.id, s.name as size_id_name, c.name as color_id_name, p.price, p.ord, p.publish FROM catalog_prices p JOIN catalog_sizes s ON p.size_id=s.id JOIN catalog_color c ON p.color_id=c.id WHERE p.stuff_id=".$a['id']." ORDER BY p.ord, p.price";
				$sizes = $GLOBALS['db']->getItems('sizelist', $sql);
				foreach ($sizes as $sizeitem) {
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
				$ret .= '</div></form>';
				$ret .= '<div class="ctlbtns"><table class="contextmenu3" cellspacing="0" cellpadding="3" border="0"><tr>';
				$ret .= '<td><a onclick="updatePrices(\'UpdatePrice\')" class="context-button" title="Сохранить"><img border="0" src="'.$THEME_REF.'/img/icons/icon_save.gif"></a></td>';
      			$ret .= '</tr></table></div>';
				$sizes = $GLOBALS['db']->getItems('get_sizes', "SELECT id,name FROM catalog_sizes ORDER BY name");
				$colors = $GLOBALS['db']->getItems('get_colors', "SELECT id,name FROM catalog_color ORDER BY name");
				$ret .= '<br><br>';
				$ret .= '<form method="post" name="frmAddPrice" id="frmAddPrice" action="">';
				$ret .= '<input name="stuff_id" value="'.$a['id'].'" type="hidden">';
				$ret .= '<table class="tfields" cellpadding="0" cellspacing="0" width="100%">';
				$ret .= '<tr><td><b>Добавить</b><a name="add"></a><br><img src="/admin/themes/_default/img/0.gif" height="1" width="150"></td><td><div class="empty"></div></td></tr>';

				$ret .= '<tr id="add_size_id" bgcolor="#fafafa"><td class="left" align="left" width="180"><b>Размер</b> <span class="sfnt">{size_id}</span></td>';
				$ret .= '<td class="right"><select name="size_id" style="width: 100%;"><option value="0">...</option>';
				foreach ($sizes as $size) {
						$ret .= '<option value="'.$size['id'].'">'.$size['name'].'</option>';
				}
				$ret .= '</select></td></tr>';
				$ret .= '<tr id="add_color_id" bgcolor="#fafafa"><td class="left" align="left" width="180"><b>Цвет</b> <span class="sfnt">{color_id}</span></td>';
				$ret .= '<td class="right"><select name="color_id" style="width: 100%;"><option value="0">...</option>';
				foreach ($colors as $color) {
						$ret .= '<option value="'.$color['id'].'">'.$color['name'].'</option>';		
				}
				$ret .= '</select></td></tr>';
				$ret .= '<tr id="add_price" bgcolor="#fafafa"><td class="left" align="left" width="180"><b>Цена</b> <span class="sfnt">{price}</span></td><td class="right"><input name="price" style="text-align: right;" value="" size="20" type="text"></td></tr>';
				$ret .= '<tr id="add_ord" bgcolor="#fafafa"><td class="left" align="left" width="180"><b>Порядок</b> <span class="sfnt">{ord}</span></td><td class="right"><input name="ord" style="text-align: right;" value="" size="10" type="text"></td></tr>';
				$ret .= '<tr id="add_ord" bgcolor="#fafafa"><td class="left" align="left" width="180"><b>Акт</b> <span class="sfnt">{publish}</span></td><td class="right"><input type="checkbox" name="publish"></td></tr>';
				$ret .= '</table><div class="ctlbtns"><input class="adm-btn" onclick="addPrice(\'AddPrice\')" value="Добавить" type="button"></div></form>';

			return $ret;
		}
		
		function getFilesForm() {
		global $PRJ_DIR, $THEME_REF, $smarty;		
			$ret = '';
			$a = $this->item;
			if (!empty($this->t->props['multifile'])) {
				//$ret .= "\n".'<div class="module-title">Дополнительные файлы</div>'."\n";
				$ret .= '<div id="filelist">';
				$ret .= '<table width="100%" cellpadding="0" cellspacing="0" class="tfields">';
			  $ret .= '<tr>';
			  //$ret .= '<th width="1%" style="text-align:center;"><input type="checkbox" name="stateall" onClick="setStateAll(this);"></th>';
			  //$ret .= '<th width="1%">ID</th>';
        $ret .= '<th width="85%">Файл</th>';
				$ret .= '<th width="10%">Размер</th>';
			  $ret .= '<th style="text-align:center;"><img alt="Действия" src="'.$THEME_REF.'/img/action_head.gif" border=0></th>';
        $ret .= '</tr>';
				
				$sql = "SELECT * FROM system_files WHERE table_name='".$this->t->getDBTableName()."' AND record_id=".$a['id']." ORDER BY credate";
				$my_files = $GLOBALS['db']->getItems('filelist', $sql);
				foreach ($my_files as $fileitem) {
					$ret .= '<tr id="file_'.$fileitem['id'].'">';
					//$ret .= '<td width="1%"><input type="checkbox" class="cng" onClick="setStateOne(this)" name="cng'.$a['id'].'" value="'.$a['id'].'"></td>';
					//$ret .= '<td>'.$fileitem['id'].'</td>';
				  $ret .= '<td><a href="'.$fileitem['file'].'">'.$fileitem['name'].'</a></td>';
					$ret .= '<td>'.$fileitem['filesize'].' байт</td>';
					$ret .= '<td><a href="#" onClick="delFile(\''.$fileitem['id'].'\',\''.$fileitem['name'].'\',\''.$this->t->getDBTableName().'\',\''.$a['id'].'\'); return false"><img src="'.$THEME_REF.'/img/icons/icon_delete.gif" border="0"></a></td>'."\n";
					$ret .= '</tr>';	
				}
				$ret .= '</table>';
				$ret .= '</div>';
				$ret .= '<input type="button" class="adm-btn" onclick="updateFileList(\''.$this->t->getDBTableName().'\','.$a['id'].');return false" value="Обновить список" />'."\n";
				$ret .= '<br><br><fieldset><legend>Добавить файл</legend>';
				$ret .= '<form id="uploadForm" action="doajaxfileupload.php" method="post" enctype="multipart/form-data">'."\n";
				$ret .= '<input name="table_name" value="'.$this->t->getDBTableName().'" type="hidden"/>'."\n";
				$ret .= '<input name="record_id" value="'.$a['id'].'" type="hidden"/>'."\n";
				$ret .= '<input name="MAX_FILE_SIZE" value="1000000" type="hidden"/>'."\n";
				$ret .= '<input name="fileToUpload[]" id="fileToUpload" class="MultiFile" type="file"/>'."\n";
				$ret .= '<br><input class="adm-btn" value="Загрузить" type="submit"/>'."\n";
				$ret .= '</form>'."\n";
				$ret .= "</fieldset>";
				$ret .= '<img id="loading" src="'.$THEME_REF.'/img/loading.gif" style="display:none;"/>'."\n";   
			  $ret .= '<div id="uploadOutput"></div>'."\n";
			}
			return $ret;
		}
		
    function getText() {
		global $PRJ_DIR, $THEME_REF, $smarty;
			$links = array(
				array(
					'ref' => $this->fullRef,
					'name' => 'Список элементов'
				)
			);
			$ret = $this->getOperationsBar($links);
			if (file_exists($PRJ_DIR.'/templates/admin/'.CUtils::_getVar('unit').'.'.CUtils::_getVar('table').'.edit.tpl')){
				$smarty->assign('update_form', $this->getUpdateForm());
				$smarty->assign('sizes_form', $this->getSizesForm());
				$smarty->assign('files_form', $this->getFilesForm());
				$ret .= $smarty->fetch('admin/'.CUtils::_getVar('unit').'.'.CUtils::_getVar('table').'.edit.tpl');
			} else {
				$ret .= $this->getUpdateForm();
			}
		  return $ret;
		}
  }

