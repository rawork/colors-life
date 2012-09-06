<html>
<head>
<title>Управление сайтом - {$prj_name}</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<link href="{$theme_ref}/css/style.css" rel="stylesheet" type="text/css">
</head>
<body topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td height="100%" align="center" valign="middle"><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td align="center"> {$message}
            <div class="auth-border">
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td valign="top"><table style="width:100%;border-bottom: 1px dotted #B2BCD9; margin-bottom: 7px;" border="0" cellspacing="3" cellpadding="0">
                      <tr>
                        <td valign="top"><img src="{$theme_ref}/img/icons/icon_key.gif"></td>
                        <td valign="top" width="100%"><div class="auth-title">Авторизация</div></td>
                      </tr>
                    </table></td>
                </tr>
                <tr>
                  <td valign="top"><form name="auth" method="post">
                      <table style="width:100%;border-bottom: 1px dotted #B2BCD9; margin-bottom: 7px;" border="0" cellspacing="0" cellpadding="0">
                        <input type="hidden" name="submited" value="1">
                        <tr>
                          <td width="20">&nbsp;</td>
                          <td align="right"><strong>Логин:&nbsp;</strong></td>
                          <td><input name="auser" type="text" class="pole" size="30"></td>
                        </tr>
                        <tr>
                          <td height="5" colspan="3"></td>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                          <td align="right"><strong>Пароль:&nbsp;</strong></td>
                          <td><input name="apw" type="password" class="pole" size="30"></td>
                        </tr>
                        <tr>
                          <td height="5" colspan="3"></td>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td><table border="0">
                              <tr>
                                <td><input type="checkbox" name="save" value="on"></td>
                                <td> запомнить меня</td>
                              </tr>
                            </table></td>
                        </tr>
                        <tr>
                          <td height="5" colspan="3"></td>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td><input class="submit" type="submit" value="Войти">
                            <br>
                            <br></td>
                        </tr>
                      </table>
                    </form></td>
                </tr>
                <tr>
                  <td style="padding-bottom: 3px;"><b>Забыли свой пароль?</b><br>
                    Следуйте на <a href="/admin/?operation=forgot">форму для запроса пароля</a>.<br>
                  </td>
                </tr>
              </table>
            </div></td>
        </tr>
      </table></td>
  </tr>
</table>
</body>
</html>
