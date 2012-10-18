{if count($items)}
<h1> 
	Уточнение заказа &rarr;
	{if !$user.id}<span>Авторизация</span> &rarr;{/if}
	<span>Оплата и доставка</span> &rarr; 
	<span>Подтверждение заказа</span> 
</h1>
<br>
<form name="frmCart" id="frmCart" method="post">
	<input type="hidden" name="recalculate" value="1">
	{foreach from=$items key=guid item=item}
	<div class="cart-item" id="stuff_{$guid}">
	<div class="item-title">
		<a href="{raURL node=catalog method=stuff prms=$item.stuff.id}">{$item.stuff.name}</a> {raItems var=prices table=catalog_price query="product_id=`$item.stuff.id` AND publish=1" sort="sort,size_id"}
		{if count($prices)}
		&nbsp;&nbsp;<br>
		<select style="width:200px;" name="price_{$guid}" id="price_{$guid}">
		<option value="0">Стандартное исполнение</option>
		{foreach from=$prices item=price}
		<option{if $price.id == $item.priceEntity.id} selected{/if} value="{$price.id}">
		{$price.size_id_name} {if $price.color_id}- {$price.color_id_name}{/if} - {$price.price} руб.
		</option>
		{/foreach}
		</select>
		{/if}
	</div>
	<div class="price">
		<span>{$item.price|number_format:2:',':' '}</span> руб.
		<i>Кол-во</i>
		<input type="text" style="width:30px;" value="{$item.counter}" name="amount_{$guid}" id="amount_{$guid}">
		<a href="javascript:deleteCartItem('{$guid}')">×</a></div>
	</div>	
	{/foreach}	
<div class="cart-total">Всего <strong id="totalQuantity">{$smarty.session.number}</strong> товара(ов) на сумму: <span id="totalSum">{$totalPriceRus} руб.</span></div>
{if $discount > 0}
<div class="cart-total">Скидка: <strong id="discount">{$discount}%</strong></div>
<div class="cart-total">Сумма с учетом скидки: <span id="totalSumDiscount">{$totalPriceDiscount} руб.</span></div>
{/if}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td width="50%"></td>
		<td width="50%" align="right">
		<input type="submit" value="Сохранить изменения" />
		<input type="button" onclick="window.location = '{if $user.id}/cart/detail.htm{else}/cart/authorize.htm{/if}'" value="Продолжить" />
		</td>
	</tr>
</table>
</form>
<br>
{foreach from=$gifts item=gift}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td height="100%"><table style="height:100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td><img src="/img/stuff_lt_gift.gif"></td>
				</tr>
				<tr>
					<td height="100%" style="background:url('/img/stuff_l_gift.gif') no-repeat left bottom;"></td>
				</tr>
			</table>
		</td>
		<td rowspan="2" class="stuff-content2">
			<table style="height:100%" width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="stuff-image2"><a href="{raURL node=catalog method=stuff prms=$gift.gift_id}">{if $gift.gift_id_small_imagenew}<img src="{$gift.gift_id_small_imagenew}">{/if}</a></td>
					<td class="stuff-description3">
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr> {raItem var=item table=catalog_product query=$gift.gift_id}
								{if $item.category_id_parent_id == 0}
								{raItem var=cat0 table=catalog_category query=$item.category_id}
								{else}
								{raItem var=cat0 table=catalog_category query=$item.category_id_parent_id}
								{/if}
								<td height="100%" valign="top"><div class="stuff-cat" style="background-image:url('{$cat0.logo}');"><a href="{raURL node=catalog method=index prms=$item.category_id}">{$item.category_id_name}</a></div>
									<div class="stuff-name"><a href="{raURL node=catalog method=stuff prms=$gift.gift_id}">{$gift.gift_id_name}</a></div>
									<div class="stuff-description"></div></td>
							</tr>
							<tr>
								<td>
									<table width="100%" cellpadding="0" cellspacing="0" border="0">
										<tr>
											<td width="100%"></td>
											<td><img src="/img/0.gif" width="128" height="1" border="0">
												<div class="stuff-price-gift"><span>Подарок</span></div></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
		<td height="100%"><table style="height:100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td><img src="/img/stuff_rt_gift.gif"></td>
				</tr>
				<tr>
					<td height="100%" style="background:url('/img/stuff_r_gift.gif') no-repeat left bottom;"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br>
<!--Трэкер "Корзина"-->

<script>document.write('<img src="http://mixmarket.biz/tr.plx?e=3779415&r='+escape(document.referrer)+'&t='+(new Date()).getTime()+'" width="1" height="1"/>');</script>

<!--Трэкер "Корзина"-->
{/foreach}

{else}
<div class="lst-item2">
	<h1>В вашей корзине нет товаров</h1>
	{raInclude var=delivery} 
</div>
{/if}