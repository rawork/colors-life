<h1>Восстановление пароля</h1>
<p>Введите свой электронный адрес, указанный при регистрации, и нажмите кнопку &laquo;Выслать пароль&raquo;.</p>
<form class="form-horizontal" name="mainForm" method="post">
{if $error_message}<div class="alert alert-error">{$error_message}</div>{/if}
{if $info_message}<div class="alert alert-success">{$info_message}</div>{/if}
  <div class="control-group">
    <label class="control-label" for="l">Электронный адрес</label>
    <div class="controls">
		<input type="text" id="l" class="required" name="login" onblur="checkForgetForm()" onkeypress="checkForgetForm()" onkeyup="checkForgetForm()">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label required" for="p2">Введите цифры
		<img id="secure_image" src="/secureimage/?{$smarty.session.name}={$smarty.session.id}">
		<a href="#" border="0" onclick="document.getElementById('secure_image').src='/secureimage/?rnd='+Math.random()+'&{$smarty.session.name}={$smarty.session.id}';return false"><img src="/img/reload.gif"></a>
	</label>
    <div class="controls">
		<input type="text" maxlength="5" size="5" onblur="checkForgetForm()" onkeypress="checkForgetForm()" onkeyup="checkForgetForm()" class="required" name="captcha" />
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <input type="submit" class="btn" id="submitBtn" disabled="true" value="Выслать пароль" />
	  <div>&nbsp;</div>
	  <div><a href="{raURL node=cabinet}">У меня есть пароль. Я хочу войти на сайт.</a></div>
      <div><a href="{raURL node=cabinet method=registration}">Хочу зарегистироваться</a></div>
    </div>
  </div>	
</form>
<script>
checkForgetForm();
</script>