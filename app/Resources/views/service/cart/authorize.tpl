{raSetVar var=title value="Авторизация"}
<h4>
<a href="/cart/">Уточнение заказа</a> &rarr;
Авторизация &rarr;
<span>Параметры заказа</span> &rarr;
<span>Подтверждение заказа</span>
</h4>
<br>
<p>Для оформления заказа войдите в магазин под своим аккаунтом.</p>
<p class="cut">Если вы делаете заказ впервые, пожалуйста, <a href="/cabinet/registration.htm">зарегистрируйтесь</a>.<br> Регистрация поможет вам контролировать заказы и сократит время оформления последующих покупок.</p>
<p class="cut"><a href="/cart/detail.htm">Не хочу регистрироваться</a></p>
<form class="form-horizontal" name="mainForm" action="/cabinet/login.htm" method="post">
<input type="hidden" name="fromPage" value="/cart/detail.htm" />
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
      <input type="submit" class="btn btn-large btn-warning" id="submitBtn" disabled="true" value="Продолжить" />
	  <div>&nbsp;</div>
	  <div><a href="/cabinet/forget.htm">Уже регистрировались и забыли пароль?</a></div>
    </div>
  </div>
</form>
<script type="text/javascript">
bindLoginForm();
</script>