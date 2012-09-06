<p>Здравствуйте, {$smarty.session.deliveryPerson}.</p>
<p>Номер Вашего заказа: <b>{$order_number}</b>.</p>
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
{foreach from=$smarty.session.cart item=aCartItem}
<tr>
<td>
<p>[{$aCartItem.stuff.id}] {$aCartItem.stuff.name} {if isset($aCartItem.priceEntity.id)}(Вариант исполнения:{$aCartItem.priceEntity.size_id_name} - {$aCartItem.priceEntity.color_id_name}){/if}<br>
Производитель: {$aCartItem.stuff.producer_id_name}
</p>
</td>
<td>{$aCartItem.counter}</td>
<td class="price">{$aCartItem.price}<span>&nbsp;руб.</span></td>
<td></td>
</tr>
{/foreach}
<tr class="lastRow">
<td colspan="2">Стоимость заказа:&nbsp;</td>
<td><span>{$smarty.session.summa}</span><span class="rub">&nbsp;руб.</span></td>
<td></td>
</tr>
<tr><td></td></tr>
</tbody>
</table>
</div>
<p><b>Параметры заказа:</b></p>
<p>Получение товара: {$sDeliveryType}, {$smarty.session.deliveryAddress}
<br>Контактное лицо: {$smarty.session.deliveryPerson}
<br>Телефон: <span class="wmi-callto">{$smarty.session.deliveryPhone}</span>
{if $smarty.session.deliveryPhoneAdd}<br>Доп. телефон: <span class="wmi-callto">{$smarty.session.deliveryPhoneAdd}</span>{/if}
{if $smarty.post.deliveryComment}<br>Комментарий к заказу: {$smarty.post.deliveryComment}</span>{/if}
</p>
<br>
{if $smarty.session.payType == 2}<p>Пожалуйста, <a target="_blank" href="http://www.colors-life.ru/notice.php?order={$order_number-$base_number}">распечатайте</a> бланк квитанции.</p>{/if}
<p>Состояние заказа можно  посмотреть в <a target="_blank" href="http://www.colors-life.ru/cabinet/">личном кабинете</a>.</p>
<p>Дополнительную информацию Вы можете получить по телефону 8 (495) 580-21-68</p>
<p>--<br>Спасибо за покупку в Цвета жизни!</p>
<p>
<br><br>Это письмо отправлено почтовым роботом. Не отвечайте на это письмо.
Вы можете задать вопросы через форму обратной связи - <a target="_blank" href="http://www.colors-life.ru/feedback.htm">http://www.colors-life.ru/feedback.htm</a>.
</p>