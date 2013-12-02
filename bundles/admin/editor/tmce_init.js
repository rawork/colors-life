//function initMCE () {
	tinyMCE.init({
		mode : 'textareas',
		theme : 'advanced',
		editor_selector : 'mceEditor',
		content_css : '',
		language : 'ru',
		extended_valid_elements : 'hr[class|width|size|noshade],iframe[src|width|height|name|align|style],div[id|class|align|style]',
		file_browser_callback : 'fileBrowserCallBack',
		forced_root_block : '',
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,inlinepopups,preview,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,undo,redo,|,link,unlink,anchor,image,cleanup,code,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,advhr,|,ltr,rtl,|,fullscreen",
		/*theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking",*/
		theme_advanced_toolbar_location : 'top',
		theme_advanced_toolbar_align : 'left',
		theme_advanced_statusbar_location : 'bottom',
		relative_urls : false,
		convert_urls : false,
		paste_use_dialog : false,
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal : false,
		height: '300',
		width: '100%',
		apply_source_formatting : true
	});
//}