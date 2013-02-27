{if !$param0}{raException}{/if}
{raItem var=item table=catalog_product query=$param0}
{if $item.publish}
	{raSetVar var=title value=$item.name}
	{raItem var=cat0 table=catalog_category query=$item.category_id_root_id}
	{raItems var=prices table=catalog_price query="product_id=`$item.id` AND publish=1" sort="sort,size_id"}
	{raItems var=fotos nquery="SELECT * FROM system_files WHERE table_name='catalog_product' AND entity_id=`$item.id` ORDER BY created"}
	{raItems var=articles nquery="SELECT ar.* FROM article_products_articles st_ar JOIN article_article ar ON ar.id=st_ar.article_id WHERE st_ar.product_id=`$item.id` GROUP BY st_ar.article_id"}	
	<div id="fb-root"></div>
	<script>initFB()</script>
	<script type="text/javascript" src="//vk.com/js/api/openapi.js?56"></script>
	<script type="text/javascript">initVK();</script>
	<h1>{$item.name}</h1>
	<table class="product-table-card" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td class="product-content">
				<div class="product-image-card pull-left">
					{if $item.imagenew}
						<a href="{$item.imagenew}" class="photo-zoom" title="{$item.name}"><img src="{$item.middle_imagenew}" title="{$item.name}"></a>
						<script type="text/javascript">
							bindZoom();
						</script>
					{else}
					<img src="/img/noimage_small.jpg">
					{/if} 
					{if count($fotos)}
					<div class="clearfix"></div>	
					<div class="product-gallery"><strong>Фото:</strong>               
					{foreach from=$fotos key=k item=foto name=fotos}
					<a rel="lightbox-tour" title="{$item.name}" href="{$foto.file}">{$smarty.foreach.fotos.index+1}</a>&nbsp;&nbsp;&nbsp;
					{/foreach}
					</div>
					<script type="text/javascript">
						$(".product-gallery a").lightBox();
					</script>
					{/if}
				</div>
				<div class="product-description pull-left">
					<div class="product-producer"><a href="{raURL node=catalog method=brand prms=$item.producer_id}">{$item.producer_id_name}</a> ({$item.producer_id_country})</div>
					{if $item.discount_price != '0.00'}
					<div class="product-price-no">{$item.price} руб.</div>
					<div class="product-price">Сейчас <span id="price_{$item.id}">{$item.discount_price}</span> руб.</div>
					{else}
					<div class="product-price"><span id="price_{$item.id}">{$item.price}</span> руб.</div>
					{/if}
					<input type="hidden" id="amount_{$item.id}" value="1">
					<a class="btn btn-warning btn-large" href="javascript:addCartItem({$item.id})">Купить</a>
					{if count($prices)}
					<div class="product-sizes">
					<h5>Размерный ряд</h5> 
					<select id="product_price_{$item.id}" onchange="setPrice({$item.id})">
					<option rel="{if $item.discount_price == '0.00'}{$item.price}{else}{$item.discount_price}{/if}" value="0">...</option>
					{foreach from=$prices item=price}
					<option rel="{$price.price}" value="{$price.id}">Размер: {$price.size_id_name}{if $price.color_id}, цвет: {$price.color_id_name}{/if} - {$price.price|number_format:2:',':' '} руб.</option>
					{/foreach}
					</select>
					</div>
					{else}
					<input type="hidden" value="0" id="product_price_{$item.id}">                        
					{/if}
					<div class="product-exists">{if $item.is_exist}<img src="/img/vnalich.png">{else}<img src="/img/zakaz.png">{/if}</div>
					{if $item.category_id_is_size}
					<div class="product-sizes-link"><a href="/sizes-table.htm" target="_blank">Таблицы размеров</a></div>
					{/if}
					<div class="product-text">{$item.description}</div>
					{if $item.discount_description}
					<div class="product-text">{$item.discount_description}</div>
					<div class="fb-like" style="display: inline-block;margin:10px 5px;" data-href="http://{$smarty.server.SERVER_NAME}{raURL node=catalog method=stuff prms=$item.id}" data-send="false" data-layout="button_count" data-width="100" data-show-faces="true"></div>
					<div style="display: inline-block;" id="vk_like"></div>
					<script type="text/javascript">initVKLike()</script>
					{/if}
				</div>
				<div class="clearfix"></div>
			</td>
		</tr>
	</table>
	{if count($articles)}
	<h4>Полезные статьи</h4>
	<div class="product-articles"> 
	{foreach from=$articles item=article} 
	<a href="{raURL node=articles method=read prms=$article.id}">{$article.name}</a><br>
	{/foreach} 
	</div>
	{/if}
	{raMethod ref=/catalog/hit.htm}
{else}
	{raException}		
{/if}