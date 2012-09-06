<h1>Адреса доставки</h1>
<form method="POST" name="mainForm">

<style>
#deliveryStreet.r {color:red;}
#deliveryStreet.b {color:black;}
select#suggestSelect {position: absolute; height:9em}
</style>

<p class="cut">Введите адрес доставки или выберите для редактирования один из <a href="/cabinet/address.htm">ваших адресов доставки</a></p>
<br>
<input name="addressId" value="{$address.id}" type="hidden">
<input name="updateAddress" value="1" type="hidden">
<table class="formTbl" width="100%">
<colgroup>
 <col width="30%">
 <col>
</colgroup>
<tbody>
<tr>
<td>&nbsp;</td>
<td></td>
</tr>
<tr>
<td><span>Город</span></td>
<td><input maxlength="60" value="{$address.street}" id="deliveryStreet" name="deliveryStreet" type="text"></td>
</tr>
<tr>
<td><span>Улица</span><p class="comment">(проезд, площадь, переулок и т.п.)</p></td>
<td><input maxlength="60" value="{$address.street}" id="deliveryStreet" name="deliveryStreet" type="text"></td>
</tr>
   <tr><td><p class="must">Дом</p></td><td><input value="37" maxlength="10" name="deliveryHouse" class="semiwide" onblur="checkForm()" onkeypress="checkForm()" onkeyup="checkForm()" type="text"></td></tr>
   <tr><td>Корпус</td><td><input value="1" maxlength="10" name="deliveryCorpus" class="semiwide" type="text"></td></tr>
   <tr><td>Строение</td><td><input value="2" maxlength="10" name="deliveryBuilding" class="semiwide" type="text"></td></tr>

      <tr><td>Квартира</td><td><input value="38" maxlength="10" name="deliveryFlat" class="semiwide" type="text"></td></tr>
   <tr><td>Комментарий к адресу доставки</td><td><textarea rows="3" name="deliveryAddressComment" maxlength="150" onkeyup="return ismaxlength(this)" class="wide">доставлять по выходным</textarea></td></tr>
   <tr><td><p class="must">ФИО получателя</p></td><td><input maxlength="60" class="wide" name="deliveryUser" value="Алякритский Роман" onblur="checkForm()" onkeypress="checkForm()" onkeyup="checkForm()" type="text"></td></tr>
   <tr><td colspan="2"><p class="cut">Укажите номера телефонов, по которым с Вами можно связаться для согласования заказа</p></td></tr>
   <tr><td><p class="must">Мобильный телефон</p><p class="comment">Формат: +7 (XXX) XXX-XX-XX</p></td><td><input maxlength="30" class="wide" name="deliveryPhone" value="+7 (916) 977-84-90" onblur="checkForm()" onkeypress="checkForm()" onkeyup="checkForm()" type="text"></td></tr>

   <tr><td><p>Дополнительный номер телефона</p><p class="comment">(с кодом города)</p></td><td><input maxlength="30" name="deliveryPhoneAdd" value="" class="wide" type="text"></td></tr>
   <tr><td>&nbsp;</td><td><input value="Сохранить" id="submitBtn" type="submit"></td></tr>
  </tbody></table>
<script>
function checkForm()
{
 f = document.mainForm;
 btn = document.getElementById('submitBtn');
 a = trim(f.deliveryStreet.value);
 b = trim(f.deliveryUser.value);
 с = trim(f.deliveryPhone.value).length < 6;
 d = trim(f.deliveryHouse.value);
 btn.disabled = (a == '' || b == '' || с || d == '') ? true : false;
}

function ismaxlength(obj)
{
 var mlength=obj.getAttribute? parseInt(obj.getAttribute("maxlength")) : "";
 if (obj.getAttribute && obj.value.length>mlength)
  obj.value=obj.value.substring(0,mlength);
}

if(document.getElementById('submitBtn'))
 checkForm();

checkMetro();</script>
 </form>
