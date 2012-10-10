{if !$param0}{raException}{/if}
{raItem var=item table=article_article query=$param0}
{if $item.publish}
{raSetVar var=title value=$item.name}	
<h1>{$item.name}</h1>
<div class="article-text">{$item.body}</div>
{else}
{raException}
{/if}