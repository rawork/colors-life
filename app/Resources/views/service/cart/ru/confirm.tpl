{if count($aItems)}
<h1>
<a href="/cart/">Уточнение заказа</a> &rarr;
{if !$auth->user}<a href="/cart/authorize.htm">Авторизация</a> &rarr;{/if}
<a href="/cart/detail.htm">Параметры заказа</a> &rarr;
Подтверждение заказа
</h1>
<br>
<form name="frmCart" id="frmCart" method="post">
<input type="hidden" name="submited" value="1"> 
<table class="cart-order-stuff" width="100%" cellpadding="0" cellspacing="0" border="0">
<colgroup>
 <col width="80%">
 <col width="10%">
 <col width="10%">
</colgroup>
<tbody>
<tr>
	<th>Наименование</th>
	<th>Кол-во</th>
	<th>Цена</th>
</tr>	
{foreach from=$aItems item=aItem}
<tr>
	<td><a target="_blank" href="{raURL node=$aItem.stuff.dir_id_name method=stuff prms=$aItem.stuff.id}">{$aItem.stuff.name}</a>
	{if $aItem.priceEntity.id} 
	<div class="stuff-sizes">Вариант исполнения: {$aItem.priceEntity.size_id_name} - {$aItem.priceEntity.color_id_name}</div>
	{/if}
	</td>
	<td class="quantity">{$aItem.counter}</td>
	<td class="price">{$aItem.price|number_format:2:',':' '} руб.</td>
</tr>
{/foreach}
</tbody>
</table>
<div class="cart-total">Всего {$smarty.session.number} товара(ов) на сумму: <span>{$list_total|number_format:2:',':' '} руб.</span></div>
{if $discount > 0}
<div class="cart-total">Скидка: <strong>{$discount}%</strong></div>
<div class="cart-total">Сумма с учетом скидки: <span>{$totalPriceDiscount} руб.</span></div>
{/if}
{if count($gifts)}<h3><span>Подарки:</span></h3>
<table class="cart-order-stuff" width="100%" cellpadding="0" cellspacing="0" border="0">
{foreach from=$gifts item=gift}
<tr>
	<td><a target="_blank" href="{raURL node=catalog method=stuff prms=$gift.gift_id}">{$gift.gift_id_name}</a></td>
</tr>
{/foreach}
<tr>
	<td>&nbsp;</td>
</tr>
</table>
{/if}
<h3>Параметры заказа</h3>			
<table class="forms" style="width:80%">
<colgroup>
 <col width="30%">
 <col>
</colgroup>
<tbody>
   <tr><td>Получатель:</td><td>{$smarty.session.deliveryPerson}</td></tr>
   <tr><td>Эл. почта:</td><td>{$smarty.session.deliveryEmail}</td></tr>
   <tr><td>Телефон:</td><td>{$smarty.session.deliveryPhone}</td></tr>
   {if $smarty.session.deliveryPhoneAdd}<tr><td>Дополнительный телефон:</td><td>{$smarty.session.deliveryPhoneAdd}</td></tr>{/if}
   <tr><td>Способ оплаты:</td><td>{$sPayType}</td></tr>
   <tr><td>Получение товара:</td><td>{$sDeliveryType}</td></tr>
   <tr><td>Адрес доставки:</td><td>{$smarty.session.deliveryAddress}</td></tr>
   <tr><td>Комментарий к заказу:</td><td><textarea name="deliveryComment" style="width:500px;height:70px" ></textarea></td></tr>
</tbody>
</table>   
            
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td width="50%">&nbsp;</td>
		<td width="50%" align="right"><input type="submit" value="Подтверждаю заказ" /></td>
	</tr>
</table>
</form>
{else}
<div class="lst-item2">
<h1>В вашей корзине нет товаров</h1>
{raInclude var=delivery}
</div>
{/if}