	var cng_num = 0;
	var checked_id = -1;
	var list_ids = '';
	var list_titles = '';
	var img_folder = new Image();
	var img_folder_settings = new Image();
	img_folder.src = theme_ref+'/img/icons/icon_folder.gif';
	img_folder_settings.src = theme_ref+'/img/icons/icon_folder_settings.gif';
	
	function setStateAll(checkitem){
		bState = checkitem.checked;
		$("input.cng").attr('checked', bState);
		cng_num = bState ? $("input.cng").length : 0;
	}
	
	function setStateOne(it){
		bState = it.checked;
		cng_num = bState ? cng_num+1 : cng_num-1;
	}
	
	function preSubmit(frm, utype) {
		if (utype)
			$('#utype').attr('value', utype);	
		$('#'+frm).submit();
	}
	
	function preFilter(type) {
		$('#filter_type').attr('value', type);
		$('#frmFilter').submit();
	}
	
	function controlEditor(it, itname) {
		if (it.checked)
			tinyMCE.execCommand('mceAddControl', false, itname);
		else
			tinyMCE.execCommand('mceRemoveControl', false, itname);
	}
	
	function showPopup() {
		setPos('popup_tree', 0, 0);
		$.dimScreen(500, 0.5, function() {$('#popup_tree').css('display', 'block')});
	}
	
	function hidePopup() {
		$('#popup_tree').css('display', 'none');
		$.dimScreenStop();
	}
	
	function marked_choice(check_id) {
		checked_id = check_id;
	}
	
	function make_tree_choice(input_id) {
        var check_item = $("#tree_val_"+checked_id);
		var found = false;
		if (check_item.attr('value') == checked_id) {
			found = true;
			title = $('#tree_title_'+checked_id).attr('innerHTML');
			$('#'+input_id).attr('value', checked_id);
			$('#'+input_id+'_title').attr('value', title);
			checked_id = -1;
			hidePopup();
		}
		if (!found) {
			alert("Выберите что нибудь");
		}
    }
	
	function make_list_choice(input_id) {
        $("input.check_list:checked").each(function (index, domEle) {
			id = $(domEle).attr("value"); 
			title = $('#list_title_'+id).attr('innerHTML');
		    list_ids = list_ids + (list_ids ? ',' : '') + id;
			list_titles = list_titles + (list_titles ? ', ' : '') + title;
      	});
		$('#'+input_id).attr('value', list_ids);
		$('#'+input_id+'_title').attr('value', list_titles);
		list_ids = '';
		list_titles = '';
		hidePopup();
    }
	
	function startGroupDelete(ref) {
		if (cng_num <= 0) {
			alert('Не выбраны элементы для удаления');
		} else {
			if (confirm('Уверены, что хотите удалить выделенные записи?')) {
				$('#frmGroupUpdate').attr('action', ref + '/groupdelete');
				$('#frmGroupUpdate').submit();
			} else {
				return false;
			}
		}	
	}
	
	function startDelete(ref) {
		if (confirm('Уверены, что хотите удалить запись?')) {
			window.location = ref;
		} else {
			return false;
		}
	}
	
	function startGroupUpdate(ref) {
		if (cng_num <= 0) {
			alert('Не выбраны элементы для редактирования');
		} else {
			$('#frmGroupUpdate').attr('action', ref + '/groupedit');
			$('#frmGroupUpdate').submit();
		}	
	}
	
	function chState(obj, name){
		$('#'+name+'_create').css('display', obj.checked ? 'block' : 'none');
		$('#'+name+'_temp').css('display', obj.checked ? 'block' : 'none');
		$('#'+name+'_load').css('display', obj.checked ? 'none' : 'block');
	}
	
	function templateState(obj, name){
		$('#'+name+'_delete').css('display', obj.selectedIndex ? 'none' : 'block');
		$('#'+name+'_temp').css('display', obj.selectedIndex ? 'none' : 'block');
		$('#'+name+'_load').css('display', obj.selectedIndex ? 'none' : 'block');
		$('#'+name+'_view').css('display', obj.selectedIndex ? 'inline' : 'none');
	}
	
	
/* ajax */	
	
	function getComponentList(currentState, moduleName) {
		state = currentState;
		showDiv('waiting', 0, -100);
		$.post("/adminajax/", {method: 'getComponentList', currentState: currentState, moduleName: moduleName},
		function(data){
			if (data.alertText) {
				alert(data.alertText);
			} else {
				$('#componentMenu').html(data.content);
			}
			hideDiv('waiting');
		}, "json");
	}
	
	function getTableList(currentState, moduleName) {
		obj = $('#tableMenu_'+moduleName);
		if (obj.html() == '') {
			showDiv('waiting', 0, -100);
			$.post("/adminajax/", {method: 'getTableList', currentState: currentState, moduleName: moduleName},
			function(data){
				if (data.alertText) {
					alert(data.alertText);
				} else {
					$('#tableMenu_'+moduleName).html(data.content);
					obj.css('display', 'block');
				}
				hideDiv('waiting');
			}, "json");
		} else if (obj.css('display') == 'none') {
			obj.css('display', 'block');
			hideDiv('waiting');
		} else {
			obj.css('display', 'none');
			hideDiv('waiting');
		}
	}
	
	function showTreePopup(inputId, table_name, field_name, dbid, zero_title, value){
		$('#popup_body').empty();
		$.post("/adminajax/", {method: 'showTreePopup', inputId: inputId, table_name: table_name, field_name: field_name, dbid: dbid, zero_title : zero_title, value: value},
		function(data){
			marked_choice(value);
			$('#popup_title').html(data.title);
			$('#popup_button').html(data.button);
			$('#popup_body').html(data.content);
			showPopup();
		}, "json");
	}
	
	function showListPopup(inputId, table_name, field_name, dbid, value){
		$("#popup_body").empty();
		$.post("/adminajax/", {method: 'showListPopup', inputId: inputId, table_name: table_name, field_name: field_name, dbid: dbid, value: value},
		function(data){
			$('#popup_title').html(data.title);
			$('#popup_button').html(data.button);
			$('#popup_body').html(data.content);
			showPopup();
		}, "json");
	}
	
	function showTemplateVersion(versionId) {
		obj = document.getElementById(versionId);
		if (obj.selectedIndex) {
			$.post("/adminajax/", {method: 'showTemplateVersion', versionId: obj.options[obj.selectedIndex].value},
			function(data){
				$('#popup_title').html(data.title);
				$('#popup_button').html(data.button);
				$('#popup_body').html(data.content);
				showPopup();
			}, "json");
		} else {
			alert('Не выбрана версия!');
		}
	}
	
	function showDuplicateSettings(ref) {
		$.post("/adminajax/", {method: 'showDuplicateSettings', ref: ref},
		function(data){
			$('#popup_title').html(data.title);
			$('#popup_button').html(data.button);
			$('#popup_body').html(data.content);
			showPopup();
		}, "json");
	}
	
	function goDuplicate(ref) {
		var quantity = parseInt($('#DuplicateQuantity').attr('value'));
		if (quantity && (quantity < 1 || quantity > 10)) {
			alert('Введите количество от 1 до 10!');	
		} else if (quantity) {
			hidePopup();
			window.location = ref + '?quantity=' + quantity;
		} else {
			alert('Введите число');	
		}
	}
	
	function editField(fieldId) {
		$.post("/adminajax/", {method: 'editField', fieldId: fieldId},
		function(data){
			$('#popup_title').html(data.title);
			$('#popup_button').html(data.button);
			$('#popup_body').html(data.content);
			showPopup();
		}, "json");
	}
	
	function makeArchive(formId) {
		showDiv('waiting', 0, -100);
		$("#archive_info").empty();
		$.post("/adminajax/", {method: 'makeArchive'},
		function(data){
			$("#archive_info").html(data.content);
			hideDiv('waiting');
			window.location.reload();
		}, "json");
	}
	
	function delFile(fileId, fileName, tableName, recordId) {
		$('#file_'+fileId).css('display', 'none');
		$.post("/adminajax/", {method: 'delFile', fileName: fileName, tableName: tableName, recordId: recordId},
		function(data){
			if (data.alertText) {
				alert(data.alertText);
			}
		}, "json");
	}
	
	function updateFileList(tableName, recordId) {
		$('#uploadOutput').empty();
		$.post("/adminajax/", {method: 'updateFileList', tableName: tableName, recordId: recordId},
		function(data){
			$('#filelist').html(data.content);
		}, "json");
	}
	
	function addPrice(formId) {
		fields = $('#frm'+formId).serialize();
		showDiv('waiting', 0, -100);
		$.post("/adminajax/", {method: 'addPrice', formdata: fields},
		function(data){
			$('#sizelist').html(data.content);
			hideDiv('waiting');
		}, "json");
	}
	
	function delPrice(priceId) {
		showDiv('waiting', 0, -100);
		$.post("/adminajax/", {method: 'delPrice', priceId: priceId},
		function(data){
			$('#price_'+priceId).remove();
			hideDiv('waiting');
		}, "json");
	}
	
	function updatePrices(formId) {
		fields = $('#frm'+formId).serialize();
		showDiv('waiting', 0, -100);
		$.post("/adminajax/", {method: 'updatePrices', formdata: fields},
		function(data){
			$('#sizelist').html(data.content);
			hideDiv('waiting');
		}, "json");
	}
	
	function updateRpp(sel, tableName) {
		showDiv('waiting', 0, -100);
		$.post("/adminajax/", {method: 'updateRpp', tableName: tableName, rpp: sel.options[sel.selectedIndex].value},
		function(data){
			hideDiv('waiting');
			location.reload();
		}, "json");
	}
	
/* end ajax */	

/* Calendar setup*/

	function setupCalendar(name, time) {
		Calendar.setup({
			inputField : name, 
			ifFormat : "%d.%m.%Y"+(time ? ' '+time : ''), 
			showsTime : time ? true : false, 
			button : "trigger_"+name, 
			align : "Br", 
			singleClick : true,
			timeFormat : 24,
			firstDay : 1
		});
	}
	
	function emptyDateSearch(nm){
		$('#'+nm+'_beg').attr('value', '');
		$('#'+nm+'_end').attr('value', '');
		return false;
	}
	
	function fileBrowser(type) {
		var connector = prj_ref+'/admin/editor/fmanager/fmanager.php?mlang=russian';
		var enableAutoTypeSelection = true;
		var cType;
		switch (type) {
			case 'image':
				cType = 'Image';
				break;
			case 'flash':
				cType = 'Flash';
				break;
			case 'file':
				cType = 'File';
				break;
		}
		if (enableAutoTypeSelection && cType) {
			connector += '&Type=' + cType;
		}
		open(connector, 'tinyfck', 'modal,width=750,height=465');
	}
	
	function fileBrowserCallBack(field_name, url, type, win) {
		var connector = prj_ref+'/admin/editor/fmanager/fmanager.php?mlang=russian';
		var enableAutoTypeSelection = true;
		var cType;
		tinyfck_field = field_name;
		tinyfck = win;
		
		switch (type) {
			case 'image':
				cType = 'Image';
				break;
			case 'flash':
				cType = 'Flash';
				break;
			case 'file':
				cType = 'File';
				break;
		}
		if (enableAutoTypeSelection && cType) {
			connector += '&Type=' + cType;
		}
		open(connector, 'tinyfck', 'modal,width=750,height=465');
	}
	
	function setFieldType(it){
		tname = it.options[it.selectedIndex].value;
		if (tname == 'enum' || tname == 'select' || tname == 'select_list' || tname == 'select_tree') {
			$('#add_select_values').css('display', 'table-row');
			$('#add_params').css('display', 'table-row');
		} else {
			$('#add_select_values').css('display', 'none');
			$('#add_params').css('display', 'none');
		}
	}
	
	function setPos(it, plusW, plusH) {
		if ($('#'+it).css('position') == 'absolute') {
			if (self.innerHeight) {
				x = self.innerWidth;
				y = self.innerHeight;
				// IE 6 Strict Mode
			} else if (document.documentElement && document.documentElement.clientHeight) {
				x = document.documentElement.clientWidth;
				y = document.documentElement.clientHeight;
				// Остальные версии IE
			} else if (document.body) {
				x = document.body.clientWidth;
				y = document.body.clientHeight;
			}
			var top = document.body.scrollTop;
			var left = document.body.scrollLeft;

			var width = $('#popup_tree').width();
			var height = $('#popup_tree').height();
	
			var halfX = x /2;
			var halfWidth = width / 2;
			var leftPad = (left + halfX) - halfWidth;

			var halfY = y /2;
			var halfHeight = height / 2;
			var topPad = (top + halfY) - halfHeight;
	    
			$('#'+it).css('left', leftPad+plusW);
			$('#'+it).css('top', topPad+plusH);
		}
	}
	
	function showDiv(it, w, h) {
		setPos(it, w, h);
		$('#'+it).css('display', 'block');
	}
	
	function hideDiv(it) {
		$('#'+it).css('display', 'none');
	}
	
	$(document).ready(function(){
		$('input.clPicker').colorPicker();
		/*$('a').click(function(){
			showDiv('waiting', 0, -100);
		});*/
		$('#popup_tree').html('<form> \
<table width="100%" border="0" cellspacing="0" cellpadding="2"> \
<tr><td height="21" class="popup_title" background="'+theme_ref+'/img/win_tbg.gif" style="background-repeat: repeat-x;"> \
<table width="100%" cellpadding="1" cellspacing="0" border="0"><tr> \
<td width="100%" id="popup_title"></td> \
<td><a href="javascript:hidePopup();"><img src="'+theme_ref+'/img/icons/icon_exit.gif" border="0" /></a></td></tr></table></td></tr> \
<tr><td><div id="popup_body"></div></td></tr> \
<tr><td align="right" id="popup_button"></td></tr> \
</table> \
</form> \
</div>');
		$('#popup_tree').draggable({handle:'td#popup_title', opacity: 0.75});
		
		
		$('.MultiFile').MultiFile({ 
			accept:'jpg|gif|png|rar|zip|pdf|flv|ppt|xls|doc', max:10, STRING: { 
				remove:'удалить',
				file:'$file', 
				selected:'Выбраны: $file', 
				denied:'Неверный тип файла: $ext!', 
				duplicate:'Этот файл уже выбран:\n$file!' 
			} 
		});		  
	  
		$("#loading").ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});
	  

		$('#uploadForm').ajaxForm({
			beforeSubmit: function(a,f,o) {
				o.dataType = "html";
				$('#uploadOutput').html('Отправка данных...');
			},
			success: function(data) {
				var $out = $('#uploadOutput');
				$out.html('');
				//$out.html('Form success handler received: <strong>' + typeof data + '</strong>');
				if (typeof data == 'object' && data.nodeType)
					data = elementToString(data.documentElement, true);
				else if (typeof data == 'object')
					data = objToString(data);
				$out.append('<div>'+ data +'</div>');
				$('#updatelistbtn').click();
			}
		});
	});
	
	function toggleSubNodes(it){
		state = $('#sitem'+it).attr('src');
		if (state == theme_ref+'/img/btnplus.gif') {
			$('.t'+it+"[rel='"+it+"']").css('display', document.attachEvent ? 'block' : 'table-row');
			$('#sitem'+it).attr('src', theme_ref+'/img/btnminus.gif');
		} else {
			$('.t'+it).css('display', 'none');
			$('#sitem'+it).attr('src', theme_ref+'/img/btnplus.gif');
		}
		return false;
	}