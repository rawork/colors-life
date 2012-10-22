<html>
<head>
<title>{$title}</title>
{$meta}
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="/bundles/admin/css/calendar-mos.css" type="text/css" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css" type="text/css">
<link rel="stylesheet" href="/bundles/jqzoom/css/jquery.jqzoom.css" type="text/css">
<link rel="stylesheet" href="/bundles/lightbox/css/jquery.lightbox-0.5.css" type="text/css">
<link href=”/favicon.ico” rel=”icon” type=”image/x-icon” />
<link href=”/favicon.ico” rel=”shortcut icon” type=”image/x-icon” />
<script type="text/javascript" src="/js/swfobject.js"></script>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/dimmer.js"></script>
<script type="text/javascript" src="/bundles/jqzoom/js/jquery.jqzoom-core.js"></script>
<script type="text/javascript" src="/bundles/lightbox/js/jquery.lightbox-0.5.js"></script>
<script type="text/javascript" src="/js/public.functions.js"></script>
<script type="text/javascript" src="/bundles/admin/js/calendar.js" ></script>
<script type="text/javascript" src="/bundles/admin/js/calendar-ru.js"></script>
<script type="text/javascript" src="/bundles/admin/js/calendar-setup.js"></script>
{literal}
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-18758509-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
{/literal}
</head>
<body>
<table align="center" class="main" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td style="padding-bottom:20px;"><table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td width="33%" class="head-contacts"><img src="/img/map.gif" border="0" usemap="#Map">
            <div class="head-mode"> Мы работаем ежедневно<br>
              {raInclude var=rejim}</div>
            <div class="head-phone">{raInclude var=phone}</div>
            <div class="head-contacts-link"><a href="/contacts.htm">контакты</a></div></td>
          <td width="33%" class="head-logo"><a href="/"><img src="/img/logo.gif"></a></td>
          <td width="33%" class="head-cart"><table align="right" width="260" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td colspan="2"><div class="head-auth-link">
				{if $auth->user}
				<b>{$auth->user.name} {$auth->user.lastname}</b> / <a href="/cabinet/">Личный кабинет</a> / <a href="/cabinet/logout.htm">Выйти</a>
				{else}
				<a href="/cabinet/">У меня уже есть логин</a> / <a href="/cabinet/registration.htm">Регистрация</a>
				{/if}
				</div></td>
              </tr>
              <tr>
                <td class="head-cart-content"><h5><a href="/cart/">Корзина</a></h5>
                  <div id="cart_count">
				  {if $smarty.session.number}
				  <span>{$smarty.session.number}</span> товаров<br>
                  на сумму <span>{$smarty.session.summa}</span> руб.
				  {else}
				  Нет выбранных <br>
                  товаров{/if} </div>
                  <div class="head-cart-link"><a href="/cart/">Оформить заказ</a></div></td>
                <td><img src="/img/cart.jpg"></td>
              </tr>
              <tr>
                <td colspan="2" style="padding-top:10px;"><form name="frmSearch" id="frmSearch" action="/search.htm" method="get">
				<input type="hidden" name="lang" value="ru">
                <table width="87%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td><img src="/img/search_input_left.gif"></td>
                      <td width="80%"><input type="text" name="text" onFocus="procFocus(this, 'Поиск по сайту')" onBlur="procBlur(this, 'Поиск по сайту')" value="Поиск по сайту" class="search-text"></td>
                      <td width="20%" style="padding-left: 5px;"><a href="javascript:document.frmSearch.submit()"><img src="/img/search_btn.jpg"></a></td>
                    </tr>
                  </table>
                  </form></td>
              </tr>
            </table></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td><table class="mainmenu" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td><img src="/img/mainmenu_left.gif" /></td>
          {raDir var=pages query=/}
          {foreach from=$pages item=menuitem}
          <td width="12.5%"><a href="{$menuitem.ref}">{$menuitem.title}</a></td>
          {/foreach}
          <td><img src="/img/mainmenu_right.gif" /></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td><img src="/img/0.gif" width="1" height="15"></td>
  </tr>
  <tr>
    <td height="100%"><table width="100%" style="height:100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td valign="top">
            <div class="leftmenu-grey-line"><img src="/img/0.gif" width="240" height="4"></div>
            {raMethod ref=/catalog/cats.htm}
            {if $node.id == 98}
            {raMethod ref=/article/cloud.htm}
			{/if}
            <div class="faq-block"><a href="/feedback.htm"><img src="/img/faq_btn.gif"></a></div>
            <div class="faq-block"><a href="/subscribe-process.htm"><img src="/img/maillist_btn.gif"></a></div>
			
			{raMethod ref=/catalog/brands.htm}
            <br><br><br>
			<!-- Include counters.tpl -->
			{include file='counters.tpl'}
	<br><br></td>
          <td><img src="/img/0.gif" width="40" height="1"></td>
          <td class="maincontent-inner">
            {raPath visible=1} 
            
            {eval var=$mainbody}
			<br>
				
            </td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td><table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td><img src="/img/footer_left.gif"></td>
          <td width="100%" class="footer-content">&copy; 2010 «Цвета Жизни»</td>
          <td><img src="/img/footer_right.gif"></td>
        </tr>
      </table></td>
  </tr>
</table>
<div class="popup" id="popup">
	<a href="#" class="popup-btn" onClick="return closePopUp('popup')">&times;</a>
	<div id="popup_content"></div>
</div>
{raMethod ref=/catalog/selectors.htm}				
<map name="Map">
  <area shape="rect" coords="0,1,10,9" href="/">
  <area shape="rect" coords="59,1,75,10" href="/sitemap.htm">
  <area shape="rect" coords="122,2,134,11" href="/feedback.htm">
</map>

</body>
</html>