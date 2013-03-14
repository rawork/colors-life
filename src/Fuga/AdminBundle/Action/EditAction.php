<?php

namespace Fuga\AdminBundle\Action;
	
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
		$sql = "SELECT id,name FROM catalog_size ORDER BY name";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$sizes = $stmt->fetchAll();
		$sql = "SELECT id,name FROM catalog_color ORDER BY name";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$colors = $stmt->fetchAll();
		$sql = "SELECT p.id, s.name as size_id_name, c.name as color_id_name, p.price, p.sort, p.publish FROM catalog_price p JOIN catalog_size s ON p.size_id=s.id JOIN catalog_color c ON p.color_id=c.id WHERE p.product_id= :id ORDER BY p.sort, p.price";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->bindValue("id", $entity['id']);
		$stmt->execute();
		$prices = $stmt->fetchAll();
		$content = '';
		$content .= '<form method="post" name="frmUpdatePrice" id="frmUpdatePrice" action="">
<input type="hidden" name="product_id" value="'.$entity['id'].'" />
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
<td><input type="text" class="input-mini" name="sort_'.$priceitem['id'].'" value="'.$priceitem['sort'].'" /></td>
<td><input type="checkbox" name="publish_'.$priceitem['id'].'" value="on"'.($priceitem['publish'] ? ' checked' : '').'></td>
<td><a href="javascript:void(0)" class="btn btn-small btn-danger" onClick="delPrice('.$priceitem['id'].')"><i class="icon-trash icon-white"></i></a></td>
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
<input name="product_id" value="'.$entity['id'].'" type="hidden">
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
<tr id="add_sort"><td width="180"><strong>Порядок</strong> <span>{sort}</span></td><td><input name="sort" style="text-align: right;" value="" type="text"></td></tr>
<tr id="add_sort"><td width="180"><strong>Акт</strong> <span>{publish}</span></td><td><input type="checkbox" name="publish"></td></tr>
</table><input class="btn btn-success" onclick="addPrice(\'AddPrice\')" value="Добавить" type="button"></form>';

		return $content;
	}

	function getFilesForm() {
		$content = '';
		$entity = $this->item;
		if (!empty($this->dataTable->params['multifile'])) {
			$content .= '<div id="filelist">
<table class="table table-condensed">
<thead><tr>
<th width="85%">Файл</th>
<th width="10%">Размер</th>
<th><i class="icon-align-justify"></i></th>
</tr></thead>';

			$sql = "SELECT * FROM system_files WHERE table_name= :table AND entity_id= :id ORDER BY created";
			$stmt = $this->get('connection1')->prepare($sql);
			$stmt->bindValue("table", $this->dataTable->dbName());
			$stmt->bindValue("id", $entity['id']);
			$stmt->execute();
			$files = $stmt->fetchAll();
			foreach ($files as $fileitem) {
				$content .= '<tr id="file_'.$fileitem['id'].'">
<td><a href="'.$fileitem['file'].'">'.$fileitem['name'].'</a></td>
<td>'.$fileitem['filesize'].' байт</td>
<td><a href="javascript:void(0)" class="btn btn-small btn-danger" onClick="delFile('.$fileitem['id'].')"><i class="icon-trash icon-white"></i></a></td>
</tr>';	
			}
			$content .= '</table>
</div>
<input type="button" id="updatelistbtn" class="btn" onclick="updateFileList(\''.$this->dataTable->dbName().'\','.$entity['id'].');return false" value="Обновить список" />
<br><br><fieldset><legend>Добавить файл</legend>
<form id="uploadForm" action="/fileupload" method="post" enctype="multipart/form-data">
<input name="table_name" value="'.$this->dataTable->dbName().'" type="hidden">
<input name="entity_id" value="'.$entity['id'].'" type="hidden">
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

