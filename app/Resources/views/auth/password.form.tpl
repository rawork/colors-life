<h1>Изменение пароля</h1>
<form class="form-horizontal" name="registrationForm" method="post">
{$cabinetMenu}
<input type="hidden" name="fromPage" value="">
{if $error_message}<div class="alert alert-error">{$error_message}</div>{/if}
{if $info_message}<div class="alert alert-success">{$info_message}</div>{/if}
  <div class="control-group">
    <label class="control-label" for="p">Старый пароль</label>
    <div class="controls">
		<input id="p" type="password" name="passwd" onblur="check_pass_change()" onkeypress="check_pass_change()" onkeyup="check_pass_change()" tabindex="1">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="p_new">Новый пароль</label>
    <div class="controls">
		<input id="p_new" type="password" name="newpasswd" onblur="checkPass(this.form.newpasswd2,this)" onkeypress="checkPass(this.form.newpasswd2,this)" onkeyup="checkPass(this.form.newpasswd2,this)" tabindex="2">
		<p class="comment">Пароль должен содержать не менее 6 символов из списка:<br />
		A-z, 0-9, ! @ # $ % ^ &amp; * ( ) _ - + и не может совпадать с логином.
		</p>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="p_new">Подтверждение</label>
    <div class="controls">
		<input type="password" class="simple-text" name="newpasswd2" onblur="checkPass(this,this.form.newpasswd)" onkeypress="checkPass(this,this.form.newpasswd)" onkeyup="checkPass(this,this.form.newpasswd)" tabindex="3">
		<p class="comment" id="passStatus">введите, пожалуйста, новый пароль еще раз</p>
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <input type="submit" class="btn" name="done" id="submitBtn" disabled="true" value="Сохранить" />
    </div>
  </div>
</form>  
<script>
 check_pass_change()
</script>  
