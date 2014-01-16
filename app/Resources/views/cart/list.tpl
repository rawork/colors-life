{if count($items)}
<h4> 
	Уточнение заказа &rarr;
	{if !$user.id}<span>Авторизация</span> &rarr;{/if}
	<span>Оплата и доставка</span> &rarr; 
	<span>Подтверждение заказа</span> 
</h4>
<br>
<form name="frmCart" id="frmCart" method="post">
<input type="hidden" name="recalculate" value="1">
<table class="table table-condensed">
	<thead>
	<tr>	
		<th>Товар</th>
		<th>Цена</th>
		<th>Количество</th>
		<th><i class="icon-align-justify"></i></th>
	</tr>
	</thead>
	{foreach from=$items key=guid item=item}
	<tr id="stuff_{$guid}">
		<td>
			<a href="{raURL node=catalog method=stuff prms=$item.stuff.id}{if $item.priceEntity}/{$item.priceEntity.id}{/if}">{$item.stuff.name}, Арт. {$item.stuff.articul}</a> 
			{if $item.priceEntity}
			&nbsp;&nbsp;<br>
			{$item.priceEntity.size_id_name}{if $item.priceEntity.color_id}, {$item.priceEntity.color_id_name}{/if}{if $item.priceEntity.articul}, Арт. {$item.priceEntity.articul}{/if}
			{/if}
		</td>
		<td>
			<span>{$item.price}</span>
		</td>
		<td>
			<input type="text" style="width:30px;" value="{$item.counter}" name="amount_{$guid}" id="amount_{$guid}">
		</td>
		<td>
			<a title="Удалить" class="delete" href="javascript:deleteCartItem('{$guid}')">×</a>
		</td>
	</tr>	
	{/foreach}	
</table>	
<div class="cart-total">Всего <span id="totalQuantity">{$smarty.session.number}</span> товар{$wordEnd} на сумму: <span id="totalSum">{$totalPriceRus} руб.</span></div>
{if $discount > 0}
<div class="cart-total">Скидка: <strong id="discount">{$discount}%</strong></div>
<div class="cart-total">Сумма с учетом скидки: <strong id="totalSumDiscount">{$totalPriceDiscount} руб.</strong></div>
{/if}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td width="50%"></td>
		<td width="50%" align="right">
		<input type="submit" class="btn btn-large" value="Сохранить изменения" />
		<input type="button" class="btn btn-warning btn-large" onclick="window.location = '{if $user.id}{raURL node=cart method=detail}{else}{raURL node=cart method=account}{/if}'" value="Продолжить" />
		</td>
	</tr>
</table>
</form>
<br>

<!--Трэкер "Корзина"-->
<script>document.write('<img src="http://mixmarket.biz/tr.plx?e=3779415&r='+escape(document.referrer)+'&t='+(new Date()).getTime()+'" width="1" height="1"/>');</script>
<!--Трэкер "Корзина"-->
{else}
<h4>В вашей корзине нет товаров</h4>
{raMethod path=Fuga:Public:Common:block args='["name":"delivery"]'}
{/if}