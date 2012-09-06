<div class="producers-title"><span>Производители</span></div>

{raItems var=items table=catalog_producers query="publish='on'" limit=100}            
{if count($items)}
    <div class="tag-cloud">
	<ul>
	{foreach from=$items item=item}
	<li><a class="w{$item.position}" rel="tag" href="{raURL node=catalog method=brand prms=$item.id}">{$item.name}</a></li>
	{/foreach}
	</ul>
	<!-- <br><a href="{raURL node=catalog method=fullbrands}">все производители</a> -->
    </div>
{else}
Данные не найдены.
{/if}	
