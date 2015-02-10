<!DOCTYPE html>
<head>
<title>{$title}</title>
{$meta}
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="/bundles/bootstrap/css/bootstrap.css" type="text/css">
<link rel="stylesheet" href="/css/default.css" type="text/css">
<link rel="stylesheet" href="/css/cat-selectors.css" type="text/css">
<link href="/bundles/admin/css/calendar-mos.css" type="text/css" rel="stylesheet">
<link rel="stylesheet" href="/bundles/jqzoom/css/jquery.jqzoom.css" type="text/css">
<link rel="stylesheet" href="/bundles/lightbox/css/jquery.lightbox-0.5.css" type="text/css">
<link href=”/favicon.ico” rel=”icon” type=”image/x-icon” />
<link href=”/favicon.ico” rel=”shortcut icon” type=”image/x-icon” />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/dimmer.js"></script>
<script type="text/javascript" src="/bundles/jqzoom/js/jquery.jqzoom-core.js"></script>
<script type="text/javascript" src="/bundles/lightbox/js/jquery.lightbox-0.5.js"></script>
<script type="text/javascript" src="/js/public.functions.js?2"></script>
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
			<div class="head-logo"><a href="/"><img src="/img/logo.png" width="128"></a></div>
		</div>
		<div class="span21">
			<div class="head-mode"> 
				<strong>Интернет-магазин экологически чистых товаров</strong><br>
				{raMethod path=Fuga:Public:Common:block args='["name":"rejim"]'}
			</div>
            <div class="head-phone">
				{raMethod path=Fuga:Public:Common:block args='["name":"phone"]'}
			</div>
			<div class="clearfix"></div>
			<div class="head-search">
				<form class="form-search" action="{raURL node=catalog method=search}" method="get">
					<input type="text" name="text" autocomplete="off" placeholder="Поиск" class="input-xlarge search-query">
					<input type="submit" class="btn" value="Поиск">
				</form>
			</div>
		</div>
		<div class="span12">
			<div class="head-user">
				{raMethod path=Fuga:Public:Account:widget}
			</div>
			<div class="head-cart">
				<h5>Ваша корзина</h5>
                <div id="cart_info"> 
					{raMethod path=Fuga:Public:Cart:widget}
				</div>
			</div>	  
		</div>	
	</div>
	<div class="row-fluid">
		<div class="span40">
			<div class="mainmenu">
				<ul>
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
				{raMethod path=Fuga:Public:Catalog:cats}
				{if $node.name == 'articles'}
				{raMethod path=Fuga:Public:Article:cloud}
				{/if}
				<div class="links-block">
					<div><a href="{raURL node=feedback}">Задать вопрос</a></div>
					<div><a href="{raURL node=subscribe-process}">Подписка на рассылку</a></div>
				</div>
				<h4>Бренды</h4>
				{raMethod path=Fuga:Public:Catalog:brands}
				<br><br><br>
				<div class="widgets">
					{include file='counters.tpl'}
				</div>
			</div>
		</div>
		<div class="span30">
			<div class="content-column">
				{raMethod path=Fuga:Public:Common:breadcrumb}
				<div class="inner-content">
					<h1>{$h1}</h1>
					{eval var=$mainbody}
				</div>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span40">
			<br>
			<div class="footer-content">Copyright &copy; 2010-2013 &laquo;Цвета жизни&raquo; - интернет-магазин экологически чистых товаров</div>
		</div>
	</div>
</div>	
<div class="popup" id="popup" cellpadding="0" cellspacing="0" border="0">
	<a class="close popup-btn" href="#" onClick="return closePopUp('popup')">&times;</a>
	<div id="popup_content"></div>
</div>
{raMethod path=Fuga:Public:Catalog:selectors}
{*{literal}*}
{*<script type="text/javascript"> _shcp = []; _shcp.push({widget_id : 592655, widget : "Chat", side : "right", position : "top", template : "purple", title : "Онлайн помощник!", title_offline : "Здравствуйте, чем могу Вам помочь?" }); (function() { var hcc = document.createElement("script"); hcc.type = "text/javascript"; hcc.async = true; hcc.src = ("https:" == document.location.protocol ? "https" : "http")+"://widget.siteheart.com/apps/js/sh.js"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(hcc, s.nextSibling); })(); </script>*}
{*{/literal}*}
<link rel="stylesheet" href="//chaser.ru/widget/1.1/css/chaser.css" />
<script src="//chaser.ru/widget/1.1/js/chaser.js?dataTagID=07a81d45ff030b63fe2a0f375b779f09" charset="utf-8"></script>
</body>
</html>
