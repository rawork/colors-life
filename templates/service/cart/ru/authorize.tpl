<h1>
<a href="/cart/">��������� ������</a> &rarr;
����������� &rarr;
<span>��������� ������</span> &rarr;
<span>������������� ������</span>
</h1>
<br>
<form name="mainForm" action="/cabinet/login.htm" method="POST">
<input name="processLogin" value="1" type="hidden">
<input type="hidden" name="fromPage" value="/cart/detail.htm" />
<p>��� ���������� ������ ������� � ������� ��� ����� ���������.</p>
  <p class="cut">���� �� ������� ����� �������, ����������, <a href="/cabinet/registration.htm">�����������������</a>.<br> ����������� ������� ��� �������������� ������ � �������� ����� ���������� ����������� �������.</p>
  <p class="cut"><a href="/cart/detail.htm">�� ���� ����������������</a></p>
<table class="forms" width="100%">
<colgroup>
 <col width="30%" />
 <col />
</colgroup>
 
<tr><td><span>����������� �����</span></td><td><input maxlength="50" class="simple-text" type="text" name="login" id="l" value="" onblur="checkLoginForm()" onkeypress="checkLoginForm()" onkeyup="checkLoginForm()"></td></tr>

<tr><td><span>������</span></td><td><input maxlength="50" class="simple-text" type="password" name="password" id="p" value="" onblur="checkLoginForm()" onkeypress="checkLoginForm()" onkeyup="checkLoginForm()">
<tr><td>&nbsp;</td><td><input type="submit" disabled="true" id="loginBtn" value="����������..."></td></tr>
<tr><td>&nbsp;</td><td><p><a href="/cabinet/forget.htm">��� ���������������� � ������ ������?</a></p></td></tr>
</table>
 <input type="hidden" value="false" name="passOk" />

<script>
checkLoginForm();
</script>
 </form>
