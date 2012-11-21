{if $smarty.session.number}
<span>{$smarty.session.number}</span> товар{$wordEnd}<br>
на сумму <span>{$smarty.session.summa}</span> руб.
<div class="head-cart-link"><a href="/cart/">Оформить заказ</a></div>
{else}
Нет выбранных <br> товаров{/if}

