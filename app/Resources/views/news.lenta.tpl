{raItems var=items table=news_news query="publish=1" limit=$settings.per_lenta}
{if count($items)}
<h4><a href="/news/">Новости</a></h4>
{foreach from=$items item=news}
<div class="news-img pull-left">{if $news.image}<a href="{raURL node=$news.node_id_name method=read prms=$news.id}"><img width="72" height="72" src="{$news.image}"></a>{/if}</div>
<div class="news-content pull-left">
<div class="news-title"><a href="{raURL node=$news.node_id_name method=read prms=$news.id}">{$news.name}</a> <span>{$news.created|fdate:"d.m.Y H:i"}</span></div>
<div class="news-text">{$news.preview}</div>
</div>
<div class="clearfix"></div>
{/foreach}
{/if}