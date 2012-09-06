<h2>Заказы</h2>
<div class="search-panel">
<form name="frmOrderSearch" action="" method="post">
c <input readonly="readonly" value="" name="datefrom" id="datefrom">
&nbsp;<img id="trigger_datefrom" style="cursor: pointer; border: 0px none;" src="/admin/themes/_default/img/calendar.gif">
<script type="text/javascript">setupCalendar('datefrom', '%H:%M:00')</script> 
по <input readonly="readonly" value="" name="datetill" id="datetill">
&nbsp;<img id="trigger_datetill" style="cursor: pointer; border: 0px none;" src="/admin/themes/_default/img/calendar.gif">
<script type="text/javascript">setupCalendar('datetill', '%H:%M:00')</script>
номер: <input type="text" name="order_num" /> <input type="submit" value="Искать" />
</form>
</div>
<table class="cabinet-orders" width="100%" cellpadding="5" cellspacing="0" border="0">
<tr>
<th>Дата</th>
<th>Номер</th>
<th>Статус</th>
<th>Кол-во товаров</th>
<th>Сумма</th>
</tr>
{raItems var=orders table=cart_order query="user_id=`$uauth->user.id`"}
{foreach from=$orders item=order}
<tr>
<td>{$order.credate|fdate}</td>
<td><a href="javascript:showOrderDetail({$order.id})">{$order.id+100000}</a></td>
<td>{$order.status}</td>
<td>{$order.counter}</td>
<td>{$order.summa} руб.
{if $order.pay_type == 'Квитанция банка'}<div class="cabinet-notice"><a href="/notice.php?order={$order.id+100000}" target="_blank">Распечатать квитанцию</a></div>{/if}
</td>
</tr>
{/foreach}
</table>
