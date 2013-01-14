{raSetVar var=title value="Личные данные"}
<h1>Личные данные</h1>
{$cabinetMenu}
{if $error_message}<div class="alert alert-error">{$error_message}</div>{/if}
{if $userInfo.discount}
	<div class="alert-success1">Персональная скидка: <strong>{$userInfo.discount}%</strong></div>
	<div>&nbsp;</div>
{/if}
<form class="form-horizontal" name="mainForm" method="post">
<input type="hidden" name="fromPage" value="">
<input name="processInfo" value="1" type="hidden">
  <div class="control-group">
    <label class="control-label required" for="newUserEmail">Электронная почта</label>
    <div class="controls">
		<input type="text" value="{$userInfo.email}" class="required" id="newUserEmail" maxlength="100" name="userEmail" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label required" for="newUserFName">Имя</label>
    <div class="controls">
		<input value="{$userInfo.name}" type="text" class="required" id="newUserFName" maxlength="30" name="userFName" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="newUserLName">Фамилия</label>
    <div class="controls">
		<input value="{$userInfo.lastname}" type="text" id="newUserLName" maxlength="30" name="userLName" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label required" for="newUserPhone">Телефон</label>
    <div class="controls">
		<input value="{$userInfo.phone}" type="text" class="required" id="newUserPhone" maxlength="20" name="userPhone" />
		<p class="comment">Формат: +7 (XXX) XXX-XX-XX</p>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="newUserAddress">Адрес</label>
    <div class="controls">
		<textarea rows="3" maxlength="500" name="userAddress">{$userInfo.address}</textarea>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="newUserGender">Пол</label>
    <div class="controls">
		<label><input type="radio" value="мужской"{if $userInfo.gender == 'мужской'} checked{/if} name="userGender" id="male">&nbsp;мужской</label>
		&nbsp;&nbsp;
		<label><input type="radio" value="женский"{if $userInfo.gender == 'женский'} checked{/if} name="userGender" id="female">&nbsp;женский</label>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="newUserAddress">День рождения</label>
    <div class="controls">
		<input class="input-mini" type="text" value="{$userInfo.birthday[0]}" name="userDay" maxlength="2">
		<select style="height: 26px;" class="span7" name="userMonth">
			{foreach from=$months key=monthNumber item=monthName}
			<option  value="{$monthNumber}"{if $monthNumber == $userInfo.birthday[1]} selected{/if}>{$monthName}</option>
			{/foreach}
		</select>
		<input class="input-mini" type="text" maxlength="4" name="userYear" value="{$userInfo.birthday[2]}">
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <input type="submit" class="btn" id="submitBtn" disabled="true" value="Сохранить" />
    </div>
  </div>	
</form>
<script>
bindInfoForm();
</script>