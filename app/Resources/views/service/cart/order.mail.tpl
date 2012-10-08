<p>Здравствуйте, {$deliveryPerson}.</p>
<p>Номер Вашего заказа: <b>{$orderNumber}</b>.</p>
<p><b>Состав заказа:</b></p>
<div class="tbl">
<table class="itemList">
<colgroup>
<col align="left" width="*">
<col align="center" width="100">
<col align="right" width="120">
<tbody>
<tr>
<td></td>
</tr>
</tbody>
</colgroup>
<tbody>
<tr class="firstRow">
<td>Товар</td>
<td>Кол-во</td>
<td class="price">Цена</td>
<td></td>
</tr>
{foreach from=$cart item=item}
<tr>
<td>
<p>[{$item.stuff.id}] {$item.stuff.name} {if isset($item.priceEntity.id)}(Вариант исполнения:{$item.priceEntity.size_id_name} - {$item.priceEntity.color_id_name}){/if}<br>
Производитель: {$item.stuff.producer_id_name}
</p>
</td>
<td>{$item.counter}</td>
<td class="price">{$item.price}<span>&nbsp;руб.</span></td>
<td></td>
</tr>
{/foreach}
<tr class="lastRow">
<td colspan="2">Стоимость заказа {if $discount}	с учетом скидки {$discount}%{/if}:&nbsp;</td>
<td> <span>
{if $discount}{$totalPriceDiscount}{else}{$totalPriceRus}{/if}</span><span class="rub">&nbsp;руб.</span>
</td>
<td></td>
</tr>
<tr><td></td></tr>
</tbody>
</table>
</div>
<p><b>Параметры заказа:</b></p>
<p>Получение товара: {$deliveryType.name}, {$deliveryAddress}
<br>Способ оплаты: {$payType.name}	
<br>Контактное лицо: {$deliveryPerson}
<br>Эл. почта: {$deliveryEmail}
<br>Телефон: <span class="wmi-callto">{$deliveryPhone}</span>
{if $deliveryPhoneAdd}<br>Доп. телефон: <span class="wmi-callto">{$deliveryPhoneAdd}</span>{/if}
{if $deliveryComment}<br>Комментарий к заказу: {$deliveryComment}</span>{/if}
</p>
<br>
{if $payType.id == 2}<p>Пожалуйста, <a target="_blank" href="http://colors-life.ru/notice/{$orderNumber}">распечатайте</a> бланк квитанции.</p>{/if}
{if $user}
<p>Состояние заказа можно  посмотреть в <a target="_blank" href="http://colors-life.ru/cabinet/">личном кабинете</a>.</p>
{/if}
<p>Дополнительную информацию Вы можете получить по телефону 8 (495) 580-21-68</p>
<p>--<br>Спасибо за покупку в Цвета жизни!</p>
<p>
<br><br>Это письмо отправлено почтовым роботом. Не отвечайте на это письмо.
Вы можете задать вопросы через форму <a target="_blank" href="http://colors-life.ru/feedback.htm">обратной связи</a>.
</p>