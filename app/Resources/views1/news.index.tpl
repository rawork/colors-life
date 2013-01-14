{raPaginator var=paginator table=news_news query="publish=1" pref="`$ref``$methodName`.htm?page=###" per_page=10 page=$smarty.get.page tpl=public}
{raItems var=items table=news_news query="publish=1" limit=$paginator->limit}
<h1>Новости</h1>
{foreach from=$items item=news}
<div class="news-img pull-left">{if $news.image}<a href="{raURL node=$news.node_id_name method=read prms=$news.id}"><img width="72" height="72" src="{$news.image}"></a>{/if}</div>
<div class="news-content pull-left">
<div class="news-title"><a href="{raURL node=$news.node_id_name method=read prms=$news.id}">{$news.name}</a> <span>{$news.created|fdate:"d.m.Y H:i"}</span></div>
<div class="news-text">{$news.preview}</div>
</div>
<div class="clearfix"></div>
{/foreach}
<div>{if is_object($paginator)}{$paginator->render()}{/if}</div>