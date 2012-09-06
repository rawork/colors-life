{raSetVar var=h1 value='Ваша корзина'}
{raSetVar var=current_step value=1}
{if sizeof($goods) > 0}
<h1>Ваша корзина &rarr; <span>Информация о Вас</span> &rarr; <span>Оплата и доставка</span> &rarr; <span>Подтверждение</span> &rarr; <span>Информация о заказе</span></h1>
          <br>  
              <form name="frmCart" id="frmCart" action="./?action=recalc" method="post">
			  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td height="100%">
				    {foreach from=$goods key=guid item=g}
                    
					<table class="stuff-table" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td><table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                          <td height="100%"><table style="height:100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td><img src="/img/stuff_lt.gif"></td>
                            </tr>
                            <tr>
                              <td height="100%" style="background:url('/img/stuff_l2.gif') no-repeat left bottom;"></td>
                          	</tr>
                          </table></td>
                          <td width="100%"><table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td class="stuff-title"><a href="{raURL node=catalog method=stuff prms=$g.stuff.id}">{$g.stuff.name}</a>
                              {raItems var=prices table=catalog_prices query="stuff_id=`$g.stuff.id`"}
                        {if count($prices)}
                        &nbsp;&nbsp;<select name="price_{$guid}" id="price_{$guid}">
                        <option value="0">Стандарт</option>
                        {foreach from=$prices item=price}
                        <option{if $price.id == $g.priceEntity.id} selected{/if} value="{$price.id}">{$price.size_id_name} {if $price.color_id}- {$price.color_id_name}{/if} - {$price.price} руб.</option>
                        {/foreach} 
                        
                        {/if}
                              </td>
                              <td style="background:url('/img/price_cart_bg2_left.gif') left top no-repeat;"><img src="/img/0.gif" width="4" height="1"></td>
                              <td class="stuff-description2">
							  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                  <td><img src="/img/0.gif" width="244" height="1" border="0" style="display:block;">
                                    <table class="stuff-cart" width="100%" cellpadding="0" cellspacing="0">
                                      <tr>
                                        <td style="padding-left:10px;"><div class="stuff-price"><span>{$g.price|number_format:2:',':' '}</span> руб.</div></td>
                                        <td> Кол-во
                                          <input type="text" style="width:30px;" value="{$g.counter}" name="amount_{$guid}">
                                          </td>
                                        <td><a href="./?action=recalc&amount_{$guid}=0"><img src="/img/delete_btn.gif" style="margin:0;" border="0"></a></td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                              </table></td>
                              <td height="100%"><table width="100%" style="height:100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                  <td><img src="/img/price_cart_bg2_right_top.gif"></td>
                                </tr>
                                <tr>
                                  <td height="100%" style="background:url('/img/price_cart_bg2_right.gif') bottom right no-repeat;"><img src="/img/0.gif" width="11" height="40"></td>
                                </tr>
                              </table></td>
                            </tr>
                          </table></td>
                        </tr>
                      </table></td>
                    </tr>
                  </table>
                    <br />
					{/foreach}
                    
                    </td>
                </tr>
              </table>
            </form>
            <div class="cart-total">Всего {$smarty.session.number} товара(ов) на сумму: <span>{$list_total|number_format:2:',':' '} руб.</span></div>
			{if is_array($discount)}<div class="cart-total">Для суммы товара от {$discount.sum_min|number_format:2:',':' '} до {$discount.sum_max|number_format:2:',':' '} руб. действует скидка <b style="color:#EE2A2A;font-size:14px;">{$discount.discount} %</b></div>
			<div class="cart-total">Сумма с учетом скидки: <span>{$list_total-$list_total*$discount.discount/100|number_format:2:',':' '} руб.</span></div>{/if}
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
            <td width="50%"><a href="#" onclick="document.frmCart.submit();return false;"><img src="/img/calc_btn.gif"></a></td>
            <td width="50%" align="right"><a href=""><a href="#" onclick="window.location = '/cart/?action=info';return false;"><img src="/img/next_btn.gif"></a></td>
            </tr>
            </table>
            <br>
			{foreach from=$gifts item=gift}	
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				
			<tr>
                                  <td height="100%"><table style="height:100%" cellpadding="0" cellspacing="0" border="0">
                                      <tr>
                                        <td><img src="/img/stuff_lt_gift.gif"></td>
                                      </tr>
                                      <tr>
                                        <td height="100%" style="background:url('/img/stuff_l_gift.gif') no-repeat left bottom;"></td>
                                      </tr>
                                    </table></td>
                                  <td rowspan="2" class="stuff-content2"><table style="height:100%" width="100%" cellpadding="0" cellspacing="0" border="0">
                                      <tr>
                                        <td class="stuff-image2"><a href="{raURL node=catalog method=stuff prms=$gift.gift_id}">{if $gift.gift_id_small_image}<img src="{$gift.gift_id_small_image}">{/if}</a></td>
                                        <td class="stuff-description3"><table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
												{raItem var=item table=catalog_stuff query=$gift.gift_id}
                                              {if $item.c_id_p_id == 0}
                                {raItem var=cat0 table=catalog_categories query=$item.c_id}
                                {else}
                                {raItem var=cat0 table=catalog_categories query=$item.c_id_p_id}
                                {/if}
                                  <td height="100%" valign="top"><div class="stuff-cat" style="background-image:url('{$cat0.logo}');"><a href="{raURL node=catalog method=index prms=$item.c_id}">{$item.c_id_name}</a></div>

											    
                                                <div class="stuff-name"><a href="{raURL node=catalog method=stuff prms=$gift.gift_id}">{$gift.gift_id_name}</a></div>
                                                <div class="stuff-description"></div></td>
                                            </tr>
                                            <tr>
                                              <td><table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                  <tr>
                                                    <td width="100%"></td>
                                                    <td><img src="/img/0.gif" width="128" height="1" border="0">
                                                      <div class="stuff-price-gift"><span>Подарок</span></div>
                                                      </td>
                                                  </tr>
                                                </table></td>
                                            </tr>
                                          </table></td>
                                      </tr>
                                    </table></td>
                                  <td height="100%"><table style="height:100%" cellpadding="0" cellspacing="0" border="0">
                                      <tr>
                                        <td><img src="/img/stuff_rt_gift.gif"></td>
                                      </tr>
                                      <tr>
                                        <td height="100%" style="background:url('/img/stuff_r_gift.gif') no-repeat left bottom;"></td>
                                      </tr>
                                    </table></td>
            </tr>
			
            </table><br>{/foreach}

{else}
<div class="lst-item2">
<h1>В вашей корзине нет товаров</h1>
{raInclude var=delivery}
</div>
{/if}