<h4>Ваш заказ принят</h4>
<br>
<p>&nbsp;</p>
<div class="cart-message">  
  <p>Спасибо, Заказ <strong>№{$orderNumber}</strong> принят!</p>
  <p>В ближайшее время с Вами свяжется наш менеджер для подтверждения заказа!</p>
  <p>&nbsp;</p>
  <p>Рекомендуем Вам подписаться на <a href="{raURL node=subscribe-process}">рассылку</a> 
	для получения новостей, информации об акциях и скидках от интернет-магазина "Цвета жизни".</p>
  {if $smarty.session.payType == 2}<p><a href="{raURL node=notice}/{$orderNumber}" target="_blank">Распечатать квитанцию</a></p>{/if}
  <p>&nbsp;</p>
</div> 
<!--Трэкер "Покупка"-->
<script>document.write('<img src="http://mixmarket.biz/tr.plx?e=3779408&r='+escape(document.referrer)+'&t='+(new Date()).getTime()+'" width="1" height="1"/>');</script>
<!--Трэкер "Покупка"--> 
