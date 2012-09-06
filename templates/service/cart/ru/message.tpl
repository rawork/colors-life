<h1>Ваш заказ принят</h1>
<br>
<p>&nbsp;</p>
<div class="cart-message">  
  <p>Спасибо, Заказ <strong>№{$order_number}</strong> принят!</p>
  <p>В ближайшее время с Вами свяжется наш менеджер для подтверждения заказа!</p>
  {if $smarty.session.pay_type == 2}<p><a href="/notice.php?order={$order_number-$base_number}" target="_blank">Распечатать квитанцию</a></p>{/if}
  <p>&nbsp;</p>
</div> 
<!--Трэкер "Покупка"-->

<script>document.write('<img src="http://mixmarket.biz/tr.plx?e=3779408&r='+escape(document.referrer)+'&t='+(new Date()).getTime()+'" width="1" height="1"/>');</script>

<!--Трэкер "Покупка"--> 
