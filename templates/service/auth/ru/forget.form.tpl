<h1>Восстановление пароля</h1>
<p>Введите свой электронный адрес, указанный при регистрации, и нажмите кнопку &laquo;Выслать пароль&raquo;.</p>
<form name="mainForm" method="POST">
<input name="processForget" value="1" type="hidden">
<table width="67%" class="forms">
<tr><td>&nbsp;</td><td>
{if $error_message}<div class="tree-error">{$error_message}</div>{/if}
{if $info_message}<div class="tree-accept">{$info_message}</div>{/if}
</td></tr>
<tr><td><span>Электронный адрес</span></td>
<td><input class="wide" maxlength="50" type="text" name="login" id="l" value="" onblur="checkForgetForm()" onkeypress="checkForgetForm()" onkeyup="checkForgetForm()" /></td></tr>
<tr>
<td><span>Введите цифры</span><div class="clear"></div>
<img id="secure_image" src="/secureimage.php?{$smarty.session.name}={$smarty.session.id}">
<a href="#" border="0" style="padding-bottom:15px;" onclick="document.getElementById('secure_image').src='/secureimage.php?rnd='+Math.random()+'&{$sess_name}={$sess_id}';return false"><img src="/img/reload.gif"></a>
</td>
<td><input type="text" maxlength="5" size="5" onblur="checkForgetForm()" onkeypress="checkForgetForm()" onkeyup="checkForgetForm()" class="simple-captcha" maxlength="50" name="captcha" />
</td>
</tr>
  <tr><td>&nbsp;</td><td><input type="submit" disabled="true" id="submitBtn" value="Выслать пароль" /></td></tr>
  <tr><td>&nbsp;</td><td><p><a href="/cabinet/">У меня есть пароль. Я хочу войти на сайт.</a></p>
                         <p><br><a href="/cabinet/registration.htm">Хочу зарегистироваться</a></td></tr>
 </table>
<script>
checkForgetForm();
</script>
</form>
