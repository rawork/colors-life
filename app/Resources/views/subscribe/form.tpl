<div id="subscribe_form">
	{if $subscribe_message}<div class="subscribe-message">{$subscribe_message}</div>{/if}
	<div class="subscribe-form">
	  новостей компании &laquo;Цвета жизни&raquo;, чтобы в<br> числе первых узнавать о скидках,<br> акциях и новинках.
	  <br><br>
	  <form name="frmSubscribe" id="frmSubscribe" method="post" action="">
		<input type="hidden" value="1" name="subscribe_type" />
		<table style="width: 350px">
		  <tr>
			<td>Электронная почта<br><input name="email" type="text" /></td>
		  </tr>
		  <tr>
			<td>Фамилия<br><input name="lastname" type="text" /></td>
		  </tr>
		  <tr>
			<td>Имя<br><input name="name" type="text" /></td>
		  </tr>
		  <tr>
			<td>
			<label><input type="radio" name="subscribe_type" value="1" /> Я <strong>хочу</strong> получать новости от компании Цвета жизни</label><br>
			<label><input type="radio" name="subscribe_type" value="2" /> Я <strong>не хочу</strong> получать новости от компании Цвета жизни</label></td>
		  </tr>
		  <tr>
			  <td>
				  <div class="control-group">
					  <div class="controls">
						  <p>Нажимая кнопку «Отправить», я подтверждаю свою дееспособность и даю согласие на обработку моих персональных данных в соответствии с <a href="/privacy-policy" target="_blank">политикой безопасности</a> персональных данных. </p>
						  <input type="checkbox" id="subsAccept" class="required" checked="checked" name="subsAccept" value="1" />
					  </div>
				  </div>
			  </td>
		  </tr>
		  <tr>
			<td><input class="btn" type="button" id="submitBtn" value="Отправить" onClick="subscribe('Subscribe')" />&nbsp;&nbsp;
			</td>
		  </tr>
		  <tr>
			<td></td>
		  </tr>
		</table>
	  </form>
	</div>
</div>
<script type="text/javascript">
    bindSubscribeForm();
</script>
