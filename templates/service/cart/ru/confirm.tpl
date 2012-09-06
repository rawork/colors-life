{if count($aItems)}
<h1>
<a href="/cart/">��������� ������</a> &rarr;
{if !$uauth->user}<a href="/cart/authorize.htm">�����������</a> &rarr;{/if}
<a href="/cart/detail.htm">��������� ������</a> &rarr;
������������� ������
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
	<th>������������</th>
	<th>���-��</th>
	<th>����</th>
</tr>	
{foreach from=$aItems item=aItem}
<tr>
	<td><a target="_blank" href="{raURL node=$aItem.stuff.dir_id_name method=stuff prms=$aItem.stuff.id}">{$aItem.stuff.name}</a>
	{if $aItem.priceEntity.id} 
	<div class="stuff-sizes">������� ����������: {$aItem.priceEntity.size_id_name} - {$aItem.priceEntity.color_id_name}</div>
	{/if}
	</td>
	<td class="quantity">{$aItem.counter}</td>
	<td class="price">{$aItem.price|number_format:2:',':' '} ���.</td>
</tr>
{/foreach}
</tbody>
</table>
<div class="cart-total">����� {$smarty.session.number} ������(��) �� �����: <span>{$list_total|number_format:2:',':' '} ���.</span></div>
{if is_array($discount)}<div class="cart-total">��� ����� ������ �� {$discount.sum_min|number_format:2:',':' '} �� {$discount.sum_max|number_format:2:',':' '} ���. ��������� ������ <b style="color:#EE2A2A;font-size:14px;">{$discount.discount} %</b></div>
<div class="cart-total">����� � ������ ������: <span>{$list_total-$list_total*$discount.discount/100|number_format:2:',':' '} ���.</span></div>{/if}
{if count($gifts)}<h3><span>�������:</span></h3>
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
<h3>��������� ������</h3>			
<table class="forms" style="width:80%">
<colgroup>
 <col width="30%">
 <col>
</colgroup>
<tbody>
   <tr><td>����������:</td><td>{$smarty.session.deliveryPerson}</td></tr>
   <tr><td>��. �����:</td><td>{$smarty.session.deliveryEmail}</td></tr>
   <tr><td>�������:</td><td>{$smarty.session.deliveryPhone}</td></tr>
   {if $smarty.session.deliveryPhoneAdd}<tr><td>�������������� �������:</td><td>{$smarty.session.deliveryPhoneAdd}</td></tr>{/if}
   <tr><td>������ ������:</td><td>{$sPayType}</td></tr>
   <tr><td>��������� ������:</td><td>{$sDeliveryType}</td></tr>
   <tr><td>����� ��������:</td><td>{$smarty.session.deliveryAddress}</td></tr>
   <tr><td>����������� � ������:</td><td><textarea name="deliveryComment" style="width:500px;height:70px" ></textarea></td></tr>
</tbody>
</table>   
            
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td width="50%">&nbsp;</td>
		<td width="50%" align="right"><input type="submit" value="����������� �����" /></td>
	</tr>
</table>
</form>
{else}
<div class="lst-item2">
<h1>� ����� ������� ��� �������</h1>
{raInclude var=delivery}
</div>
{/if}