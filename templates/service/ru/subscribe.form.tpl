<div class="subscribe-form">
  <div class="popup-title">Подписаться на рассылку</div>
  новостей компании &laquo;Цвета жизни&raquo;, чтобы в<br> числе первых узнавать о скидках,<br> акциях и новинках.
  <br><br>
  <form name="frmSubscribe" id="frmSubscribe" method="post" action="">
    <input type="hidden" value="1" name="subscribe_type" />
    <table style="width: 350px">
      <tr>
        <td style="padding: 0 0 5px 0;">Адрес электронной почты<br><input style="font: 12px Arial; width: 100%" name="email" type="text" /></td>
      </tr>
	  <tr>
        <td style="padding: 0 0 5px 0;">Фамилия<br><input style="font: 12px Arial; width: 100%" name="lastname" type="text" /></td>
      </tr>
	  <tr>
        <td style="padding: 0 0 5px 0;">Имя<br><input style="font: 12px Arial; width: 100%" name="name" type="text" /></td>
      </tr>
	  <tr>
		<td style="padding: 0 0 10px 0;white-space:nowrap;">
		<input class="subscribe-radio" style="width:auto;" type="radio" name="subscribe_type" id="rd1" value="1" /><label for="rd1">Я <strong>хочу</strong> получать новости от компании Цвета жизни</label></p>
		<input class="subscribe-radio" style="width:auto;" type="radio" name="subscribe_type" id="rd2" value="2" /><label for="rd2">Я <strong>не хочу</strong> получать новости от компании Цвета жизни</label></p></td>
	  </tr>	
      <tr>
        <td><input class="subscribe-button" style="width:auto;" type="button" value="Отправить" onClick="xajax_showSubscribeResult(xajax.getFormValues('frmSubscribe'))" />&nbsp;&nbsp;
		</td>
      </tr>
      <tr>
        <td></td>
      </tr>
    </table>
  </form>
</div>
