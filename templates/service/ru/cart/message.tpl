<h1><span>Ваша корзина</span> &rarr; <span>Информация о Вас</span> &rarr; <span>Оплата и доставка</span> &rarr; <span>Подтверждение</span> &rarr; Информация о заказе</h1>
            <br>
<p>&nbsp;</p>
<div class="cart-message">  
  <p>Спасибо, Заказ <strong>№{$order_number}</strong> принят!</p>
  <p>В ближайшее время с Вами свяжется наш менеджер для подтверждения заказа!</p>
  {if $smarty.session.pay_type == 'Квитанция банка'}<p><a href="/notice.php?order={$order_number-100000}" target="_blank">Распечатать квитанцию</a></p>{/if}
  <p>&nbsp;</p>
</div>  
