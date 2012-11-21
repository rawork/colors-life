{raItems var=items table=catalog_partner query="publish=1"}
<noindex>
<div class="partners-title pull-left">Наши партнеры</div>
<div class="partners-logos pull-left">
	{foreach from=$items item=item name=logos}
    <div class="partners-logo pull-left">
		<a target="_blank" href="{$item.link}"><img src="{$item.logo}" alt="{$item.name}"></a>
	</div>
	{if $smarty.foreach.logos.iteration % 5 == 0}<div class="clearfix"></div>{/if}
	{/foreach}
</div>
<div class="clearfix"></div>
</noindex>