{raItems var=items table=catalog_product query="publish=1 AND is_new=1" limit=$settings.limit_new}
<table class="product-table" cellpadding="0" cellspacing="0" border="0">
	{foreach from=$items item=item name=product}
	{raItem var=cat0 table=catalog_category query=$item.category_id_root_id}
	{raCount var=prices table=catalog_price query="product_id=`$item.id` AND publish=1"}
	{if $smarty.foreach.product.iteration == 1}<tr>{/if}
		<td class="product-content">
			<div class="product-image pull-left">
				<a href="{raURL node=catalog method=stuff prms=$item.id}">{if $item.small_imagenew}<img src="{$item.small_imagenew}">{else}<img src="/img/noimage_small.jpg">{/if}</a>
			</div>
			<div class="product-description pull-left">
				<div class="product-title"><a href="{raURL node=catalog method=stuff prms=$item.id}"><span>{$item.name}</span></a></div>	
				<div class="product-producer"><a href="{raURL node=catalog method=brand prms=$item.producer_id}">{$item.producer_id_name}</a> ({$item.producer_id_country})</div>
				{if $item.discount_price != '0.00'}
				<div class="product-price-no">{$item.price} руб.</div>
				<div class="product-price">{if $prices}от {/if}{$item.discount_price} руб.</div>
				{else}
				<div class="product-price">{if $prices}от {/if}{$item.price} руб.</div>
				{/if}
				<a class="btn btn-warning btn-large" href="{raURL node=catalog method=stuff prms=$item.id}">Купить</a>
				<div class="product-exists">{if $item.is_exist}<img src="/img/vnalich.png">{else}<img src="/img/zakaz.png">{/if}</div>
			</div>
			<div class="clearfix"></div>
		</td>
	{if $smarty.foreach.product.iteration % 2 == 0}</tr><tr>{/if}
	{/foreach}
</table>
