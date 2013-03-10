{if count($items)}
<h4>
<a href="{raURL node=cart}">Уточнение заказа</a> &rarr;
{if !$user.id}<a href="{raURL node=cart method=authorize}">Авторизация</a> &rarr; {/if}
<a href="{raURL node=cart method=detail}">Параметры заказа</a> &rarr;
Подтверждение заказа
</h4>
<br>
<form name="frmCart" id="frmCart" method="post">
<table class="table table-condensed">
<thead>
<tr>
	<th>Наименование</th>
	<th>Кол-во</th>
	<th>Цена, руб.</th>
</tr>
</thead>
{foreach from=$items item=item}
<tr>
	<td><a target="_blank" href="{raURL node=catalog method=stuff prms=$item.stuff.id}">{$item.stuff.name}</a>
	{if $item.priceEntity.id} 
	<div class="stuff-sizes"><strong>Размер:</strong> {$item.priceEntity.size_id_name}, <strong>цвет:</strong> {$item.priceEntity.color_id_name}</div>
	{/if}
	</td>
	<td class="quantity">{$item.counter}</td>
	<td class="price">{$item.price}</td>
</tr>
{/foreach}
</table>
<div class="cart-total">Всего {$smarty.session.number} товар{$wordEnd} на сумму: <span>{$totalPriceRus} руб.</span></div>
{if $discount > 0}
<div class="cart-total">Скидка: <strong>{$discount}%</strong></div>
<div class="cart-total">Сумма с учетом скидки: <strong>{$totalPriceDiscount} руб.</strong></div>
{/if}
<h5>Параметры заказа</h5>
<dl class="dl-horizontal">
  <dt>Получатель:</dt>
  <dd>{$smarty.session.deliveryPerson}</dd>
</dl>
<dl class="dl-horizontal">
  <dt>Эл. почта:</dt>
  <dd>{$smarty.session.deliveryEmail}</dd>
</dl>
<dl class="dl-horizontal">
  <dt>Телефон:</dt>
  <dd>{$smarty.session.deliveryPhone}</dd>
</dl>
{if $smarty.session.deliveryPhoneAdd}
<dl class="dl-horizontal">
  <dt>Дополнительный телефон:</dt>
  <dd>{$smarty.session.deliveryPhoneAdd}</dd>
</dl>{/if}
<dl class="dl-horizontal">
  <dt>Способ оплаты:</dt>
  <dd>{$payType.name}</dd>
</dl>
<dl class="dl-horizontal">
  <dt>Получение товара:</dt>
  <dd>{$deliveryType.name}</dd>
</dl>
<dl class="dl-horizontal">
  <dt>Адрес доставки:</dt>
  <dd>{$smarty.session.deliveryAddress}</dd>
</dl>
<dl class="dl-horizontal">
  <dt>Комментарий к заказу:</dt>
  <dd><textarea name="deliveryComment" rows="5"></textarea></dd>
</dl>
<div class="pull-right"><input type="submit" class="btn btn-large btn-warning" value="Подтверждаю заказ" /></div>
</form>
{else}
<h4>В вашей корзине нет товаров</h4>
{raMethod path=Fuga:Public:Common:block args='["name":"delivery"]'} 
{/if}