{if $methodName == 'stuff'}
{raItems var=items table=catalog_product nquery="SELECT s.* FROM catalog_category t1 JOIN catalog_product s ON t1.id=s.category_id WHERE s.publish=1 AND s.is_hit=1 AND t1.root_id=`$item.category_id_root_id` AND s.id<>`$item.id` ORDER BY RAND() LIMIT `$settings.limit_hit`" }
{if count($items)}
<h3><span>Хиты продаж:</span></h3>

			{foreach from=$items item=item2}
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
                            <td class="stuff-title"><a class="screenshot" href="{raURL node=catalog method=stuff prms=$item2.id}" rel="{$item2.small_imagenew}">{$item2.name}</a>
							{raItems var=prices table=catalog_price query="product_id=`$item2.id`"}
							{if count($prices)}
							&nbsp;&nbsp;<select name="stuff_price_{$item2.id}" id="stuff_price_{$item2.id}" onchange="setPrice({$item2.id})">
							<option rel="{if $item.discount_price == '0.00'}{$item.price}{else}{$item.discount_price}{/if}" value="0">...</option>
							{foreach from=$prices item=price}
							<option rel="{$price.price}" value="{$price.id}">{$price.size_id_name} {if $price.color_id}- {$price.color_id_name}{/if} - {$price.price} руб.</option>
							{/foreach}
							{else}
							<input type="hidden" value="0" name="stuff_price_{$item2.id}" id="stuff_price_{$item2.id}">
							{/if}
</td>
                            <td style="background:url('/img/price_cart_bg2_left.gif') left top no-repeat;"><img src="/img/0.gif" width="4" height="1"></td>
                            <td class="stuff-description2"><table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                  <td><table class="stuff-cart-list" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
										<td colspan="3"><img src="/img/0.gif" width="244" height="1" border="0"></td>
										</tr>
                                        <tr>
                                          <td style="white-space:nowrap;"><div class="stuff-price"><span id="price_{$item2.id}">{if $item2.discount_price != '0.00'}{$item2.discount_price}{else}{$item2.price}{/if}</span> руб.</div></td>
                                          <td style="white-space:nowrap;"> Кол-во
                                           <input type="text" name="amount_{$item2.id}" id="amount_{$item2.id}" style="width:30px;" value="1">
                                            <a href="javascript:addCartItem({$item2.id})">Купить</a></td>
                                          <td><a href="javascript:addCartItem({$item2.id})"><img src="/img/cart0.gif" style="margin:0;" border="0"></a></td>
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
                                  <td height="100%" style="background:url('/img/price_cart_bg2_right.gif') bottom right no-repeat;"><img src="/img/0.gif" width="11" height="1"></td>
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
 {/if}
{else}
{if empty($param0)}
{raItems var=items table=catalog_product query="publish=1 AND is_hit=1" limit=$settings.limit_hit sort="RAND()"}
{else}
{raItem var=cat table=catalog_category query=$param0}	
{raIds var=ids query="SELECT s.id FROM catalog_category t1 JOIN catalog_product s ON t1.id=s.category_id WHERE s.publish=1 AND s.is_hit=1 AND t1.root_id=`$cat.root_id` ORDER BY RAND() LIMIT `$settings.limit_hit`"}
{raItems var=items table=catalog_product query="id IN (`$ids`)" sort="RAND()"}
{/if}
{if count($items)}
<div class="spec-link"><a href="javascript:void(0)">Хиты продаж</a></div>       
<table class="stuff-table" cellpadding="0" cellspacing="0" border="0">
              {counter assign=cnt start=1}
              {foreach from=$items item=item}
              {if $cnt==1}
              <tr>{/if}
                <td style="width:50%;padding:8px; vertical-align:top;height:100%;"><table style="height:100%" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td><img src="/img/stuff_lt.gif"></td>
					  <td class="stuff-t"></td>
					  <td><img src="/img/stuff_rt.gif"></td>
                    </tr>
					<tr>
					  <td class="stuff-l"></td>
  					  <td class="stuff-content"><table style="height:100%" width="100%" cellpadding="0" cellspacing="0" border="0">
                          <tr>
                            <td class="stuff-image"><a href="{raURL node=catalog method=stuff prms=$item.id}">{if $item.small_imagenew}<img src="{$item.small_imagenew}">{else}<img src="/img/noimage_small.jpg">{/if}</a></td>
                            <td class="stuff-description"><table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                
                                {raItem var=cat0 table=catalog_category query=$item.category_id_root_id}
                                
                                  <td height="100%" valign="top"><div class="stuff-cat" style="background-image:url('{$cat0.logo}');"><a href="{raURL node=catalog method=index prms=$item.category_id}">{$item.category_id_title}</a></div>
                                    <div class="stuff-name"><a href="{raURL node=catalog method=stuff prms=$item.id}"><span>{$item.name}</span></a></div>
                                    <div class="stuff-producer"><a href="{raURL node=catalog method=brand prms=$item.producer_id}">{$item.producer_id_name}</a> ({$item.producer_id_country})</div>
									<div class="stuff-description">{$item.preview}</div>
									
									<div class="stuff-exist">{if $item.is_exist}<img src="/img/vnalich.png">{else}<img src="/img/zakaz.png">{/if}</div>
									{raItems var=prices table=catalog_price query="product_id=`$item.id` AND publish=1" sort="sort,size_id"}
                        {if count($prices)}
                        <div class="stuff-sizes">
                        Размерный ряд:<br> 
                        <select name="stuff_price_{$item.id}" id="stuff_price_{$item.id}" onchange="setPrice({$item.id})">
                        <option rel="{if $item.discount_price == '0.00'}{$item.price}{else}{$item.discount_price}{/if}" value="0">...</option>
                        {foreach from=$prices item=price}
                        <option rel="{$price.price}" value="{$price.id}">{$price.size_id_name} {if $price.color_id}- {$price.color_id_name}{/if} - {$price.price} руб.</option>
                        {/foreach} 
                        </div>
                        <br /><br /><br />
                        {else}
<input type="hidden" value="0" name="stuff_price_{$item.id}" id="stuff_price_{$item.id}">    {/if}
                                    </td>
                                </tr>
                                <tr>
                                  <td><table width="100%" cellpadding="0" cellspacing="0" border="0">
                                      <tr>
                                        <td width="100%"><img src="/img/0.gif" width="1" height="83" style="display:block;"></td>
                                        <td><img src="/img/0.gif" width="128" height="1" style="display:block;">
                                            <table class="stuff-cart" width="100%" cellpadding="0" cellspacing="0">
                                              <tr><td colspan="2">
											  {if $item.discount_price != '0.00'}
											  <div class="stuff-price-no"><span>{$item.price}</span> руб.</div>
											  <div class="stuff-price"><span id="price_{$item.id}">{$item.discount_price}</span> руб.</div>
											  {else}
											  <div class="stuff-price"><span id="price_{$item.id}">{$item.price}</span> руб.</div>
											  {/if}
											  </td></tr>
											  <tr>
                                                <td width="100%"> Кол-во
                                                  <input type="text" name="amount_{$item.id}" id="amount_{$item.id}" style="width:30px;" value="1">
                                                  <a href="javascript:addCartItem({$item.id})">Купить</a></td>
                                                <td><a href="javascript:addCartItem({$item.id})"><img src="/img/cart0.gif" style="margin:0;" border="0"></a></td>
                                              </tr>
                                            </table>
                                          </td>
                                      </tr>
                                    </table></td>
                                </tr>
                              </table></td>
                          </tr>
                        </table></td>
					  <td class="stuff-r"></td>
					</tr>
					<tr>		
                      <td><img src="/img/stuff_lb.gif"></td>
                      <td class="stuff-b"></td>
					  <td><img src="/img/stuff_rb.gif"></td>
                    </tr>
                  </table></td>
                {if $cnt < 2} {counter assign=cnt} {else}</tr>
              {counter assign=cnt start=1}{/if}
              {/foreach}
            </table>

{/if}
{/if}