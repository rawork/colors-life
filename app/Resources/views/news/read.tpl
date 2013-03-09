<div id="fb-root"></div>
<script>initFB()</script>
<script type="text/javascript" src="//vk.com/js/api/openapi.js?56"></script>
<script type="text/javascript">initVK();</script>	

<h1>{$news.name}</h1>
<div class="fb-like" style="display: inline-block;" data-href="http://{$smarty.server.SERVER_NAME}{raURL node=news method=read prms=$news.id}" data-send="false" data-layout="button_count" data-width="100" data-show-faces="true"></div>
<div style="display: inline-block;" id="vk_like"></div>
<script type="text/javascript">initVKLike()</script>
<br><br>
<div class="news-img pull-left">{if $news.image}<img width="72" height="72" src="{$news.image}">{/if}</div>
<div class="news-content pull-left">
<div class="news-title"><span>{$news.created|fdate:"d.m.Y"}</span></div>
<div class="news-text">{$news.body}</div>
<div><a href="{raURL node=news}">Все новости</a></div>
</div>