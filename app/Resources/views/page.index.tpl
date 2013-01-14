<!DOCTYPE html>
<head>
<title>{$title}</title>
{$meta}
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="/bundles/bootstrap/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="/css/default.css" type="text/css">
<link rel="stylesheet" href="/css/cat-selectors.css" type="text/css">
<link href=”/favicon.ico” rel=”icon” type=”image/x-icon” />
<link href=”/favicon.ico” rel=”shortcut icon” type=”image/x-icon” />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/dimmer.js"></script>
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
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span7">
			<div class="head-logo"><a href="/"><img src="/img/logo.gif" width="128"></a></div>
		</div>
		<div class="span21">
			<div class="head-mode"> <strong>Интернет-магазин экологически чистых товаров</strong><br>{raInclude var=rejim}</div>
            <div class="head-phone">
				{raInclude var=phone}
			</div>
			<div class="clearfix"></div>
			<div class="head-search">
				<form class="form-search" action="/search.htm" method="get">
					<input type="text" name="text" autocomplete="off" placeholder="Поиск" class="input-xlarge search-query">
					<button type="submit" class="btn">Поиск</button>
				</form>
			</div>
		</div>
		<div class="span12">
			<div class="head-user">
				{if $auth->user}
				<b>{$auth->user.name} {$auth->user.lastname}</b> / <a href="/cabinet/">Личный кабинет</a> / <a href="/cabinet/logout.htm">Выйти</a>
				{else}
				<a href="/cabinet/">Вход в личный кабинет</a> / <a href="/cabinet/registration.htm">Регистрация</a>
				{/if}
			</div>
			<div class="head-cart">
				<h5>Ваша корзина</h5>
                <div id="cart_info"> 
					{raMethod ref=/cart/widget.htm} 
				</div>
			</div>	  
		</div>	
	</div>
	<div class="row-fluid">
		<div class="span40">
			<div class="mainmenu">
				<ul>
				{raDir var=links query=/}
				{foreach from=$links item=menuitem}
				<li><a href="{$menuitem.ref}">{$menuitem.title}</a></li>
				{/foreach}
				</ul>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span10">
			<div class="left-column">
				{raMethod ref=/catalog/cats.htm}
				<div class="links-block">
					<div><a href="/feedback.htm">Задать вопрос</a></div>
					<div><a href="/subscribe-process.htm">Подписка на рассылку</a></div>
				</div>
				{raMethod ref=/catalog/brands.htm}
				<br><br><br>
				<div class="widgets">
					<div id="fb-root"></div>
					<script>initFB()</script>
					<script type="text/javascript" src="//vk.com/js/api/openapi.js?56"></script>
					<script type="text/javascript">initVK();</script>
					<div class="fb-like-box" data-href="http://www.facebook.com/colors-life.ru" data-width="200" data-show-faces="true" data-stream="false" data-header="false"></div>
					<br><br>
					<div id="vk_groups"></div>
					<script type="text/javascript">initVKLikebox();</script>
					<br><br>
					{include file='counters.tpl'}
				</div>
			</div>
		</div>
		<div class="span30">
			<div class="content-column">
				{raMethod ref=/catalog/advert.htm}
				<div class="index-content">{$mainbody}</div>
				<div class="spec-link">
					<a href="javascript:changeTab('offer_stuff')" id="offer_stuff_link" class="active">Спецпредложения</a> 
					<a href="javascript:changeTab('new_stuff')" id="new_stuff_link">Новинки</a>
				</div>
				<div id="offer_stuff">{raMethod ref=/catalog/offer.htm}</div>
				<div id="new_stuff" style="display:none">{raMethod ref=/catalog/new.htm}</div>
				<div class="news">{raMethod ref=/news/lenta.htm}</div>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span40">
			<div class="partners">{raMethod ref=/catalog/partners.htm}</div>
			<div class="footer-content">Copyright &copy; 2010-2012 &laquo;Цвета жизни&raquo; - интернет-магазин экологически чистых товаров</div>
		</div>
	</div>
</div>	
<div class="popup" id="popup" cellpadding="0" cellspacing="0" border="0">
	<a class="close popup-btn" href="#" onClick="return closePopUp('popup')">&times;</a>
	<div id="popup_content"></div>
</div>
{raMethod ref=/catalog/selectors.htm}			
</body>
</html>