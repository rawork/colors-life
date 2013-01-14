<h4>Бренды</h4>
{raItems var=items table=catalog_producer query="publish=1" limit=100}            
{if count($items)}
<div class="tag-cloud">
	<ul>
	{foreach from=$items item=item}
	<li><a class="w{$item.weight}" rel="tag" href="{raURL node=catalog method=brand prms=$item.id}">{$item.name}</a></li>
	{/foreach}
	</ul>
</div>
{else}
Данные не найдены.
{/if}	
