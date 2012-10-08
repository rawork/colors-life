<form name="mainForm" method="POST">
<h1>Вход в личный кабинет</h1>
<input type="hidden" name="fromPage" value="">
<input name="processLogin" value="1" type="hidden">
<table class="forms" width="100%">
<colgroup>
 <col width="30%" />
 <col />
</colgroup>
<tr><td>&nbsp;</td><td>{if $error_message}<div class="tree-error">{$error_message}</div>{/if}</td></tr>
<tr>
<td><span>Электронный адрес</span></td>
<td><input maxlength="50" type="text" class="simple-text required" name="login" id="l" /></td>
</tr>
<tr>
<td><span>Пароль</span></td>
<td><input maxlength="50" type="password" class="simple-text required" name="password" id="p" /></td>
</tr>
  <tr><td>&nbsp;</td><td><input type="submit" disabled="disabled" id="submitBtn" value="Войти" /></td></tr>
  <tr><td>&nbsp;</td><td><p><a href="/cabinet/forget.htm">Уже регистрировались и забыли пароль?</a></p>
                         <p><br /><a href="/cabinet/registration.htm">Хочу зарегистрироваться</a></td></tr>
 </table>
 <input type="hidden" value="false" name="passOk" />
</form>
<script type="text/javascript">
bindLoginForm();
</script>