{raItems var=items table=catalog_product query="publish=1 AND is_new=1" limit=$settings.limit_new}
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
                            <td class="stuff-image"><a href="{raURL node=catalog method=stuff prms=$item.id}">{if $item.small_image}<img src="{$item.small_image}">{else}<img src="/img/noimage_small.jpg">{/if}</a></td>
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
<input type="hidden" value="0" name="stuff_price_{$item.id}" id="stuff_price_{$item.id}">    
                        {/if}
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
