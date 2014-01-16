{$cabinetMenu}
{if $items}
<!--<form class="form-inline" name="frmOrderSearch" action="" method="post">
c <input class="input-small" type="text" readonly="readonly" value="" name="datefrom" id="datefrom">
&nbsp;<img id="trigger_datefrom" style="cursor: pointer; border: 0px none;" src="/bundles/admin/img/calendar.gif">
по <input class="input-small" type="text" readonly="readonly" value="" name="datetill" id="datetill">
&nbsp;<img id="trigger_datetill" style="cursor: pointer; border: 0px none;" src="/bundles/admin/img/calendar.gif">
&nbsp;&nbsp;номер: <input type="text" class="input-small" name="order_num" /> <input type="submit" class="btn" value="Искать" />
</form> 
<script type="text/javascript">
	setupCalendar('datefrom', '%H:%M:00');
	setupCalendar('datetill', '%H:%M:00');
</script>
-->
{foreach from=$items item=order}
<h6>Заказ №{$order.id} от {$order.created|fdate}, <span>сумма {$order.summa} руб., cтатус <strong>{$order.status}</strong>
{if $order.pay_type == 'Квитанция банка'} &mdash; <a href="{raURL node=cabinet method=notice prms=$order.id+100000}" target="_blank">Распечатать квитанцию</a></span>{/if}
</h6>
<table class="table table-striped table-bordered">
	<thead>
	<tr>
		<th width="80%">Наименование</th>	
		<th width="7%"><span class="pull-right">Кол-во</span></th>
		<th width="12%"><span class="pull-right">Сумма</span></th>
	</tr>
	</thead>
	{foreach from=$order.products item=product}
	<tr>
		<td><a target="_blank" href="{raURL node=catalog method=stuff params=$product.id}">{$product.name}</a></td>
		<td><span class="pull-right">{$product.quantity}</span></td>
		<td><span class="pull-right">{$product.price}</span></td>
	</tr>
	{/foreach}
</table>
<br>
{/foreach}

{else}
У Вас нет оформленных заказов	
{/if}