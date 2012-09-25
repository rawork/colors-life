<html>
<head>
<title>{$title}</title>
{$meta}
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="/css/style.css" type="text/css">
<link href=”/favicon.ico” rel=”icon” type=”image/x-icon” />
<link href=”/favicon.ico” rel=”shortcut icon” type=”image/x-icon” />
<script type="text/javascript" src="/js/swfobject.js"></script>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/dimmer.js"></script>
<script type="text/javascript" src="/js/jquery.jqzoom.js"></script>
<script type="text/javascript" src="/js/public.functions.js"></script>
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
{literal}
<!-- Yandex.Metrika -->
<div style="display:none;"><script type="text/javascript">
(function(w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter2019754 = new Ya.Metrika(2019754);
             yaCounter2019754.clickmap(true);
             yaCounter2019754.trackLinks(true);

        } catch(e) {}
    });
})(window, 'yandex_metrika_callbacks');
</script></div>
<script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
<noscript><div style="position:absolute"><img src="//mc.yandex.ru/watch/2019754" alt="" /></div></noscript>
<!-- /Yandex.Metrika -->
{/literal}
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
                <td colspan="2">
				<div class="head-auth-link">
				{if $auth->user}
				<b>{$auth->user.name} {$auth->user.lastname}</b> | <a href="/cabinet/">Личный кабинет</a> | <a href="/cabinet/logout.htm">Выйти</a>
				{else}
				<a href="/cabinet/">У меня уже есть логин</a> | <a href="/cabinet/registration.htm">Регистрация</a>
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
                <td colspan="2" style="padding-top:10px;">
                <form name="frmSearch" id="frmSearch" action="/search.htm" method="get">
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
          {raDir var=trees query=/}
          {foreach from=$trees item=menuitem}
          <td width="14%"><a href="{$menuitem.ref}">{$menuitem.title}</a></td>
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
			<div class="leftmenu-grey-line"><img src="/img/0.gif" height="4" width="240"></div>
			{raMethod ref=/catalog/cats.htm}
            
            <div class="faq-block"><a href="/feedback.htm"><img src="/img/faq_btn.gif"></a></div>
			<div class="faq-block"><a href="/subscribe-process.htm"><img src="/img/maillist_btn.gif"></a></div>
            
            {raMethod ref=/catalog/brands.htm}
			
			<br><br><br>
			<!-- Include counters.tpl -->
			{include file='counters.tpl'}
			</td>
          <td><img src="/img/0.gif" width="40" height="1"></td>
          <td class="maincontent">
          	{raMethod ref=/catalog/advert.htm}
			<div class="index-content">{$mainbody}</div>
			{raMethod ref=/news/lenta.htm}
            <div class="spec-link"><a href="javascript:changeTab('offer_stuff')" id="offer_stuff_link" class="active">Спецпредложения</a> <a href="javascript:changeTab('new_stuff')" id="new_stuff_link">Новинки</a></div>
            <div id="offer_stuff">{raMethod ref=/catalog/offer.htm}</div>
            <div id="new_stuff" style="display:none">{raMethod ref=/catalog/new.htm}</div>
			<br>
			
            </td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td class="footer-partners">{raMethod ref=/catalog/partners.htm}</td>
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
<table class="popup" id="popup" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td class="extramenu-content">
				<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr><td align="right"><a href="#" onClick="return closePopUp('popup')"><img src="/img/close_btn.png"></a></td></tr>
				<tr><td id="popup_content"></td></tr>
				</table>
				</td>
              </tr>
              <tr>
                <td style="background:url('/img/extramenu_b.gif') repeat-x"><img src="/img/extramenu_b.gif"></td>
              </tr>
            </table>
{raMethod ref=/catalog/selectors.htm}			
<map name="Map">
  <area shape="rect" coords="0,1,10,9" href="/">
  <area shape="rect" coords="59,1,75,10" href="/sitemap.htm">
  <area shape="rect" coords="122,2,134,11" href="/feedback.htm">
</map>

</body>
</html>