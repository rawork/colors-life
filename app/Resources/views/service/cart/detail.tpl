<h1>
<a href="/cart/">Уточнение заказа</a> &rarr;
{if !$user.id}<a href="/cart/authorize.htm">Авторизация</a> &rarr;{/if}
Параметры заказа &rarr;
<span>Подтверждение заказа</span>
</h1>
<br>
          
<form name="mainForm" method="POST">
<input type="hidden" name="processDetail" value="1">
<!-- Блок способ оплаты -->
<table class="forms" width="100%">
<colgroup>
 <col width="30%">
 <col>
</colgroup>
<tbody>
<tr><td>Получение товара</td>
 <td style="vertical-align: top;">
  {foreach from=$deliveryTypes key=iKey item=deliveryType}
  <input onclick="setDeliveryType({$deliveryType.id})" value="{$deliveryType.id}" name="deliveryType"{if !$iKey} checked="checked"{/if} id="deliveryType{$deliveryType.id}" type="radio"> <label for="deliveryType{$deliveryType.id}">{$deliveryType.name}</label>
  {/foreach}
  {foreach from=$deliveryTypes key=iKey item=deliveryType}
  <p class="comment delivery-text" id="deliveryDescr{$deliveryType.id}"{if $iKey} style="display:none"{/if}>{$deliveryType.description}</p>
  {/foreach}
   </td>
</tr>
<tr><td>Способ оплаты</td>
 <td>
  {foreach from=$payTypes key=iKey item=payType}
  <input onclick="setPayType({$payType.id})" value="{$payType.id}" name="payType"{if !$iKey} checked="true"{/if} id="payType{$payType.id}" type="radio"> <label for="payType{$payType.id}">{$payType.name}</label>
  {/foreach}
  <p class="cut closed" id="payDescr2">Для жителей городов России временно доступен только один вид оплаты: квитанция банка.</p>
  </td>
</tr>
</tbody></table>
<!-- адрес-->
<table class="forms" width="100%">
<colgroup>
 <col width="30%">
 <col>
</colgroup>
 <tbody><tr valign="top">
   <td>Адрес доставки</td>
   <td>

<table class="forms" style="width:100%">
<colgroup>
 <col width="30%">
 <col>
</colgroup>
<tbody>
   <tr>
	   <td><span>Адрес доставки</span></td>
	   <td><textarea class="simple-text required" rows="4" name="deliveryAddress" maxlength="500">{$smarty.session.deliveryAddress}</textarea></td></tr>
   <tr>
	   <td><span>ФИО получателя</span></td>
	   <td><input class="simple-text required" name="deliveryPerson" maxlength="60" value="{$smarty.session.deliveryPerson}" type="text"></td></tr>
   <tr>
	   <td><span>Эл. почта</span></td>
	   <td><input class="simple-text required" name="deliveryEmail" maxlength="60" value="{$smarty.session.deliveryEmail}" type="text"></td></tr>
   <tr><td colspan="2"><p class="cut">Укажите номера телефонов, по которым с Вами можно связаться для согласования заказа</p></td></tr>
   <tr>
	   <td><span>Мобильный телефон</span>
		   <p class="comment">Формат: +7 (XXX) XXX-XX-XX</p></td>
	   <td><input maxlength="30" class="simple-text required" name="deliveryPhone" value="{$smarty.session.deliveryPhone}" type="text"></td></tr>

   <tr><td>Дополнительный номер телефона
		   <p class="comment">(с кодом города)</p></td>
	   <td><input maxlength="30" name="deliveryPhoneAdd" value="{$smarty.session.deliveryPhoneAdd}" class="simple-text" type="text"></td></tr>
  </tbody></table>
  </td>
  </tr>
 </tbody></table>
<!-- //адрес-->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td width="50%">&nbsp;</td>
<td width="50%" align="right"><input type="submit" disabled="disabled" id="submitBtn" value="Продолжить" /></td>
</tr>
</table>
</form>
<script type="text/javascript">
	bindDetailForm();
</script>
