{if count($items)}
<h4>Бренды</h4>
<div class="tag-cloud">
	<ul>
	{foreach from=$items item=item}
	<li><a class="w{$item.weight}" rel="tag" href="{raURL node=catalog method=brand prms=$item.id}">{$item.name}</a></li>
	{/foreach}
	</ul>
</div>
{/if}	
