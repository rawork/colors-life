<form name="mainForm" method="POST">
<h1>Личные данные</h1>
{$cabinetMenu}
<input type="hidden" name="fromPage" value="">
<input name="processInfo" value="1" type="hidden">
<table class="forms" width="100%">
<colgroup>
 <col width="30%" />
 <col />
</colgroup>
<tr><td>&nbsp;</td><td>{if $error_message}<div class="tree-error">{$error_message}</div>{/if}</td></tr>
<tr>
<td><span>Электронная почта</span></td>
<td><input onblur="checkInfoForm()" onkeypress="checkInfoForm()" onkeyup="checkInfoForm()" type="text" value="{$userInfo.email}" class="simple-text" maxlength="30" name="userEmail" /></td>
</tr>
<tr>
<td><span>Имя</span></td>
<td><input onblur="checkInfoForm()" onkeypress="checkInfoForm()" onkeyup="checkInfoForm()" type="text" value="{$userInfo.name}" class="simple-text" maxlength="30" name="userFName" /></td>
</tr>
<tr>
<td>Фамилия</td>
<td><input onblur="checkInfoForm()" onkeypress="checkInfoForm()" onkeyup="checkInfoForm()" type="text" value="{$userInfo.lastname}" class="simple-text" maxlength="50" name="userLName" /></td>
</tr>
<tr>
<td><span>Телефон</span></td>
<td><input onblur="checkInfoForm()" onkeypress="checkInfoForm()" onkeyup="checkInfoForm()" type="text" value="{$userInfo.phone}" class="simple-text" maxlength="30" name="userPhone" />
<p class="comment">Формат: +7 (XXX) XXX-XX-XX</p></td>
</tr>
<tr>
<td>Адрес</td>
<td><textarea class="simple-text" maxlength="500" name="userAddress">{$userInfo.address}</textarea></td>
</tr>
<tr>
<td>Пол</td>
<td>
<input type="radio" value="мужской"{if $userInfo.gender == 'мужской'} checked{/if} name="userGender" id="male"><label for="male">&nbsp;мужской</label>
<br><input type="radio" value="женский"{if $userInfo.gender == 'женский'} checked{/if} name="userGender" id="female"><label for="female">&nbsp;женский</label>
</td>
</tr>
 <tr><td>День рождения</td>
<td style="vertical-align: top;">
  <input type="text" value="{$userInfo.birthday[0]}" name="userDay" maxlength="2" style="width:2em;height: 18px;margin-bottom:3px;">
  <select style="height: 25px;" name="userMonth">
{foreach from=$Months key=MonthNumber item=MonthName}
<option  value="{$MonthNumber}"{if $MonthNumber == $userInfo.birthday[1]} selected{/if}>{$MonthName}</option>
{/foreach}
</select>
  <input type="text" maxlength="4" name="userYear" style="width:3em;height: 18px;margin-bottom:3px;" value="{$userInfo.birthday[2]}"></td>
</tr>
<tr>
<td></td>
<td><input type="submit" id="submitBtn" disabled="true" value="Сохранить" /></td>
</tr>
 </table>
<script>
checkInfoForm();
<script>
</form>