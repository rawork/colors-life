<h1>
<a href="/cart/">��������� ������</a> &rarr;
{if !$uauth->user}<a href="/cart/authorize.htm">�����������</a> &rarr;{/if}
��������� ������ &rarr;
<span>������������� ������</span>
</h1>
<br>
          
<form name="mainForm" method="POST">
<input type="hidden" name="processDetail" value="1">
<!-- ���� ������ ������ -->
<table class="forms" width="100%">
<colgroup>
 <col width="30%">
 <col>
</colgroup>
<tbody>
<tr><td>��������� ������</td>
 <td style="vertical-align: top;">
  {foreach from=$aDeliveryTypes key=iKey item=aDeliveryType}
  <input onclick="setDeliveryType({$aDeliveryType.id})" value="{$aDeliveryType.id}" name="deliveryType"{if !$iKey} checked="checked"{/if} id="deliveryType{$aDeliveryType.id}" type="radio"> <label for="deliveryType{$aDeliveryType.id}">{$aDeliveryType.name}</label>
  {/foreach}
  {foreach from=$aDeliveryTypes key=iKey item=aDeliveryType}
  <p class="comment delivery-text" id="deliveryDescr{$aDeliveryType.id}"{if $iKey} style="display:none"{/if}>{$aDeliveryType.description}</p>
  {/foreach}
   </td>
</tr>
<tr><td>������ ������</td>
 <td>
  {foreach from=$aPayTypes key=iKey item=aPayType}
  <input onclick="setPayType({$aPayType.id})" value="{$aPayType.id}" name="payType"{if !$iKey} checked="true"{/if} id="payType{$aPayType.id}" type="radio"> <label for="payType{$aPayType.id}">{$aPayType.name}</label>
  {/foreach}
  <p class="cut" id="payDescr2" style="display:none">��� ������� ������� ������ �������� �������� ������ ���� ��� ������: ��������� �����.</p>
  </td>
</tr>
</tbody></table>
<!-- �����-->
<table class="forms" width="100%">
<colgroup>
 <col width="30%">
 <col>
</colgroup>
 <tbody><tr valign="top">
   <td>����� ��������</td>
   <td>

<table class="forms" style="width:100%">
<colgroup>
 <col width="30%">
 <col>
</colgroup>
<tbody>

   <tr><td><span>����� ��������</span></td><td><textarea rows="4" name="deliveryAddress" onblur="checkDetailForm()" onkeypress="checkDetailForm()" onkeyup="checkDetailForm()" maxlength="500" class="simple-text">{$smarty.session.deliveryAddress}</textarea></td></tr>
   <tr><td><span>��� ����������</span></td><td><input class="simple-text" name="deliveryPerson" maxlength="60" value="{$smarty.session.deliveryPerson}" onblur="checkDetailForm()" onkeypress="checkDetailForm()" onkeyup="checkDetailForm()" type="text"></td></tr>
   <tr><td><span>��. �����</span></td><td><input class="simple-text" name="deliveryEmail" maxlength="60" value="{$smarty.session.deliveryEmail}" onblur="checkDetailForm()" onkeypress="checkDetailForm()" onkeyup="checkDetailForm()" type="text"></td></tr>
   <tr><td colspan="2"><p class="cut">������� ������ ���������, �� ������� � ���� ����� ��������� ��� ������������ ������</p></td></tr>
   <tr><td><span>��������� �������</span><p class="comment">������: +7 (XXX) XXX-XX-XX</p></td><td><input maxlength="30" class="simple-text" name="deliveryPhone" value="{$smarty.session.deliveryPhone}" onblur="checkDetailForm()" onkeypress="checkDetailForm()" onkeyup="checkForm()" type="text"></td></tr>

   <tr><td>�������������� ����� ��������<p class="comment">(� ����� ������)</p></td><td><input maxlength="30" name="deliveryPhoneAdd" value="{$smarty.session.deliveryPhoneAdd}" class="simple-text" type="text"></td></tr>
  </tbody></table>
  </td>
  </tr>
 </tbody></table>
<!-- //�����-->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td width="50%">&nbsp;</td>
<td width="50%" align="right"><input type="submit" disabled="disabled" id="submitBtn" value="����������" /></td>
</tr>
</table>
</form>
