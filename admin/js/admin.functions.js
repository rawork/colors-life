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
	
	function show_tree_popup(input_id, unit_name, table_name, field_name, dbid, zero_title){
		value = $('#'+input_id).attr('value');
		$('#popup_body').empty();
		xajax_show_tree_popup(input_id, unit_name, table_name, field_name, dbid, zero_title, value);
	}
	
	function show_list_popup(input_id, unit_name, table_name, field_name, dbid){
		values = $('#'+input_id).attr('value');
		$("#popup_body").empty();
		xajax_show_list_popup(input_id, unit_name, table_name, field_name, dbid, values);
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
				$('#frmGroupUpdate').attr('action', ref + '&action=group_delete');
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
			$('#frmGroupUpdate').attr('action', ref + '&action=s_group_update');
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
	
	
/* xajax */	
	
	try { if (undefined == xajax.config) xajax.config = {}; } catch (e) { xajax = {}; xajax.config = {}; };
	xajax.config.requestURI = prj_ref+"/admin/procajax.php";
	xajax.config.statusMessages = false;
	xajax.config.waitCursor = true;
	xajax.config.version = "xajax 0.5 rc1";
	xajax.config.legacy = false;
	xajax.config.defaultMode = "asynchronous";
	xajax.config.defaultMethod = "POST";
	
	xajax_show_tree_popup = function() {
		return xajax.request( { xjxfun: 'show_tree_popup' }, { parameters: arguments } );
	}

	xajax_show_list_popup = function() {
		return xajax.request( { xjxfun: 'show_list_popup' }, { parameters: arguments } );
	}
	
	xajax_editField = function(field_id) {
		return xajax.request( { xjxfun: 'editField'}, { parameters: arguments} );	
	}
	
	xajax_showTemplateVersion = function() {
		return xajax.request( { xjxfun: 'showTemplateVersion'}, { parameters: arguments} );	
	}
	
	xajax_showDuplicateSettings = function() {
		return xajax.request( { xjxfun: 'showDuplicateSettings'}, { parameters: arguments} );
	}
	
	function goDuplicate(ref) {
		var quantity = parseInt($('#DuplicateQuantity').attr('value'));
		if (quantity && (quantity < 1 || quantity > 10)) {
			alert('Введите количество от 1 до 10!');	
		} else if (quantity) {
			hidePopup();
			window.location = ref + '&quantity=' + quantity;
		} else {
			alert('Введите число');	
		}
	}
	
	function showTemplateVersion(ver_id) {
		obj = document.getElementById(ver_id);
		if (obj.selectedIndex) {
			xajax_showTemplateVersion(obj.options[obj.selectedIndex].value);
		} else {
			alert('Не выбрана версия!');
		}
	}
	
	xajax_getComponentList = function(st, un) {
    	return xajax.request( { xjxfun: 'getComponentList' }, { parameters: arguments } ); 
	}
	
	xajax_getTableList = function(component, st){
		return xajax.request( { xjxfun: 'getTableList' }, { parameters: arguments } ); 
	}
	
	function getComponentList(st, un) {
		state = st;
		showDiv('waiting', 0, -100);
		xajax_getComponentList(st, un);
	}
	
	xajax_makeArchive = function() {
    	return xajax.request( { xjxfun: 'makeArchive' }, { parameters: arguments } ); 
	}
	
	function makeArchive(fD) {
		showDiv('waiting', 0, -100);
		xajax_makeArchive(fD);
	}
	
	function delFile(file_id, file_name, class_id, rocord_id) {
		$('#file_'+file_id).css('display', 'none');
		xajax_delFile(file_name, class_id, rocord_id);
	}
	
	xajax_delFile = function() {
		return xajax.request( { xjxfun: 'delFile'}, { parameters: arguments} );	
	}
	
	function delPrice(price_id) {
		$('#price_'+price_id).css('display', 'none');
		xajax_delPrice(price_id);
	}
	
	xajax_delPrice = function() {
		showDiv('waiting', 0, -100);
		return xajax.request( { xjxfun: 'delPrice'}, { parameters: arguments} );	
	}
	
	function addPrice(fD) {
		xajax_addPrice(fD);
	}
	
	xajax_addPrice = function() {
		showDiv('waiting', 0, -100);
		return xajax.request( { xjxfun: 'addPrice'}, { parameters: arguments} );	
	}
	
	function updatePrices(fD) {
		showDiv('waiting', 0, -100);
		xajax_updatePrices(fD);
	}
	
	xajax_updatePrices = function() {
		return xajax.request( { xjxfun: 'updatePrices'}, { parameters: arguments} );	
	}
	
	xajax_updateFileList = function() {
		var out = $('#uploadOutput');
		out.empty();
		//$('#filelist').html('Обновление списка файлов...');
		return xajax.request( { xjxfun: 'updateFileList'}, { parameters: arguments} );	
	}
	
	function getTableList(component, st) {
		it = $('#tableMenu_'+component);
		if (it.attr('innerHTML') == '') {
			showDiv('waiting', 0, -100);
			xajax_getTableList(component, st);
		} else if (it.css('display') == 'none') {
			it.css('display', 'block');
			hideDiv('waiting');
		} else {
			it.css('display', 'none');
			hideDiv('waiting');
		}
	}
	
	
	
/* end xajax */	

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
		open(connector, 'tinyfck', 'modal,width=640,height=465');
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
		open(connector, 'tinyfck', 'modal,width=640,height=465');
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
	
	function updateRpp(sel) {
		ref = document.getElementById('ref');
		window.location = ref.value+'&rpp='+sel.options[sel.selectedIndex].value;
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
				$out.append('<div><pre>'+ data +'</pre></div>');
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