{raItem var=cat table=catalog_category query=$param0}	
<h1>{$cat.title}</h1>
<div class="cat-description">{$cat.description}</div>
{if $param1 != 'sort' && $param1 != 'price' && $param1 != 'name'}
{assign var=param1 value=sort}
{/if}
{if !$param2}
{assign var=param2 value="0"}
{else}
{assign var=param2_where value=" AND producer_id=`$param2`"}
{/if}
{if $smarty.get.rtt}
{if $smarty.get.rtt > 100 || $smarty.get.rtt < 10}
{assign var=rtt value=1000}
{else}
{assign var=rtt value=$smarty.get.rtt}
{/if}
{else}
{assign var=rtt value=10}
{/if}
{if $smarty.get.page}
{assign var=page value=$smarty.get.page}
{else}
{assign var=page value=1}
{/if}

{raPaginator var=paginator table=catalog_product query="publish=1 AND category_id=`$param0` `$param2_where`" pref="`$ref``$methodName`.`$param0`.`$param1`.`$param2`.htm?page=###&rtt=`$rtt`" per_page=$rtt page=$page tpl=public}
{raItems var=items table=catalog_product query="category_id=`$cat.id` AND publish=1 `$param2_where`" limit=$paginator->limit sort="is_exist DESC,`$param1`"}
<table class="stuff-selector" width="100%" cellpadding="5" cellspacing="0" border="0">
<tr>
<td width="40%">Сортировать по: {if $param1 != 'price' && $param1 != 'name'} 
	<a href="{raURL node=catalog method=$methodNname prms="`$param0`.price"}">цене</a> 
	<a href="{raURL node=catalog method=$methodNname prms="`$param0`.name"}">названию</a> 
	{elseif $param1 == 'price'} <span>цене</span> 
	<a href="{raURL node=catalog method=$methodNname prms="`$param0`.name"}">названию</a> 
	{else} <a href="{raURL node=catalog method=$methodNname prms="`$param0`.price"}">цене</a> 
	<span>названию</span> {/if}</td>
<td width="60%" align="right">Показать товары: 
	<a href="{raURL node=catalog method=index prms=$param0}">таблицей</a> 
	<span>списком</span>
по <select name="cpage" onChange="setCatalogRTT(this, {$rtt}, {$page})">
		<option value="10"{if $rtt == 10} selected{/if}>10</option>
		<option value="20"{if $rtt > 10 && $rtt <= 20} selected{/if}>20</option>
		<option value="50"{if $rtt > 20 && $rtt <= 50} selected{/if}>50</option>
		<option value="100"{if $rtt > 50 && $rtt <= 100} selected{/if}>100</option>
		<option value="1000"{if $rtt > 100 || $rtt < 10} selected{/if}>Все</option>
		</select>
	на страницу
</td>
</tr>
</table>
{if is_object($paginator)}{$paginator->render()}{/if}
{foreach from=$items item=item2}
<table class="stuff-table" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td><table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td height="100%"><table style="height:100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
				<td><img src="/img/stuff_lt.gif"></td>
				</tr>
				<tr>
				<td height="100%" style="background:url('/img/stuff_l2.gif') no-repeat left bottom;"></td>
				</tr>
			</table></td>
			<td width="100%"><table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
				<td class="stuff-title"><a class="screenshot" href="{raURL node=catalog method=stuff prms=$item2.id}" rel="{$item2.small_imagenew}">{$item2.name}</a>
				{raItems var=prices table=catalog_price query="product_id=`$item2.id` AND publish=1" sort="sort,size_id"}
				{if count($prices)}
				&nbsp;&nbsp;<select name="stuff_price_{$item2.id}" id="stuff_price_{$item2.id}" onchange="setPrice({$item2.id})">
				<option rel="{if $item.discount_price == '0.00'}{$item.price}{else}{$item.discount_price}{/if}" value="0">...</option>
				{foreach from=$prices item=price}
				<option rel="{$price.price}" value="{$price.id}">{$price.size_id_name} {if $price.color_id}- {$price.color_id_name}{/if} - {$price.price} руб.</option>
				{/foreach}
				{else}
				<input type="hidden" value="0" name="stuff_price_{$item2.id}" id="stuff_price_{$item2.id}">
				{/if}
				</td>
				<td style="background:url('/img/price_cart_bg2_left.gif') left top no-repeat;"><img src="/img/0.gif" width="4" height="1"></td>
				<td class="stuff-description2"><table width="100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td><table class="stuff-cart-list" width="100%" cellpadding="0" cellspacing="0">
							<tr>
							<td colspan="3"><img src="/img/0.gif" width="274" height="1" border="0"></td>
							</tr>
							<tr>
								<td style="white-space:nowrap;text-align:right;padding-right: 5px;"><div class="stuff-price"><span id="price_{$item2.id}">{if $item2.discount_price != '0.00'}{$item2.discount_price}{else}{$item2.price}{/if}</span> руб.</div></td>
								<td style="white-space:nowrap;"> Кол-во
								<input type="hidden" value="0" name="stuff_price_{$item2.id}" id="stuff_price_{$item2.id}">
								<input type="text" name="amount_{$item2.id}" id="amount_{$item2.id}" style="width:30px;" value="1">
								<a href="javascript:addCartItem({$item2.id})">Купить</a></td>
								<td><a href="javascript:addCartItem({$item2.id})"><img src="/img/cart0.gif" style="margin:0;" border="0"></a></td>
							</tr>
							</table>
						</td>
					</tr>
					</table></td>
				<td height="100%"><table width="100%" style="height:100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td><img src="/img/price_cart_bg2_right_top.gif"></td>
					</tr>
					<tr>
						<td height="100%" style="background:url('/img/price_cart_bg2_right.gif') bottom right no-repeat;"><img src="/img/0.gif" width="11" height="1"></td>
					</tr>
					</table></td>
				</tr>
			</table></td>
		</tr>
		</table></td>
	</tr>
</table>
<br />
{/foreach}           
{if is_object($paginator)}{$paginator->render()}{/if}
            