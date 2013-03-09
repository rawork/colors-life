{$local_h1}
{foreach from=$items item=art}
<div class="article-block"> 
    <div class="article-title"><a href="{raURL node=articles method=read prms=$art.id}">{$art.name}</a></div>
	<div class="article-text">{$art.preview}</div>
	<div class="article-link"><a href="{raURL node=articles method=read prms=$art.id}">Подробнее &rarr;</a></div>
	<hr>
</div>
{/foreach}
{$paginator->render()}