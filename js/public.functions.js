function newWin(nameImg,width,height){
	LeftPosition = (screen.width) ? (screen.width-width)/2 : 0;
 	TopPosition = (screen.height) ? (screen.height-height)/2 : 0;
	win=open("","",'height='+height+',width='+width+',top='+TopPosition+',left='+LeftPosition);
	win.document.write('<body leftmargin=0 topmargin=0 marginwidth=0 marginheight=0><table cellpadding=0 cellspacing=0 border=0><tr><td><img src="'+nameImg+'" style="cursor:hand;" onclick="window.close();" alt=""></td></tr></table></body></html>');
  	win.document.close();
}

function newWinHtml(urlHtml,width,height){
	LeftPosition = (screen.width) ? (screen.width-width)/2 : 0;
 	TopPosition = (screen.height) ? (screen.height-height)/2 : 0;
	win=open(urlHtml,"",'height='+height+',width='+width+',top='+TopPosition+',left='+LeftPosition);
}

function open_window(link,w,h) {
 LeftPosition = (screen.width) ? (screen.width-w)/2 : 0;
 TopPosition = (screen.height) ? (screen.height-h)/2 : 0;
 var win = 'height='+h+',width='+w+',top='+TopPosition+',left='+LeftPosition+',menubar=no,location=no,scrollbars=yes,resizable=yes';
 newWin = window.open(link,'newWin'+w+h,win);
}

function procSubscribe(frm, proc, w, h) {
	ofrm = document.getElementById(frm);
	window.open('', frm, 'width='+w+',height='+h);
	eval("ofrm.subscribe_type.value="+proc);
	ofrm.submit();
}

function procFocus(ob, txt) {
	if (ob.value == txt) {
		ob.value = '';
	}	
}

function procBlur(ob, txt) {
	if (ob.value == '') {
		ob.value = txt;
	}	
}

function setupCalendar(name, time) {
	Calendar.setup({
		inputField : name, 
		ifFormat : "%d.%m.%Y", 
		showsTime : false, 
		button : "trigger_"+name, 
		align : "Br", 
		singleClick : true,
		timeFormat : 24,
		firstDay : 1
	});
}

document.onkeydown = register;

function register(e) {
	if (!e) e = window.event;
	var k = e.keyCode;
	if (e.ctrlKey) {
		var tagName = (e.target || e.srcElement).tagName;
		if (tagName != 'INPUT' && tagName != 'TEXTAREA') {
			var d;
			if (k == 37) {
				d = $('#previous_page');
			}
			if (k == 39) {
				d = $('#next_page');
			}
			if (d) location.href = d.attr('href');
		}
	}
}

/* start ajax */

function voteProcess(voteName, isProcess) {
	formData = null;
	if (isProcess == 1) {
		formData = $('#frm'+voteName).serialize();
	}
	$.post("/ajax/", {method: 'voteProcess', voteName: voteName, formData: formData},
	function(data){
		$('#'+voteName).html(data.content);
	}, "json");
}

function addCartItem(productId) {
	quantity = $('#amount_'+productId).attr('value');
	price = $('#price_'+productId).html();
	priceId = $('#stuff_price_'+productId+' option:selected').val();
	if (parseInt(quantity) > 0) {
		$.post("/ajax/", {method: 'addCartItem', productId: productId, quantity: quantity, price: price, priceId: priceId},
		function(data){
			if (data.cart_count) {
				$('#cart_count').html(data.cart_count);
			}
			$('#popup_content').html(data.popup_content);
			popUp('popup');
		}, "json");
	} else {
		alert('Неправильный формат количества');
	}
}

function deleteCartItem(productGuid) {
	$.post("/ajax/", {method: 'deleteCartItem', productGuid: productGuid},
	function(data){
		if (data.totalQuantity == 0) {
			location.reload();
		} else {
			$('#cart_count').html(data.cart_count);
			$('#totalQuantity').html(data.totalQuantity);
			$('#totalSum').html(data.totalSum);
			$('#discount').html(data.discount);
			$('#totalSumDiscount').html(data.totalSumDiscount);
			$('#stuff_'+productGuid).remove();
		}
	}, "json");
}

function showOrderDetail(orderId) {
	$.post("/ajax/", {method: 'showOrderDetail', orderId: orderId},
	function(data){
		$('#popup_content').html(data.popup_content);
		popUp('popup');
	}, "json");
}
	
function showSubscribeResult(formId) {
	fields = $('#frm'+formId).serialize();
	$.post("/ajax/", {method: 'showSubscribeResult', formdata: fields},
	function(data){
		$('#subscribe_form').html(data.content);
	}, "json");
}


function sendStuffExist(productId) {
	email = $('#email'+productId).attr('value');
	$.post("/ajax/", {method: 'sendStuffExist', productId: productId, email: email},
	function(data){
		$('#mailblock'+productId).html(data.status);
	}, "json");
}
	
/* end ajax */

function checkForm(form) {
	// Заранее объявим необходимые переменные
	var el, // Сам элемент
	elName, // Имя элемента формы
	value, // Значение
	type; // Атрибут type для input-ов
	// Массив списка ошибок, по дефолту пустой
	var errorList = [];
	// Хэш с текстом ошибок (ключ - ID ошибки)
	var errorText = {
	1 : "Не заполнено поле 'Имя'",
	2 : "Не заполнено поле 'E-mail'",
	3 : "Не заполнено поле 'Телефон'",
	4 : "Неизвестная ошибка"
	}
	// Получаем семейство всех элементов формы
	// Проходимся по ним в цикле
	//form = document.getElementById(frm);
	for (var i = 0; i < form.elements.length; i++) {
	el = form.elements[i];
	elName = el.nodeName.toLowerCase();
	value = el.value;
	if (elName == "input") { // INPUT
	// Определяем тип input-а
	type = el.type.toLowerCase();
	// Разбираем все инпуты по типам и обрабатываем содержимое
	switch (type) {
	case "text" :
	if (el.title != "" && value == "") errorList.push("Не заполнено поле '"+el.title+"'");
	break;
	case "file" :
	//if (value == "") errorList.push(3);
	break;
	case "checkbox" :
	// Ничего не делаем, хотя можем
	break;
	case "radio" :
	// Ничего не делаем, хотя можем
	break;
	default :
	// Сюда попадают input-ы, которые не требуют обработки
	// type = hidden, submit, button, image
	break;
	}
	} else if (el.title != "" && elName == "textarea") { // TEXTAREA
	if (value == "") errorList.push("Не заполнено поле '"+el.title+"'");
	} else if (el.title != "" && elName == "select") { // SELECT
	if (value == 0) errorList.push("Не выбран элемент в поле '"+el.title+"'");
	} else {
	// Обнаружен неизвестный элемент ;)
	}
	}
	// Финальная стадия
	// Если массив ошибок пуст - возвращаем true
	if (!errorList.length) {
		return true;
	}
	// Если есть ошибки - формируем сообщение, выовдим alert
	// и возвращаем false
	var errorMsg = "При заполнении формы допущены следующие ошибки:\n\n";
	for (i = 0; i < errorList.length; i++) {
	errorMsg += errorList[i] + "\n";
	}
	alert(errorMsg);
	return false;
}

function popUp(name) { //default name = pop_up 
	$.dimScreen(500, 0.4, function() {$('#'+name).fadeIn('fast')});
}

var closePopupTimer;
var mdelay = 2000;

function closePopUpTime(time, name) {
	if (time == 0) {
		time = mdelay;
	}
	closePopupTimer = setTimeout('closePopUp(\''+name+'\')', time);
}

function closePopUp(name) {  // default name = pop_up
	clearTimeout(closePopupTimer);
	$('#'+name).css('display', 'none');
	$.dimScreenStop();
	return false;
}

$(window).resize(function(){
	$('#__dimScreen').css({
            height: $(document).height() + 'px'
            ,width: $(document).width() + 'px'
    });
});

var cur_tab = 'offer_stuff';

function changeTab(tab_id) {
	if (tab_id != cur_tab) {
		$('#'+cur_tab).css('display', 'none');
		$('#'+cur_tab+'_link').toggleClass('active');
		$('#'+tab_id).css('display', 'block');
		$('#'+tab_id+'_link').toggleClass('active');
		cur_tab = tab_id;
	}
}

var cur_advert = 1;
var showAdvertTimer;
var time_after_click = 6000;
var max_advert = 5;

function showAdvertClick(it) {
	
	showAdvert(it);
}

function showAdvert(it){
	clearTimeout(showAdvertTimer);
	if (it != cur_advert) {
		
		$('#adv_text_'+cur_advert).fadeOut(800, function () {
            $('#adv_text_'+it).fadeIn(400);
          });
		//$('#adv_text_'+cur_advert).css('display', 'none');
		$('#adv_tab_'+cur_advert).toggleClass('adv-btn-active');
		$('#adv_tab_'+cur_advert).toggleClass('adv-btn');
		//$('#adv_text_'+it).css('display', 'block');
		$('#adv_tab_'+it).toggleClass('adv-btn');
		$('#adv_tab_'+it).toggleClass('adv-btn-active');
		cur_advert = it
	}
	showAdvertTime(it);
}

function showAdvertTime(it) {
	it = (it == max_advert) ? 1 : it+1;
	showAdvertTimer = setTimeout('showAdvert('+it+')', time_after_click);
}

var cur_cat = 0;

function toggleCat(it) {
	if (cur_cat != 0 && cur_cat != it) {
		$("#cat_"+cur_cat).css('display', 'none');
	}
	var x = $(window).width();
	var static_width = 1280;
	var left = 235;
	if (x > static_width) {
		left = (x-static_width)/2+left;
	}
	$('#cat_'+it).css('left', left);
	$("#cat_"+it).css('display', $("#cat_"+it).css('display') == 'none' ? 'block' : 'none');
	cur_cat = it;
	
}

var cur_cat2 = 0;

function toggleCatBlock(it) {
	if (cur_cat2 != 0 && cur_cat2 != it) {
		$("#index_cat_"+cur_cat2).css('display', 'none');
	}
	$("#index_cat_"+it).css('display', $("#index_cat_"+it).css('display') == 'none' ? 'block' : 'none');
	cur_cat2 = it;
}

function toggleBlock(it) {
	$("#"+it).css('display', $("#"+it).css('display') == 'none' ? 'block' : 'none');
}

function setCatalogRTT(el, cur_rtt, cur_page) {
	var url2 = window.location.href;
	//alert(url2);
	if (url2.indexOf('rtt=') != -1) {
		url2 = url2.replace('rtt='+cur_rtt, 'rtt='+el.options[el.selectedIndex].value);
		url2 = url2.replace('page='+cur_page+'&', '');
	} else if (url2.indexOf('?') != -1) {
		url2 = url2 + '&rtt='+el.options[el.selectedIndex].value;
	} else {
		url2 = url2 + '?rtt='+el.options[el.selectedIndex].value;
	}
	//alert(el.options[el.selectedIndex].value);
	//alert(url2);
	window.location = url2;
}

function setCatalogRPP(el, ref) {
	ref = ref + '&limit='+el.options[el.selectedIndex].value;
	window.location = ref;
}

function setPrice(it) {
	$('#price_'+it).html($('#stuff_price_'+it+' option:selected').attr('rel'))
}


/*
 * Url preview script 
 * powered by jQuery (http://www.jquery.com)
 * 
 * written by Alen Grakalic (http://cssglobe.com)
 * 
 * for more info visit http://cssglobe.com/post/1695/easiest-tooltip-and-image-preview-using-jquery
 *
 */
 
var passComplete = false;
function comparePasswords(first, repeate){
    if(repeate.value != first.value) {
        if(document.getElementById) {
            document.getElementById("passStatus").innerHTML = "пароли не совпадают, попробуйте еще раз";
            document.getElementById("passStatus").style.color = "red";
            repeate.style.color = "red";
        }
        passComplete = false;
    }
    else if(repeate.value.length<6) {
        if(document.getElementById) {
            document.getElementById("passStatus").innerHTML = "пароль должен состоять минимум из 6 символов";
            document.getElementById("passStatus").style.color = "red";
            repeate.style.color = "red";
        }
        passComplete = false;
    }
    else {
        if(document.getElementById) {
            document.getElementById("passStatus").innerHTML = "введено верно";
            document.getElementById("passStatus").style.color = "green";
            repeate.style.color = "";
        }
        passComplete = true;
    }
}

function check_pass_change() {
    f = document.forms['registrationForm'];
    if(f.done) {
        f.done.disabled = ((f.passwd.value == "") || !passComplete );
    }

    if(f.passOk) {
      f.passOk.value = ((f.newUserPassword.value == "") || !passComplete ) ? 'false' : 'true';
      checkForm();
    }

    if(f.passOk) {
      f.passOk.value = ((f.newUserPassword.value == "") || !passComplete ) ? 'false' : 'true';
      checkForm();
    }
}

FirstIntent = true;
function checkPass(f, ffirst) {
    if (f.value != "") {
        if (!FirstIntent || (f.value.length >= ffirst.value.length )) {
            FirstIntent = false;
            comparePasswords(ffirst,f);
        }
    }
    check_pass_change();
}

function checkRegForm()
{
 f = document.registrationForm;
 btn = document.getElementById('submitBtn');
 a = trim(f.newUserFName.value);
 b = trim(f.newUserEmail.value);
 b2 = trim(f.newUserPhone.value);
 c = trim(f.captcha.value);
 ok = trim(f.passOk.value);
 btn.disabled = ((a == '' || b == '' || b2 == '' || c == '') || !isEmail(b) || !isPhone(b2) || c.length < 5 || ok == 'false') ? true : false;
}

function trim(str)
{
 return str.replace(/^[\s\xA0]+/, '').replace(/[\s\xA0]+$/, '').replace(/ +$/, '').replace(/^ +/, '');
}

function isEmail(email)
{
 var pattern = /^[-._A-Za-z0-9]{1,}@[-._A-Za-z0-9]{1,}\.[A-Za-z]{2,4}$/;
 return pattern.test(email);
}

function isPhone(phone)
{
 var pattern = /^([+])?[0-9\s\(\)-]{10,}$/;
 return pattern.test(phone);
}

function checkLoginForm()
{
 f = document.mainForm;
 btn = document.getElementById('submitBtn');
 a = trim(f.login.value);
 b = trim(f.password.value);
 btn.disabled = ((a == '' || b == '') || !isEmail(a) || b.length < 3) ? true : false;
}

function checkInfoForm()
{
 f = document.mainForm;
 btn = document.getElementById('submitBtn');
 a = trim(f.userFName.value);
 b = trim(f.userEmail.value);
 c = trim(f.userPhone.value);
 btn.disabled = ((a == '' || b == '') || !isPhone(c) || !isEmail(b)) ? true : false;
}

function checkForgetForm()
{
 f = document.mainForm;
 btn = document.getElementById('submitBtn');
 a = trim(f.login.value);
 c = trim(f.captcha.value);
 btn.disabled = (!isEmail(a) || c.length < 5) ? true : false;
}

function checkDetailForm()
{
 f = document.mainForm;
 btn = document.getElementById('submitBtn');
 a = trim(f.deliveryAddress.value);
 b = trim(f.deliveryPerson.value);
 c = trim(f.deliveryEmail.value);
 d = trim(f.deliveryPhone.value);
 btn.disabled = ((a == '' || b == '') || !isEmail(c) || !isPhone(d)) ? true : false;
}

function procBlurEmail(el, text, email_id){
	procBlur(el, text);
	checkEmailForm(email_id);
}

function checkEmailForm(email_id) {
	a = trim($('#email'+email_id).attr('value'));
	var flag = (!isEmail(a)) ? true : false;
	$('#btnEmail'+email_id).attr('disabled', flag);
}

function setPayType(pay_id) {
	
}

function setDeliveryType(delivery_id) {
	$('.delivery-text').css('display', 'none');
	$('#deliveryDescr'+delivery_id).css('display', 'block');
	if (delivery_id == 2) {
		$('#payType2').attr('checked', true);
		$('#payType1').attr('disabled', true);
		$('#payDescr2').css('display', 'block');
	} else {
		$('#payType1').attr('disabled', false);
		$('#payDescr2').css('display', 'none');
	}
}

function showMailForm(productId) {
	$('#mailblock'+productId).css('display', 'block');
	return false;
}


function bindDetailForm() {
	$('.required').bind({
		blur: function() {
			checkDetailForm()
		},
		focus: function() {
			checkDetailForm()
		},
		keypress: function() {
			checkDetailForm()
		},
		keyup: function() {
			checkDetailForm()
		}
	});
	checkDetailForm();
}

function bindLoginForm() {
	$(document).ready(function(){
		$('.required').bind({
			blur: function() {
				checkLoginForm()
			},
			focus: function() {
				checkLoginForm()
			},
			keypress: function() {
				checkLoginForm()
			},
			keyup: function() {
				checkLoginForm()
			}
		});
		checkLoginForm();
	});
}

function bindInfoForm() {
	$(document).ready(function(){
		$('.required').bind({
			blur: function() {
				checkInfoForm()
			},
			focus: function() {
				checkInfoForm()
			},
			keypress: function() {
				checkInfoForm()
			},
			keyup: function() {
				checkInfoForm()
			}
		});
		checkInfoForm();
	});
}

function bindZoom() {
	var options = {
		zoomWidth: 350,
		zoomHeight: 350,
		xOffset: 10,
		yOffset: 0,
		offset: 5,
		preload:1,
		position: "right" //and MORE OPTIONS
	};

	$(".jqzoom").jqzoom(options);
}

function initFB() {
	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/ru_RU/all.js#xfbml=1";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
}

function initVK() {
	VK.init({apiId: 3169203, onlyWidgets: true});
}

function initVKLike() {
	VK.Widgets.Like("vk_like", {type: "button", height: 20});
}

function initVKLikebox() {
	VK.Widgets.Group("vk_groups", {mode: 0, width: "200", height: "290"}, 21028918);
}
