<div class="producers-title"><span>От А до Я</span></div>

{raItems var=items table=article_tag sort=name limit=100}            
{if count($items)}
    <div class="tag-cloud">
	<ul>
	{foreach from=$items item=item}
	<li><a class="w{$item.weight}" rel="tag" href="{raURL node=articles method=index}?tag={$item.id}">{$item.name}</a></li>
	{/foreach}
	</ul>
	<br><a href="{raURL node=articles method=tags}">все метки</a>
    </div>
{else}
Данные не найдены.
{/if}			