{raPageNavigation var=pages table=news_news query="publish='on'" pref="`$ref``$mname`.htm?page=###" per_page=10 page=$smarty.get.page tpl=public}
{raItems var=items table=news_news query="publish='on'" limit=$pages->limit}
<table class="news-table" cellpadding="0" cellspacing="0" border="0">
{foreach from=$items item=news}
<tr>
<td valign="top">{if $news.image}<a href=""><img width="72" height="72" src="{$news.image}"></a>{/if}</td>
<td valign="top">
<div class="news-title"><a href="{raURL node=$news.dir_id_name method=read prms=$news.id}">{$news.name}</a> <span>{$news.credate|fdate:"d.m.Y H:i"}</span></div>
<div class="news-text">{$news.announce}</div>
</td>
</tr>
{/foreach}
</table>
<div>{if is_object($pages)}{$pages->getText()}{/if}</div>