{raPaginator var=paginator table=news_news query="publish=1" pref="`$ref``$methodName`.htm?page=###" per_page=10 page=$smarty.get.page tpl=public}
{raItems var=items table=news_news query="publish=1" limit=$paginator->limit}
<table class="news-table" cellpadding="0" cellspacing="0" border="0">
{foreach from=$items item=news}
<tr>
<td valign="top">{if $news.image}<a href=""><img width="72" height="72" src="{$news.image}"></a>{/if}</td>
<td valign="top">
<div class="news-title"><a href="{raURL node=$news.node_id_name method=read prms=$news.id}">{$news.name}</a> <span>{$news.created|fdate:"d.m.Y H:i"}</span></div>
<div class="news-text">{$news.preview}</div>
</td>
</tr>
{/foreach}
</table>
<div>{if is_object($paginator)}{$paginator->render()}{/if}</div>