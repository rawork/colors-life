<?php

namespace AdminInterface\Action;
	
class EditAction extends Action {

	public $item;

	function __construct(&$adminController) {
		parent::__construct($adminController);
		$this->item = $this->dataTable->getItem($this->get('router')->getParam('id')); 
	}

	function getForm() {
		if ($this->get('util')->_postVar('id')) {
			if ($this->get('util')->_postVar('utype')) {
				$path = $_SERVER['HTTP_REFERER'];
				$_SESSION['message'] = ($this->dataTable->updateGlobals() ? 'Обновлено' : 'Ошибка обновления');
				header('location: '.$path);	
			} else {
				$this->messageAction($this->dataTable->updateGlobals() ? 'Обновлено' : 'Ошибка обновления');
			}
		}
		$ret = '';
		$entity = $this->item;
		if (count($entity)) {
			$svalues = explode(';', 'Строка|string;Текст|text;Булево|checkbox;Файл|file;Выбор|select');
			foreach ($svalues as $valueItem) {
				$types[] = explode('|', $valueItem);
			}
			$params = array(
				'entity' => $entity,
				'types' => $types
			);
			$template = 'admin/components/'.$this->get('router')->getParam('module').'.'.$this->get('router')->getParam('table').'.tpl';
			if ($text = $this->render($template, $params, true)) {
				return $ret.$text;
			} else {
				$ret .= '<form enctype="multipart/form-data" method="post" name="frmInsert" id="frmInsert" action="'.$this->fullRef.'/edit">';
				$ret .= '<input type="hidden" name="id" value="'.$entity['id'].'">';
				$ret .= '<input type="hidden" id="utype" name="utype" value="0">';
				$ret .= '<table class="table table-condensed">';
				$ret .= '<thead><tr>';
				$ret .= '<th>Редактирование</th>';
				$ret .= '<th>Запись: '.$entity['id'].'</th></tr></thead>';
				foreach ($this->dataTable->fields as $k => $v) {
					$ft = $this->dataTable->createFieldType($v, $entity);
					$ret .= '<tr><td align="left" width=150><strong>'.$v['title'].'</strong>'.$this->getHelpLink($v).$this->getTemplateName($v).'</td><td>';
					$ret .= !empty($v['readonly']) ? $ft->getStatic() : $ft->getInput();
					$ret .= '</td></tr>';
				}
				/* Реализация дополнительных параметров */
				/*if ($this->t->getDBTableName() == 'catalog_stuff' && $a['c_id'] != 0) {

					$features = $this->get('connection')->getItems('get_filters', 'SELECT id,name FROM catalog_features where id IN ('.$a['c_id_filters'].') order by name');
					foreach ($features as $feature) {
						$feature_variants = $this->get('connection')->getItems('get_feature_variants', "SELECT id,name FROM catalog_features_variants WHERE filter_id=".$feature['id']);
						$feature_value_item = $this->get('connection')->getItem('get_feature_value', 'SELECT * from catalog_features_values where stuff_id='.$a['id'].' AND feature_id='.$feature['id']); 
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
				$ret .= '</table>
<input type="button" class="btn btn-success" onClick="preSubmit(\'frmInsert\', 0)" value="Сохранить">
<input type="button" class="btn" onClick="preSubmit(\'frmInsert\', 1)" value="Применить">
<input type="button" class="btn" onClick="window.location = \''.$this->fullRef.'\'" value="Отменить"></form>';
			}
		}
		return $ret;
	}

	function getPricesForm() {
		$entity = $this->item;
		$sizes = $this->get('connection')->getItems('get_sizes', "SELECT id,name FROM catalog_sizes ORDER BY name");
		$colors = $this->get('connection')->getItems('get_colors', "SELECT id,name FROM catalog_color ORDER BY name");
		$sql = "SELECT p.id, s.name as size_id_name, c.name as color_id_name, p.price, p.ord, p.publish FROM catalog_prices p JOIN catalog_sizes s ON p.size_id=s.id JOIN catalog_color c ON p.color_id=c.id WHERE p.stuff_id=".$entity['id']." ORDER BY p.ord, p.price";
		$prices = $this->get('connection')->getItems('sizelist', $sql);
		$content = '';
		$content .= '<form method="post" name="frmUpdatePrice" id="frmUpdatePrice" action="">
<input type="hidden" name="stuff_id" value="'.$entity['id'].'" />
<div id="pricelist">
<table class="table table-condensed">
<thead><tr>
<th width="30%">Размер</th>
<th width="30%">Цвет</th>
<th width="30%">Цена</th>
<th width="5%">Порядок</th>
<th width="1%">Акт</th>
<th><i class="icon-align-justify"></i></th>
</tr></thead>';
		
		foreach ($prices as $priceitem) {
			$content .= '<tr id="price_'.$priceitem['id'].'">
<td>'.$priceitem['size_id_name'].'</td>
<td>'.$priceitem['color_id_name'].'</td>
<td><input type="text" class="input-mini right" name="price_'.$priceitem['id'].'" value="'.$priceitem['price'].'" /></td>
<td><input type="text" class="input-mini" name="ord_'.$priceitem['id'].'" value="'.$priceitem['ord'].'" /></td>
<td><input type="checkbox" name="publish_'.$priceitem['id'].'" value="on"'.($priceitem['publish'] ? ' checked' : '').'></td>
<td><a href="#" class="btn btn-small btn-danger" onClick="delPrice('.$priceitem['id'].'); return false"><i class="icon-trash icon-white"></i></a></td>
</tr>';	
		}
		$content .= '</table>
</div>
</form>
<div class="form-inline" id="control">
<a class="btn btn-small btn-success" title="Сохранить" onclick="updatePrices(\'UpdatePrice\')"><i class="icon-film icon-white"></i></a>
</div>
<br>
<form method="post" name="frmAddPrice" id="frmAddPrice" action="">
<input name="stuff_id" value="'.$entity['id'].'" type="hidden">
<table class="table table-condensed">
<thead><tr><td><strong>Добавить</strong></td><th></th></tr></thead>
<tr id="add_size_id"><td width="180"><b>Размер</b> <span class="sfnt">{size_id}</span></td>
<td><select name="size_id" style="width: 100%;"><option value="0">...</option>';
		foreach ($sizes as $size) {
				$content .= '<option value="'.$size['id'].'">'.$size['name'].'</option>';
		}
		$content .= '</select></td></tr>
<tr id="add_color_id"><td width="180"><strong>Цвет</strong> <span>{color_id}</span></td>
<td><select name="color_id"><option value="0">...</option>';
		foreach ($colors as $color) {
				$content .= '<option value="'.$color['id'].'">'.$color['name'].'</option>';		
		}
		$content .= '</select></td></tr>
<tr id="add_price"><td width="180"><strong>Цена</strong> <span>{price}</span></td><td><input name="price" style="text-align: right;" value="" type="text"></td></tr>
<tr id="add_ord"><td width="180"><strong>Порядок</strong> <span>{ord}</span></td><td><input name="ord" style="text-align: right;" value="" type="text"></td></tr>
<tr id="add_ord"><td width="180"><strong>Акт</strong> <span>{publish}</span></td><td><input type="checkbox" name="publish"></td></tr>
</table><input class="btn btn-success" onclick="addPrice(\'AddPrice\')" value="Добавить" type="button"></form>';

		return $content;
	}

	function getFilesForm() {
		$content = '';
		$a = $this->item;
		if (!empty($this->dataTable->params['multifile'])) {
			$content .= '<div id="filelist">
<table class="table table-condensed">
<thead><tr>
<th width="85%">Файл</th>
<th width="10%">Размер</th>
<th><i class="icon-align-justify"></i></th>
</tr></thead>';

			$sql = "SELECT * FROM system_files WHERE table_name='".$this->dataTable->getDBTableName()."' AND entity_id=".$a['id']." ORDER BY credate";
			$files = $this->get('connection')->getItems('filelist', $sql);
			foreach ($files as $fileitem) {
				$content .= '<tr id="file_'.$fileitem['id'].'">
<td><a href="'.$fileitem['file'].'">'.$fileitem['name'].'</a></td>
<td>'.$fileitem['filesize'].' байт</td>
<td><a href="javascript:void(0)" class="btn btn-small btn-danger" onClick="delFile('.$fileitem['id'].')"><i class="icon-trash icon-white"></i></a></td>
</tr>';	
			}
			$content .= '</table>
</div>
<input type="button" id="updatelistbtn" class="btn" onclick="updateFileList(\''.$this->dataTable->getDBTableName().'\','.$a['id'].');return false" value="Обновить список" />
<br><br><fieldset><legend>Добавить файл</legend>
<form id="uploadForm" action="/doajaxfileupload.php" method="post" enctype="multipart/form-data">
<input name="table_name" value="'.$this->dataTable->getDBTableName().'" type="hidden">
<input name="entity_id" value="'.$a['id'].'" type="hidden">
<input name="MAX_FILE_SIZE" value="1000000" type="hidden">
<input name="fileToUpload[]" id="fileToUpload" class="multi" type="file">
<br><input class="btn btn-success" value="Загрузить" type="submit">
</form>
</fieldset>
<div id="uploadOutput"></div>';
		}
		return $content;
	}

	function getText() {
		$links = array(
			array(
				'ref' => $this->fullRef,
				'name' => 'Список элементов'
			)
		);
		$content = $this->getOperationsBar($links);
		if ($this->get('templating')->exists('admin/'.$this->get('router')->getParam('module').'.'.$this->get('router')->getParam('table').'.edit.tpl')){
			$params = array (
				'updateForm' => $this->getForm(),
				'sizesForm' => $this->getPricesForm(),
				'filesForm' => $this->getFilesForm()
			);
			$content .= $this->render('admin/'.$this->get('router')->getParam('module').'.'.$this->get('router')->getParam('table').'.edit.tpl', $params);
		} else {
			$content .= $this->getForm();
		}
		return $content;
	}
}

