<h1>
<a href="/cart/">Уточнение заказа</a> &rarr;
Авторизация &rarr;
<span>Параметры заказа</span> &rarr;
<span>Подтверждение заказа</span>
</h1>
<br>
<form name="mainForm" action="/cabinet/login.htm" method="POST">
<input name="processLogin" value="1" type="hidden">
<input type="hidden" name="fromPage" value="/cart/detail.htm" />
<p>Для оформления заказа войдите в магазин под своим аккаунтом.</p>
  <p class="cut">Если вы делаете заказ впервые, пожалуйста, <a href="/cabinet/registration.htm">зарегистрируйтесь</a>.<br> Регистрация поможет вам контролировать заказы и сократит время оформления последующих покупок.</p>
  <p class="cut"><a href="/cart/detail.htm">Не хочу регистрироваться</a></p>
<table class="forms" width="100%">
<colgroup>
 <col width="30%" />
 <col />
</colgroup>
 
<tr><td><span>Электронный адрес</span></td><td><input maxlength="50" class="simple-text" type="text" name="login" id="l" value="" onblur="checkLoginForm()" onkeypress="checkLoginForm()" onkeyup="checkLoginForm()"></td></tr>

<tr><td><span>Пароль</span></td><td><input maxlength="50" class="simple-text" type="password" name="password" id="p" value="" onblur="checkLoginForm()" onkeypress="checkLoginForm()" onkeyup="checkLoginForm()">
<tr><td>&nbsp;</td><td><input type="submit" disabled="true" id="loginBtn" value="Продолжить..."></td></tr>
<tr><td>&nbsp;</td><td><p><a href="/cabinet/forget.htm">Уже регистрировались и забыли пароль?</a></p></td></tr>
</table>
 <input type="hidden" value="false" name="passOk" />

<script>
checkLoginForm();
</script>
 </form>
