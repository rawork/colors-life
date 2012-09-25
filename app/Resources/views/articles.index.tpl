{raItems var=items table=articles_articles nquery="SELECT SUBSTRING(termin, 1, 1) as letter FROM articles_articles WHERE publish='on' GROUP BY SUBSTRING(termin, 1, 1) ORDER BY SUBSTRING(termin, 1, 1)"}
<!--<div class="letters">
{foreach from=$items item=item}
 <a href="{raURL node=$node.name method=letter prms=$item.letter}">{$item.letter}</a> 
 {/foreach}
 </div>-->
{if $smarty.get.tag}
{raItem var=tag table=articles_tags select="id,name" query=$smarty.get.tag}
{raPaginator var=paginator table=articles_articles query="tags LIKE '%`$tag.name`%' AND publish='on' AND dir_id=`$node.id`" pref="`$ref``$methodName`.###.htm?tag=`$tag.id`" per_page=$settings.per_page page=$param0 tpl=public}
{raItems var=items table=articles_articles query="tags LIKE '%`$tag.name`%' AND publish='on' AND dir_id=`$node.id`" limit=$paginator->limit}
<h1>Все статьи на тему &laquo;{$tag.name}&raquo;</h1>
{else} 
{raPaginator var=paginator table=articles_articles query="publish='on' AND dir_id=`$node.id`" pref="`$ref``$methodName`.###.htm" per_page=$settings.per_page page=$param0 tpl=public}
{raItems var=items table=articles_articles query="publish='on' AND dir_id=`$node.id`" limit=$paginator->limit}
{/if}
{if is_object($paginator)}{$paginator->getText()}{/if}
{foreach from=$items key=k item=art}
 <div class="article-block"> 
    <div class="article-title"><a href="{raURL node=$art.dir_id_name method=read prms=$art.id}">{$art.name}</a></div>
  <div class="article-text">{$art.announce}</div>
  <div class="article-link"><a href="{raURL node=$art.dir_id_name method=read prms=$art.id}">Подробнее &gt;</a></div>
</div>
  {/foreach}
{if is_object($paginator)}{$paginator->getText()}{/if} 