{if $error_message}<div class="alert alert-error">{$error_message}</div>{/if}
<form class="form-horizontal" name="mainForm" method="post">
<input type="hidden" name="fromPage" value="{$fromPage}">
<input type="hidden" value="false" name="passOk" />
  <div class="control-group">
    <label class="control-label" for="l">Электронный адрес</label>
    <div class="controls">
		<input type="text" id="l" class="required" name="login">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="p">Пароль</label>
    <div class="controls">
		<input type="password" id="p" class="required" name="password">
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <input type="submit" class="btn" id="submitBtn" disabled="true" value="Войти" />
	  <div>&nbsp;</div>
	  <div><a href="{raURL node=cabinet method=forget}">Уже регистрировались и забыли пароль?</a></div>
      <div><a href="{raURL node=cabinet method=registration}">Хочу зарегистрироваться</a></div>
    </div>
  </div>
</form>
<script type="text/javascript">
bindLoginForm();
</script>