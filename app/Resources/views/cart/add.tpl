{if $product}
<div class="cart-add"> Товар успешно добавлен в корзину!<br><br><br>
	<input type="button" class="btn btn-warning btn-large" onclick="window.location = '{raURL node=cart}'" value="Оформить заказ">&nbsp;&nbsp;
	<input type="button" class="btn btn-large" onclick="return closePopUp('popup')" value="Продолжить покупки">
</div>
{else}
Ошибка добавления товара!
{/if}

