<form name="mainForm" method="POST">
<h1>���� � ������ �������</h1>
<input type="hidden" name="fromPage" value="">
<input name="processLogin" value="1" type="hidden">
<table class="forms" width="100%">
<colgroup>
 <col width="30%" />
 <col />
</colgroup>
<tr><td>&nbsp;</td><td>{if $error_message}<div class="tree-error">{$error_message}</div>{/if}</td></tr>
<tr>
<td><span>����������� �����</span></td>
<td><input maxlength="50" type="text" class="simple-text" name="login" id="l" value="" onblur="checkLoginForm()" onkeypress="checkLoginForm()" onkeyup="checkLoginForm()" /></td>
</tr>
<tr>
<td><span>������</span></td>
<td><input maxlength="50" type="password" class="simple-text" name="password" id="p" value="" onblur="checkLoginForm()" onkeypress="checkLoginForm()" onkeyup="checkLoginForm()" /></td>
</tr>
  <tr><td>&nbsp;</td><td><input type="submit" disabled="true" id="loginBtn" value="�����" /></td></tr>
  <tr><td>&nbsp;</td><td><p><a href="/cabinet/forget.htm">��� ���������������� � ������ ������?</a></p>
                         <p><br /><a href="/cabinet/registration.htm">���� ������������������</a></td></tr>
 </table>
 <input type="hidden" value="false" name="passOk" />
<script>
</form>
