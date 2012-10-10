{if !$param0}{raException}{/if}
{raItem var=item table=catalog_product query=$param0}
{if $item.publish}
	<div id="fb-root"></div>
	<script>initFB()</script>
	<script type="text/javascript" src="//vk.com/js/api/openapi.js?56"></script>
	<script type="text/javascript">initVK();</script>
	{raSetVar var=title value=$item.name}
	<h1>{$item.name}</h1>
	<table class="stuff-table" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td style="padding:8px;"><table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
			<td><img src="/img/stuff_lt.gif"></td>
			<td class="stuff-t"></td>
			<td><img src="/img/stuff_rt.gif"></td>
			</tr>
			<tr>
			<td class="stuff-l"></td>
			<td class="stuff-content"><table style="height:100%" width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="stuff-image"> 
						{if $item.big_image}
						<a href="{$item.big_image}" class="jqzoom" title="{$item.name}"><img src="{$item.image}"  title="{$item.name}" style="border: 0px solid #666;"></a>
						<script type="text/javascript">
							bindZoom();
						</script>
						{elseif $item.image}
						<img width="260" src="{$item.image}">
						{else}
						<img src="/img/noimage_small.jpg">
						{/if} 
					{raItems var=fotos nquery="SELECT * FROM system_files WHERE table_name='catalog_product' AND entity_id=`$item.id` ORDER BY created"}
					{if count($fotos)}
					<div class="stuff-extra-image-links">Галерея:               
					{foreach from=$fotos key=k item=foto}
					<a rel="lightbox-tour" title="{$item.name}" href="{$foto.file}">{$k+1}</a>&nbsp;&nbsp;&nbsp;
					{/foreach}
					</div>
					<script type="text/javascript">
					$(".stuff-extra-image-links a").lightBox();
				</script>
					{/if}
					</td>
					<td class="stuff-description"><table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr> 
						{raItem var=cat0 table=catalog_category query=$item.category_id_root_id}
						<td height="100%" valign="top"><div class="stuff-cat" style="background-image:url('{$cat0.logo}');"><a href="{raURL node=catalog method=index prms=$item.category_id}">{$item.category_id_title}</a></div>
							<div class="stuff-producer"><a href="{raURL node=catalog method=brand prms=$item.producer_id}">{$item.producer_id_name}</a> ({$item.producer_id_country})</div>
							<div class="fb-like" style="display: inline-block;margin:10px 5px;" data-href="http://{$smarty.server.SERVER_NAME}{raURL node=$item.node_id_name method=read prms=$item.id}" data-send="false" data-layout="button_count" data-width="100" data-show-faces="true"></div>
							<div style="display: inline-block;" id="vk_like"></div>
							<script type="text/javascript">initVKLike()</script>
							<div class="stuff-description">{$item.description}</div>
							{if $item.discount_description}
							<div class="stuff-description">{$item.discount_description}</div>
							{/if}
							<div class="stuff-exist">{if $item.is_exist}<img src="/img/vnalich.png">{else}<img src="/img/zakaz.png">{/if}</div>
							{if !$item.is_exist}<div class="know-when">
							<a href="#" onclick="return showMailForm({$item.id})">Узнать, когда появится в продаже</a>
							<div id="mailblock{$item.id}"><input type="text" onkeypress="checkEmailForm({$item.id})" onkeyup="checkEmailForm({$item.id})" onblur="procBlurEmail(this, 'Электронная почта', {$item.id})" onfocus="procFocus(this, 'Электронная почта')" value="Электронная почта" class="width-200" name="email{$item.id}" id="email{$item.id}"> <input type="button" class="btnEmail" id="btnEmail{$item.id}" onclick="sendStuffExist({$item.id})" disabled="true" value="Оставить заявку" /></div>
							</div>{/if}
							{if $item.category_id_is_size}
							<div class="stuff-sizes-link"><a href="/sizes-table.htm" target="_blank">Таблицы размеров</a></div>
							{/if}
							{raItems var=prices table=catalog_price query="product_id=`$item.id` AND publish=1" sort="sort,size_id"}
							{if count($prices)}
							<div class="stuff-sizes">
							Размерный ряд:<br> 
							<select name="stuff_price_{$item.id}" id="stuff_price_{$item.id}" onchange="setPrice({$item.id})">
							<option rel="{if $item.discount_price == '0.00'}{$item.price}{else}{$item.discount_price}{/if}" value="0">...</option>
							{foreach from=$prices item=price}
							<option rel="{$price.price}" value="{$price.id}">{$price.size_id_name} {if $price.color_id}- {$price.color_id_name}{/if} - {$price.price} руб.</option>
							{/foreach} 
							</div>
							<br><br><br>

							{else}
							<input type="hidden" value="0" name="stuff_price_{$item.id}" id="stuff_price_{$item.id}">                        
							{/if}  </td>
						</tr>
						<tr>
						<td><table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td width="100%"></td>
								<td><img src="/img/0.gif" width="128" height="1" style="display:block;">
								<table class="stuff-cart" width="100%" cellpadding="0" cellspacing="0">
									<tr>
									<td colspan="2"> {if $item.discount_price != '0.00'}
										<div class="stuff-price-no"><span>{$item.price}</span> руб.</div>
										<div class="stuff-price"><span id="price_{$item.id}">{$item.discount_price}</span> руб.</div>
										{else}
										<div class="stuff-price"><span id="price_{$item.id}">{$item.price}</span> руб.</div>
										{/if} </td>
									</tr>
									<tr>
									<td width="100%"> Кол-во
										<input type="text" name="amount_{$item.id}" id="amount_{$item.id}" style="width:30px;" value="1">
										<a href="javascript:addCartItem({$item.id})">Купить</a></td>
									<td><a href="javascript:addCartItem({$item.id})"><img src="/img/cart0.gif" style="margin:0;" border="0"></a></td>
									</tr>
								</table></td>
							</tr>
							</table></td>
						</tr>
					</table></td>
				</tr>
				</table></td>
			<td class="stuff-r"></td>
			</tr>
			<tr>
			<td><img src="/img/stuff_lb.gif"></td>
			<td class="stuff-b"></td>
			<td><img src="/img/stuff_rb.gif"></td>
			</tr>
		</table></td>
	</tr>
	</table>
	{raItems var=items table=articles nquery="SELECT ar.* FROM article_products_articles st_ar JOIN article_article ar ON ar.id=st_ar.article_id WHERE st_ar.product_id=`$item.id` GROUP BY st_ar.article_id"}
	{if count($items)}
	<h3><span>Полезные статьи:</span></h3>
	<div class="stuff-articles"> 
	{foreach from=$items item=art} 
	<a href="{raURL node=articles method=read prms=$art.id}">{$art.name}</a><br>
	{/foreach} 
	</div>
	{/if}
	{raMethod ref=/catalog/hit.htm}
{else}
	{raException}		
{/if}