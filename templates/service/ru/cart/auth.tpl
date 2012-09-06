          <h1><a href="/cart/">Ваша корзина</a> &rarr; Информация о Вас &rarr; <span>Оплата и доставка</span> &rarr; <span>Подтверждение</span> &rarr; <span>Информация о заказе</span></h1>
          <br>
{if $uauth->user}
<div class="cart-auth-info">
<b>ФИО:</b> {$uauth->user.name}<br>
<b>Телефон:</b> {$uauth->user.phone}<br>
<b>E-mail:</b> {$uauth->user.email}<br>
<b>Способ оплаты:</b> {$smarty.session.pay_type}<br>
<b>Способ доставки:</b> {$smarty.session.delivery_type}<br>
<b>Адрес доставки:</b> {$smarty.session.delivery_address}<br>
</div>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
            <td width="50%">&nbsp;</td>
            <td width="50%" align="right"><a href=""><a href="#" onclick="window.location = '/cart/?action=delivery'; return false;"><img src="/img/next_btn.gif"></a></td>
            </tr>
            </table>
{else}
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
          <td valign="top">
          <div>Если вы новый покупатель,<br> 
			Вам необходимо пройти регистрацию:<br></div>
          <div class="reg-form">
          <form method="post" action="/cabinet/?action=reg">
          Логин <span class="required">*</span><br>
          <input type="text" name="login" class="field-t1"><br>
          Пароль <span class="required">*</span><br>
          <input type="password" name="password" class="field-t1"><br>
          Пароль еще раз <span class="required">*</span><br>
          <input type="password" name="password_password_check" class="field-t1"><br>
          ФИО <span class="required">*</span><br>
          <input type="text" name="name" class="field-t1"><br>
          Дата рождения <span class="required">*</span><br>
          <input type="text" name="birthday" class="field-t1"><br>
          Телефон <span class="required">*</span><br>
          <input type="text" name="phone" class="field-t1"><br>
          Эл. почта <span class="required">*</span><br>
          <input type="text" name="email" class="field-t1"><br>
          Способ оплаты <span class="required">*</span><br>
          <select name="pay_type" class="field-t2">
          {foreach from=$pays item=pay}
          <option value="{$pay.name}">{$pay.name}</option>
		  {/foreach}
          </select>
          <br>
          Способ доставки <span class="required">*</span><br>
          <select name="delivery_type" class="field-t2">
          {foreach from=$delivery item=deliv}
          <option value="{$deliv.name}">{$deliv.name}</option>
		  {/foreach}
          </select><br>
           Адрес доставки <br>
          <textarea style="width:250px;" type="text" name="address" class="field-t1"></textarea><br>
           Введите символы на картинке внизу <br>
          <input type="text" class="field-t1"><br>
          <img id="secure_image" src="/secureimage.php"> <a href="#" onclick="document.getElementById('secure_image').src='/secureimage.php?rnd='+Math.random()+'&{$sess_name}={$sess_id}';return false">обновить код</a><br>
          <input type="submit" value="Отправить">
          </form>
          </div>
          </td>
          <td valign="top">
          <div>Если вы уже зарегистрированы,<br>
авторизуйтесь:<br></div>
          <div class="login-form">
          <form method="post" action="/cabinet/?action=login">
		  <input type="hidden" name="submited" value="1">

          Логин <br>
          <input type="text" name="login" class="field-t1"><br>
          Пароль <br>
          <input type="password" name="password" class="field-t1"><br>
          <a href="#" onclick="return showForgotForm()">Забыли пароль?</a><br>
          <input type="submit" value="Войти">
          </form>
          </div>
          </td>
          </tr>
          </table>
{/if}