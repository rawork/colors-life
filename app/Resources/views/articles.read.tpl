{raItem var=item table=articles_articles query=$param0}
{if $item}
{raSetVar var=title value=$item.name}	
<h1>{$item.name}</h1>
<div class="article-text">{$item.body}</div>
{else}
Статья не найдена
{/if}