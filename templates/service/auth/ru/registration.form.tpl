<h1>Регистрация</h1>
<form name="registrationForm" method="POST">
<input type="hidden" name="fromPage" value="{$fromPage}" />
<input name="processRegistration" value="1" type="hidden">
<table width="67%" class="forms">
<colgroup>
 <col width="30%" />
 <col />
</colgroup>
<tr><td>&nbsp;</td><td>{if $error_message}<div class="tree-error">{$error_message}</div>{/if}</td></tr>
<tr>
<td><span>Имя</span></td>
<td><input onblur="checkRegForm()" onkeypress="checkRegForm()" onkeyup="checkRegForm()" type="text" class="simple-text" maxlength="30" name="newUserFName" /></td>
</tr>
<tr>
<td>Фамилия</td>
<td><input onblur="checkRegForm()" onkeypress="checkRegForm()" onkeyup="checkRegForm()" type="text" class="simple-text" maxlength="50" name="newUserLName" /></td>
</tr>
<tr>
<td><span>Электронная почта</span></td>
<td><input onblur="checkRegForm()" onkeypress="checkRegForm()" onkeyup="checkRegForm()" type="text" class="simple-text" maxlength="30" name="newUserEmail" /></td>
</tr>
<tr>
<td><span>Телефон</span></td>
<td><input onblur="checkRegForm()" onkeypress="checkRegForm()" onkeyup="checkRegForm()" type="text" class="simple-text" maxlength="30" name="newUserPhone" />
<p class="comment">Формат: +7 (XXX) XXX-XX-XX</p></td>
</tr>
<tr>
<td><span>Пароль</span></td>
<td><input type="password" class="simple-pass" maxlength="50" name="newUserPassword" />
<p class="comment">Пароль должен содержать не менее 6 символов из списка:<br>A-z, 0-9, ! @ # $ % ^ &amp; * ( ) _ - +, и не может совпадать с электронным адресом</p></td>
</tr>
<tr>
<td><span>Подтверждение пароля</span></td>
<td><input onblur="checkPass(this,this.form.newUserPassword)" onkeypress="checkPass(this,this.form.newUserPassword)" onkeyup="checkPass(this,this.form.newUserPassword)" type="password" class="simple-pass" maxlength="50" name="newUserPasswordConfirm" />
<p class="comment" id="passStatus">введите, пожалуйста, новый пароль еще раз</p></td>
</tr>
<tr>
<td><span>Введите цифры</span><div class="clear"></div>
<img id="secure_image" src="/secureimage.php?{$smarty.session.name}={$smarty.session.id}">
<a href="#" border="0" style="padding-bottom:15px;" onclick="document.getElementById('secure_image').src='/secureimage.php?rnd='+Math.random()+'&{$sess_name}={$sess_id}';return false"><img src="/img/reload.gif"></a>
</td>
<td><input type="text" maxlength="5" size="5" onblur="checkRegForm()" onkeypress="checkRegForm()" onkeyup="checkRegForm()" class="simple-captcha" maxlength="50" name="captcha" />
</td>
</tr>
<tr>
<td></td>
<td><input type="submit" id="submitBtn" disabled="true" value="Продолжить" /></td>
</tr>
</table>
<input type="hidden" value="false" name="passOk" />
</form>