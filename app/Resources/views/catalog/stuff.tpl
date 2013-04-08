<div id="fb-root"></div>
<script>initFB()</script>
<script type="text/javascript" src="//vk.com/js/api/openapi.js?56"></script>
<script type="text/javascript">initVK();</script>
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
				<br>
				<div id="fb-root"></div>
				<script>initFB()</script>
				<script type="text/javascript" src="//vk.com/js/api/openapi.js?56"></script>
				<script type="text/javascript">initVK();</script>	
				<div class="fb-like" data-href="http://{$smarty.server.SERVER_NAME}{raURL node=catalog method=stuff prms=$item.id}" data-send="false" data-layout="button_count" data-width="100" data-show-faces="true"></div>
				<br>
				<div id="vk_like"></div>
				<script type="text/javascript">initVKLike()</script>
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
				<div class="product-sizes-link"><a href="{raURL node=sizes-table}" target="_blank">Таблицы размеров</a></div>
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
<h4>Комментарии</h4>
<!-- Put this div tag to the place, where the Comments block will be -->
<div id="vk_comments"></div>
<script type="text/javascript">
	initVKComment();
</script>
{raMethod path=Fuga:Public:Catalog:hit args="['id':`$item.id`]"}
