{raSetVar var=title value="Заказы"}
<h1>Заказы</h1>
{$cabinetMenu}
<form class="form-inline" name="frmOrderSearch" action="" method="post">
c <input class="input-small" type="text" readonly="readonly" value="" name="datefrom" id="datefrom">
&nbsp;<img id="trigger_datefrom" style="cursor: pointer; border: 0px none;" src="/bundles/admin/img/calendar.gif">
по <input class="input-small" type="text" readonly="readonly" value="" name="datetill" id="datetill">
&nbsp;<img id="trigger_datetill" style="cursor: pointer; border: 0px none;" src="/bundles/admin/img/calendar.gif">
&nbsp;&nbsp;номер: <input type="text" class="input-small" name="order_num" /> <input type="submit" class="btn" value="Искать" />
</form>
<table class="table table-striped table-condensed">
	<thead>
	<tr>
		<th>Номер</th>	
		<th>Дата</th>
		<th>Статус</th>
		<th>Кол-во товаров</th>
		<th>Сумма</th>
	</tr>
	</thead>
	{raItems var=orders table=cart_order query="user_id=`$user.id`"}
	{foreach from=$orders item=order}
	<tr>
		<td><a href="javascript:showOrderDetail({$order.id})">{$order.id+100000}</a></td>
		<td>{$order.created|fdate}</td>
		<td>{$order.status}</td>
		<td>{$order.counter}</td>
		<td>{$order.summa} руб.
		{if $order.pay_type == 'Квитанция банка'}<div class="cabinet-notice"><a href="/notice/{$order.id+100000}" target="_blank">Распечатать квитанцию</a></div>{/if}
		</td>
	</tr>
	{/foreach}
</table>
<script type="text/javascript">
	setupCalendar('datefrom', '%H:%M:00');
	setupCalendar('datetill', '%H:%M:00');
</script>