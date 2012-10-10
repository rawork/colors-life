{if !$param0}{raException}{/if}
{raItem var=news table=news_news query=$param0}
{if $news.publish}
{raSetVar var=title value=$news.name}
{raSetVar var=h1 value=$news.name}
<h1>{$news.name}</h1>
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