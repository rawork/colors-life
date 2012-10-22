{if $smarty.get.tag}
{raItem var=tag table=article_tag select="id,name" query=$smarty.get.tag}
{raPaginator var=paginator table=article_article query="tag LIKE '%`$tag.name`%' AND publish=1 AND node_id=`$node.id`" pref="`$ref``$methodName`.###.htm?tag=`$tag.id`" per_page=$settings.per_page page=$param0 tpl=public}
{raItems var=items table=article_article query="tag LIKE '%`$tag.name`%' AND publish=1 AND node_id=`$node.id`" limit=$paginator->limit}
<h1>Все статьи на тему &laquo;{$tag.name}&raquo;</h1>
{else} 
{raPaginator var=paginator table=article_article query="publish=1 AND node_id=`$node.id`" pref="`$ref``$methodName`.###.htm" per_page=$settings.per_page page=$param0 tpl=public}
{raItems var=items table=article_article query="publish=1 AND node_id=`$node.id`" limit=$paginator->limit}
{/if}
{if is_object($paginator)}{$paginator->render()}{/if}
{foreach from=$items item=art}
 <div class="article-block"> 
    <div class="article-title"><a href="{raURL node=$art.node_id_name method=read prms=$art.id}">{$art.name}</a></div>
  <div class="article-text">{$art.preview}</div>
  <div class="article-link"><a href="{raURL node=$art.node_id_name method=read prms=$art.id}">Подробнее &gt;</a></div>
</div>
  {/foreach}
{if is_object($paginator)}{$paginator->render()}{/if} 