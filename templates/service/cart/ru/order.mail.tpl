<p>������������, {$smarty.session.deliveryPerson}.</p>
<p>����� ������ ������: <b>{$order_number}</b>.</p>
<p><b>������ ������:</b></p>
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
<td>�����</td>
<td>���-��</td>
<td class="price">����</td>
<td></td>
</tr>
{foreach from=$smarty.session.cart item=aCartItem}
<tr>
<td>
<p>[{$aCartItem.stuff.id}] {$aCartItem.stuff.name} {if isset($aCartItem.priceEntity.id)}(������� ����������:{$aCartItem.priceEntity.size_id_name} - {$aCartItem.priceEntity.color_id_name}){/if}<br>
�������������: {$aCartItem.stuff.producer_id_name}
</p>
</td>
<td>{$aCartItem.counter}</td>
<td class="price">{$aCartItem.price}<span>&nbsp;���.</span></td>
<td></td>
</tr>
{/foreach}
<tr class="lastRow">
<td colspan="2">��������� ������:&nbsp;</td>
<td><span>{$smarty.session.summa}</span><span class="rub">&nbsp;���.</span></td>
<td></td>
</tr>
<tr><td></td></tr>
</tbody>
</table>
</div>
<p><b>��������� ������:</b></p>
<p>��������� ������: {$sDeliveryType}, {$smarty.session.deliveryAddress}
<br>���������� ����: {$smarty.session.deliveryPerson}
<br>�������: <span class="wmi-callto">{$smarty.session.deliveryPhone}</span>
{if $smarty.session.deliveryPhoneAdd}<br>���. �������: <span class="wmi-callto">{$smarty.session.deliveryPhoneAdd}</span>{/if}
{if $smarty.post.deliveryComment}<br>����������� � ������: {$smarty.post.deliveryComment}</span>{/if}
</p>
<br>
{if $smarty.session.payType == 2}<p>����������, <a target="_blank" href="http://www.colors-life.ru/notice.php?order={$order_number-$base_number}">������������</a> ����� ���������.</p>{/if}
<p>��������� ������ �����  ���������� � <a target="_blank" href="http://www.colors-life.ru/cabinet/">������ ��������</a>.</p>
<p>�������������� ���������� �� ������ �������� �� �������� 8 (495) 580-21-68</p>
<p>--<br>������� �� ������� � ����� �����!</p>
<p>
<br><br>��� ������ ���������� �������� �������. �� ��������� �� ��� ������.
�� ������ ������ ������� ����� ����� �������� ����� - <a target="_blank" href="http://www.colors-life.ru/feedback.htm">http://www.colors-life.ru/feedback.htm</a>.
</p>