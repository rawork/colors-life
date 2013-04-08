{if $error_message}<div class="alert alert-error">{$error_message}</div>{/if}
<form class="form-horizontal" name="registrationForm" method="post">
<input type="hidden" name="fromPage" value="{$fromPage}" />
<input type="hidden" value="false" name="passOk" />
  <div class="control-group">
    <label class="control-label required" for="newUserFName">Имя</label>
    <div class="controls">
		<input type="text" class="required" id="newUserFName" maxlength="30" name="newUserFName" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="newUserLName">Фамилия</label>
    <div class="controls">
		<input type="text" id="newUserLName" maxlength="30" name="newUserLName" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label required" for="newUserPhone">Телефон</label>
    <div class="controls">
		<input type="text" class="required" id="newUserPhone" maxlength="20" name="newUserPhone" />
		<p class="comment">Формат: +7 (XXX) XXX-XX-XX</p>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label required" for="newUserEmail">Электронная почта</label>
    <div class="controls">
		<input type="text" class="required" id="newUserEmail" maxlength="100" name="newUserEmail" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label required" for="p">Пароль</label>
    <div class="controls">
		<input type="password" id="p" class="required" name="newUserPassword">
		<p class="comment">Пароль должен содержать не менее 6 символов из списка:<br>A-z, 0-9, ! @ # $ % ^ &amp; * ( ) _ - +, и не может совпадать с электронным адресом</p>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label required" for="p2">Подтверждение пароля</label>
    <div class="controls">
		<input onblur="checkPass(this,this.form.newUserPassword)" onkeypress="checkPass(this,this.form.newUserPassword)" onkeyup="checkPass(this,this.form.newUserPassword)" type="password" id="p2" name="newUserPasswordConfirm">
		<p class="comment" id="passStatus">введите, пожалуйста, новый пароль еще раз</p>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label required" for="p2">Введите цифры
		<img id="secure_image" src="/secureimage/?{$sessionName}={$sessionId}">
		<a href="#" border="0" onclick="document.getElementById('secure_image').src='/secureimage/?rnd='+Math.random()+'&{$sessionName}={$sessionId}';return false"><img src="/img/reload.gif"></a>
	</label>
    <div class="controls">
		<input type="text" maxlength="5" size="5" class="required" name="captcha" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="newUserSubscribe">Подписка</label>
    <div class="controls">
		<p>Вы хотите получать новости и другие предложения? Поставьте галочку </p>
		<input type="checkbox" id="newUserSubscribe" name="newUserSubscribe" value="1" />
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <input type="submit" class="btn" id="submitBtn" disabled="true" value="Продолжить" />
    </div>
  </div>
</form>
<script type="text/javascript">
bindRegistrationForm();
</script>
