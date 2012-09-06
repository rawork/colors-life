<h1>Изменение пароля</h1>
<form name="registrationForm" method="POST">
{$cabinetMenu}
<input type="hidden" name="fromPage" value="">
<input name="processPassword" value="1" type="hidden">
<table class="forms" width="100%">
<colgroup>
 <col width="30%" />
 <col />
</colgroup>
<tr><td>&nbsp;</td><td>
{if $error_message}<div class="tree-error">{$error_message}</div>{/if}
{if $info_message}<div class="tree-accept">{$info_message}</div>{/if}
</td></tr>
  <tr>
  <td>Старый пароль</td>
  <td><input class="simple-text" type="password" name="passwd" onblur="check_pass_change()" onkeypress="check_pass_change()" onkeyup="check_pass_change()" tabindex="1"></td>
 </tr>
 <tr>
  <td>Новый пароль</td>
  <td>
   <input type="password" class="simple-text" name="newpasswd" onblur="checkPass(this.form.newpasswd2,this)" onkeypress="checkPass(this.form.newpasswd2,this)" onkeyup="checkPass(this.form.newpasswd2,this)" tabindex="2">
   <p class="comment">Пароль должен содержать не менее 6 символов из списка:<br />
   A-z, 0-9, ! @ # $ % ^ &amp; * ( ) _ - + и не может совпадать с логином.
   </p>
 </td>
</tr>
<tr>
 <td>Подтверждение</td>
 <td>
  <input type="password" class="simple-text" name="newpasswd2" onblur="checkPass(this,this.form.newpasswd)" onkeypress="checkPass(this,this.form.newpasswd)" onkeyup="checkPass(this,this.form.newpasswd)" tabindex="3">
  <p class="comment" id="passStatus">введите, пожалуйста, новый пароль еще раз</p><br>
 </td>
</tr>
  <tr><td>&nbsp;</td><td><input type="submit" disabled="true" name="done" id="submitBtn" value="Сохранить"></td></tr>
 </table>
<script>
 check_pass_change()
</script>
</form>
