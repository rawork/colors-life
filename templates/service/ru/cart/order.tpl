{if sizeof($goods)}
            <h1><a href="/cart/">Ваша корзина</a> &rarr; <a href="/cart/?action=info">Информация о Вас</a> &rarr; <a href="/cart/?action=delivery">Оплата и доставка</a> &rarr; Подтверждение &rarr; <span>Информация о заказе</span></h1>
            <br>
            <!--<div class="cart-order-total">Ваш заказ на сумму 
			{if is_array($discount)}<span>{$list_total-$list_total*$discount.discount/100|number_format:2:',':' '} руб.</span> (с учетом скидки: <b>{$discount.discount}%</b>){else}
			<span>{$list_total|number_format:2:',':' '} руб.</span>{/if}
			</div> -->
<form name="frmCart" id="frmCart" action="./?action=order" method="post">
<input type="hidden" name="submited" value="1"> 
 <table class="cart-order-stuff" width="100%" cellpadding="0" cellspacing="0" border="0">
              {foreach from=$goods item=g}
			  <tr>
                <td><a target="_blank" href="{raURL node=$g.stuff.dir_id_name method=stuff prms=$g.stuff.id}">{$g.stuff.name}</a>
				{if $g.priceEntity.id} 
                <div class="stuff-sizes">Вариант исполнения: {$g.priceEntity.size_id_name} - {$g.priceEntity.color_id_name}</div>
                {/if}
                </td>
                <td>{$g.price|number_format:2:',':' '} руб.</td>
                <td>{$g.counter} шт.</td>
                <td>{$g.price*$g.counter|number_format:2:',':' '} руб.</td>
              </tr>
			  {/foreach}
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
            </table>
			<div class="cart-total">Всего {$smarty.session.number} товара(ов) на сумму: <span>{$list_total|number_format:2:',':' '} руб.</span></div>
			{if is_array($discount)}<div class="cart-total">Для суммы товара от {$discount.sum_min|number_format:2:',':' '} до {$discount.sum_max|number_format:2:',':' '} руб. действует скидка <b style="color:#EE2A2A;font-size:14px;">{$discount.discount} %</b></div>
			<div class="cart-total">Сумма с учетом скидки: <span>{$list_total-$list_total*$discount.discount/100|number_format:2:',':' '} руб.</span></div>{/if}
			{if count($gifts)}<h3><span>Подарки:</span></h3>
			<table class="cart-order-stuff" width="100%" cellpadding="0" cellspacing="0" border="0">
              {foreach from=$gifts item=gift}
			  <tr>
                <td><a target="_blank" href="{raURL node=catalog method=stuff prms=$gift.gift_id}">{$gift.gift_id_name}</a></td>
              </tr>
			  {/foreach}
              <tr>
                <td>&nbsp;</td>
              </tr>
            </table>{/if}
            <div class="cart-contacts">
			<b>ФИО:</b> {$uauth->user.name}<br>
			<b>Телефон:</b> {$uauth->user.phone}<br>
			<b>E-mail:</b> {$uauth->user.email}<br>
			<b>Способ оплаты:</b> {$smarty.session.pay_type}<br>
            <b>Доставка:</b> {$smarty.session.delivery_type}<br>
            <b>Адрес доставки:</b> {$smarty.session.delivery_address}<br>
			<b>Примечания:</b>
			<textarea name="additions" style="width:100%;height:70px" ></textarea>
            </div>
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="50%">&nbsp;</td>
                <td width="50%" align="right"><a href=""><a href="#" onClick="document.frmCart.submit();return false;"><img src="/img/order_btn.gif"></a></td>
              </tr>
            </table>
</form>
{else}
<div class="lst-item2">
<h1>В вашей корзине нет товаров</h1>
{raInclude var=delivery}
</div>
{/if}