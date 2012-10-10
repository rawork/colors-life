var imgFolder = new Image();
var imgFolderSetting = new Image();
imgFolder.src = theme_ref + '/img/icons/icon_folder.gif';
imgFolderSetting.src = theme_ref + '/img/icons/icon_folder_settings.gif';

function setLanguage(language) {
	$('#language').attr('value', language);
	$('$formLang').submit();
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

function startGroupDelete() {
	elements = $(".list-checker:checked");
	if (elements.length <= 0) {
		alert('Не выбраны элементы для удаления');
	} else {
		if (confirm('Уверены, что хотите удалить выделенные записи?')) {
			ids = new Array();
			elements.each(function (index, domElement) {
				id = $(domElement).attr('value'); 
				ids.push(id);
			});
			$('#ids').attr('value', ids.join());
			$('#frmGroupUpdate').attr('action', location.href + '/groupdelete');
			$('#frmGroupUpdate').submit();
		} else {
			return false;
		}
	}	
}

function startDelete(id) {
	if (confirm('Уверены, что хотите удалить запись?')) {
		window.location = location.href + '/delete/' +id;
	} else {
		return false;
	}
}

function startGroupUpdate(checkUpdate) {
	elements = $(".list-checker:checked");
	if (checkUpdate == true) {
		if (elements.length <= 0) {
			alert('Не выбраны элементы для редактирования');
		} else {
			ids = new Array();
			elements.each(function (index, domElement) {
				id = $(domElement).attr('value'); 
				ids.push(id);
			});
			$('input[name="edited"]').attr('value', 0);
			$('#ids').attr('value', ids.join());
			$('#frmGroupUpdate').submit();
		}
	} else {
		$('#frmGroupUpdate').submit();
	}	
}


function controlEditor(it, itname) {
	if (it.checked)
		tinyMCE.execCommand('mceAddControl', false, itname);
	else
		tinyMCE.execCommand('mceRemoveControl', false, itname);
}

function showPopup() {
	$('#modalDialog').modal('show');
}

function hidePopup() {
	$('#modalDialog').modal('hide');
}

function makePopupChoice(inputId) {
	$('#'+inputId).attr('value', $('#popupChoiceId').attr('value'));
	$('#'+inputId+'_title').attr('value', $('#popupChoiceTitle').html());
	hidePopup();
}

function makeListChoice(input_id) {
	ids = new Array();
	titles = new Array(); 
	$("input.popup-item:checked").each(function (index, domElement) {
		id = $(domElement).attr('value'); 
		title = $('#itemTitle' + id).html();
		ids.push(id);
		titles.push(title);
	});
	$('#'+input_id).attr('value', ids.join());
	$('#'+input_id+'_title').attr('value', titles.join(', '));
	hidePopup();
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
				obj.html(data.content);
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

function showSelectPopup(inputId, tableName, fieldName, dbId, title){
	$.post("/adminajax/", {method: 'showSelectPopup', inputId: inputId, table_name: tableName, field_name: fieldName, dbid: dbId, title : title},
	function(data){
		$('#popupTitle').html(data.title);
		$('#popupButtons').html(data.button);
		$('#popupContent').html(data.content);
		$('.popup-item').on("click", function(event){
			$('#popupChoiceId').attr('value', $(this).attr('rel'));
			$('#popupChoiceTitle').html($(this).html());
		});	
		showPopup();
	}, "json");
}

function emptySelect(inputId) {
	$('#'+inputId).attr('value', 0);
	$('#'+inputId+'_title').attr('value', 'Не выбрано');
}

function showPage(divId, tableName, fieldName, entityId, page) {
	$.post("/adminajax/", {method: 'showPage', divId: divId, tableName: tableName, fieldName: fieldName, entityId: entityId, page: page},
	function(data){
		$('#'+divId).html(data.content);
		$('.popup-item').on("click", function(event){
			$('#popupChoiceId').attr('value', $(this).attr('rel'));
			$('#popupChoiceTitle').html($(this).html());
		});
	}, "json");
}

function showTreePopup(inputId, tableName, fieldName, dbId, title){
	checkedId = dbId;
	$.post("/adminajax/", {method: 'showTreePopup', inputId: inputId, table_name: tableName, field_name: fieldName, dbid: dbId, title : title},
	function(data){
		$('#popupTitle').html(data.title);
		$('#popupButtons').html(data.button);
		$('#popupContent').html(data.content);
		$("#navigation").treeview({
			persist: "location",
			collapsed: true,
			unique: true
		});
		$('.popup-item').on("click", function(event){
			$('#popupChoiceId').attr('value', $(this).attr('rel'));
			$('#popupChoiceTitle').html($(this).html());
		});
		showPopup();
	}, "json");
}

function showListPopup(inputId, table_name, field_name, value){
	$.post("/adminajax/", {method: 'showListPopup', inputId: inputId, table_name: table_name, field_name: field_name, value: value},
	function(data){
		$('#popupTitle').html(data.title);
		$('#popupButtons').html(data.button);
		$('#popupContent').html(data.content);
		showPopup();
	}, "json");
}

function showTemplateVersion(versionId) {
	obj = document.getElementById(versionId);
	if (obj.selectedIndex) {
		$.post("/adminajax/", {method: 'showTemplateVersion', versionId: obj.options[obj.selectedIndex].value},
		function(data){
			$('#popupTitle').html(data.title);
			$('#popupButtons').html(data.button);
			$('#popupBody').html(data.content);
			showPopup();
		}, "json");
	} else {
		alert('Не выбрана версия!');
	}
}

function showCopyDialog(id) {
	$.post("/adminajax/", {method: 'showCopyDialog', id: id},
	function(data){
		$('#popupTitle').html(data.title);
		$('#popupButtons').html(data.button);
		$('#popupContent').html(data.content);
		showPopup();
	}, "json");
}

function goCopy(ref) {
	var quantity = parseInt($('#copyQuantity').attr('value'));
	if (quantity && (quantity < 1 || quantity > 10)) {
		$('#copyInput').addClass('error');
		$('#copyHelp').html('Введите число от 1 до 10');	
	} else if (quantity) {
		hidePopup();
		window.location = location.href + ref + '?quantity=' + quantity;
	} else {
		$('#copyInput').addClass('error');
		$('#copyHelp').html('Введите число от 1 до 10');	
	}
}

function editField(fieldId) {
	$.post("/adminajax/", {method: 'editField', fieldId: fieldId},
	function(data){
		$('#popupTitle').html(data.title);
		$('#popupButtons').html(data.button);
		$('#popupContent').html(data.content);
		showPopup();
	}, "json");
}

function makeArchive(formId) {
	showDiv('waiting', 0, -100);
	$("#archive_info").css('display', 'none');
	$("#archive_info").empty();
	$.post("/adminajax/", {method: 'makeArchive'},
	function(data){
		$("#archive_info").html(data.content);
		hideDiv('waiting');
		window.location.reload();
	}, "json");
}

function delFile(fileId) {
	$('#file_'+fileId).css('display', 'none');
	$.post("/adminajax/", {method: 'delFile', fileId: fileId},
	function(data){
		if (data.alertText) {
			alert(data.alertText);
		}
	}, "json");
}

function updateFileList(tableName, recordId) {
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
		$('#pricelist').html(data.content);
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
		$('#pricelist').html(data.content);
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

function setupCalendar() {
	for (var name in calendars) {
		time = (calendars[name] ? ' ' + calendars[name] : '')
		Calendar.setup({
			inputField : name, 
			ifFormat : "%d.%m.%Y" + time, 
			showsTime : time ? true : false, 
			button : "trigger_" + name, 
			align : "Br", 
			singleClick : true,
			timeFormat : 24,
			firstDay : 1
		});
	}
}

function emptyDateSearch(name){
	$('#'+name+'_beg').attr('value', '');
	$('#'+name+'_end').attr('value', '');
	return false;
}

function fileBrowser(type) {
	var connector = prj_ref+'/bundles/admin/editor/fmanager/fmanager.php?mlang=russian';
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
	var connector = prj_ref+'/bundles/admin/editor/fmanager/fmanager.php?mlang=russian';
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

function showDiv(it) {
	$('#'+it).css('display', 'block');
}

function hideDiv(it) {
	$('#'+it).css('display', 'none');
}

$(document).ready(function(){
	$('input.clPicker').colorPicker();
	
	$('#myTab a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	})
	
	$('.multi').MultiFile({ 
		accept:'jpg|gif|png|rar|zip|pdf|flv|ppt|xls|doc', 
		max:10, 
		remove:'удалить',
		file:'$file', 
		selected:'Выбраны: $file', 
		denied:'Неверный тип файла: $ext!', 
		duplicate:'Этот файл уже выбран:\n$file!' 
	});		  

	$("#waiting").ajaxStart(function(){
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
			var out = $('#uploadOutput');
			out.html('');
			if (typeof data == 'object' && data.nodeType)
				data = elementToString(data.documentElement, true);
			else if (typeof data == 'object')
				data = objToString(data);
			out.append('<div>'+ data +'</div>');
			$('a.MultiFile-remove').click();
			$('#updatelistbtn').click();
		}
	});
	
	setupCalendar();
	
	$('#list-checker').on('click', function (event) {
		if ($(this).attr('checked')) {
			$(".list-checker").attr('checked', 'checked');
		} else {
			$(".list-checker").removeAttr('checked');
		}
	});
	
});

