{if !$param0}{raException}{/if}
{raItem var=news table=news_news query=$param0}
{if $news.publish}
<div id="fb-root"></div>
<script>initFB()</script>
<script type="text/javascript" src="//vk.com/js/api/openapi.js?56"></script>
<script type="text/javascript">initVK();</script>	
{raSetVar var=title value=$news.name}
{raSetVar var=h1 value=$news.name}
<h1>{$news.name}</h1>
<div class="fb-like" style="display: inline-block;" data-href="http://{$smarty.server.SERVER_NAME}{raURL node=news method=read prms=$item.id}" data-send="false" data-layout="button_count" data-width="100" data-show-faces="true"></div>
<div style="display: inline-block;" id="vk_like"></div>
<script type="text/javascript">initVKLike()</script>
<br><br>
<table class="news-table" cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top">{if $news.image}<a href=""><img width="72" height="72" src="{$news.image}"></a>{/if}</td>
<td valign="top">
<div class="news-title"><span>{$news.created|fdate:"d.m.Y H:i"}</span></div>
<div class="news-text">{$news.body}</div>
</td>
</tr>
</table>
{else}
{raException}
{/if}