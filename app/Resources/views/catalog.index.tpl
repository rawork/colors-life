{if $param0 == $controller}
{raItems var=cats table=catalog_categories query="publish='on' AND p_id=0"}
<div class="catalog-index-cats">
{foreach from=$cats item=cat}
<span class="cat-item{$cat.id}"><a href="javascript:toggleCatBlock({$cat.id})">{$cat.name}</a></span>
{/foreach}
</div>
{foreach from=$cats item=cat}
{raItems var=subcats table=catalog_categories query="publish='on' AND p_id=`$cat.id`"}
{raCount var=count_subcats table=catalog_categories query="publish='on' AND p_id=`$cat.id`"}
{if count($subcats)}
<div id="index_cat_{$cat.id}" style="display:none">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td class="celector-left-top-{$cat.id}"><img src="/img/0.gif" width="30" height="10" border="0"></td>
    <td class="celector-top-{$cat.id}"><img src="/img/0.gif" width="1" height="10" border="0"></td>
    <td class="celector-right-top-{$cat.id}"><img src="/img/0.gif" width="9" height="10" border="0"></td>
  </tr>
  <tr>
    <td class="celector-left2-{$cat.id}"><img src="/img/0.gif" width="30" height="1" border="0"></td>
    <td class="celector-content-{$cat.id}"><table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td class="white-left-top"><img src="/img/0.gif" width="10" height="9" border="0"></td>
          <td class="white-top"><img src="/img/0.gif" width="1" height="9" border="0"></td>
          <td class="white-right-top"><img src="/img/0.gif" width="10" height="9" border="0"></td>
        </tr>
        <tr>
          <td class="white-right"><img src="/img/0.gif" width="10" height="1" border="0"></td>
          <td class="white-content" style="background-image: url('{$cat.image}')" valign="top">
		  <h4 class="cat-item{$cat.id}"><span>{$cat.name}</span></h4>
		  <table class="subcats-block" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td class="cat-subcats{$cat.id}"><table width="100%" cellpadding="5" cellspacing="0" border="0">
                    <tr> {math assign=maxPerColumn equation="ceil(x/y)" x=$count_subcats y=2}
                      {counter assign=cnt start=1}
                      {foreach from=$subcats item=subcat}
                      {if $cnt == 1}
                      <td>{/if}
                        <div class="cat-level2"><a href="{raURL node=$node.name method=index prms=$subcat.id}"><b>{$subcat.name}</b></a></div>
                        <div class="cat-level3"> {raItems var=subcats2 table=catalog_categories query="publish='on' AND p_id=`$subcat.id`"}
                          {foreach from=$subcats2 item=subcat2}
                          &mdash; <a href="{raURL node=$node.name method=index prms=$subcat2.id}">{$subcat2.name} </a> <br>
                          {/foreach} </div>
                        {if $cnt >= $maxPerColumn}</td>
                      {counter assign=cnt start=1}{else}{counter assign=cnt}{/if}
                      {/foreach} </tr>
                  </table></td>
              </tr>
            </table></td>
          <td class="white-right"><img src="/img/0.gif" width="10" height="1" border="0"></td>
        </tr>
        <tr>
          <td class="white-left-bottom"><img src="/img/0.gif" width="10" height="9" border="0"></td>
          <td class="white-bottom"><img src="/img/0.gif" width="1" height="9" border="0"></td>
          <td class="white-right-bottom"><img src="/img/0.gif" width="10" height="9" border="0"></td>
        </tr>
      </table></td>
    <td class="celector-right-{$cat.id}"><img src="/img/0.gif" width="9" height="1" border="0"></td>
  </tr>
  <tr>
    <td class="celector-left-bottom-{$cat.id}"><img src="/img/0.gif" width="30" height="11" border="0"></td>
    <td class="celector-bottom-{$cat.id}"><img src="/img/0.gif" width="1" height="11" border="0"></td>
    <td class="celector-right-bottom-{$cat.id}"><img src="/img/0.gif" width="9" height="11" border="0"></td>
  </tr>
</table>
</div>
{/if}
            
{/foreach}
{raMethod ref=/catalog/hit.htm}           
{else}
{raItem var=cat table=catalog_categories query=$param0}	
<h1>{$cat.name}</h1>
<div class="cat-description">{$cat.description}</div>		
{if $cat.p_id == 0}			
{raItems var=subcats table=catalog_categories query="publish='on' AND p_id=`$cat.id`"}
{raCount var=count_subcats table=catalog_categories query="publish='on' AND p_id=`$cat.id`"}	
<table class="subcats-block" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td class="cat-subcats{$cat.id}"><table width="100%" cellpadding="5" cellspacing="0" border="0">
		<tr> {math assign=maxPerColumn equation="ceil(x/y)" x=$count_subcats y=2}
			{counter assign=cnt start=1}
			{foreach from=$subcats item=subcat}
			{if $cnt == 1}
			<td>{/if}
			<div class="cat-level2"><a href="{raURL node=$node.name method=index prms=$subcat.id}"><b>{$subcat.name}</b></a></div>
			<div class="cat-level3"> {raItems var=subcats2 table=catalog_categories query="publish='on' AND p_id=`$subcat.id`"}
				{foreach from=$subcats2 item=subcat2}
				&mdash; <a href="{raURL node=$node.name method=index prms=$subcat2.id}">{$subcat2.name} </a> <br>
				{/foreach} </div>
			{if $cnt >= $maxPerColumn}</td>
			{counter assign=cnt start=1}{else}{counter assign=cnt}{/if}
			{/foreach} </tr>
		</table></td>
	</tr>
</table>
{raMethod ref=/catalog/hit.htm} 
{else}
{raItems var=subcats table=catalog_categories query="publish='on' AND p_id=`$cat.id`"}
{raCount var=count_subcats table=catalog_categories query="publish='on' AND p_id=`$cat.id`"}
{if $count_subcats > 0}	
<table class="subcats-block" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td class="cat-subcats{$cats_tree[0].id}"><table width="100%" cellpadding="5" cellspacing="0" border="0">
		<tr> {math assign=maxPerColumn equation="ceil(x/y)" x=$count_subcats y=2}
			{counter assign=cnt start=1}
			{foreach from=$subcats item=subcat}
			{if $cnt == 1}
			<td>{/if}
			<div class="cat-level2"><a href="{raURL node=catalog method=index prms=$subcat.id}">{$subcat.name}</a></div>
			{if $cnt >= $maxPerColumn}</td>
			{counter assign=cnt start=1}{else}{counter assign=cnt}{/if}
			{/foreach} </tr>
		</table></td>
	</tr>
</table>
{raMethod ref=/catalog/hit.htm}
{/if}

{/if}


{if $param1 != 'ord' && $param1 != 'price' && $param1 != 'name'}
{assign var=param1 value=ord}
{/if}
{if !$param2}
{assign var=param2 value="0"}
{else}
{assign var=param2_where value=" AND producer_id=`$param2`"}
{/if}
{if $smarty.get.rtt}
{if $smarty.get.rtt > 48 || $smarty.get.rtt < 6}
{assign var=rtt value=1000}
{else}
{assign var=rtt value=$smarty.get.rtt}
{/if}
{else}
{assign var=rtt value=6}
{/if}
{if $smarty.get.page}
{assign var=page value=$smarty.get.page}
{else}
{assign var=page value=1}
{/if}

{if $cat}
{raSetVar var=title value=$cat.name}
{/if}

{raPaginator var=paginator table=catalog_stuff query="publish='on' AND c_id=`$param0` `$param2_where`" pref="`$ref``$mname`.`$param0`.`$param1`.`$param2`.htm?page=###&rtt=`$rtt`" per_page=$rtt page=$page tpl=public}
{raItems var=items table=catalog_stuff query="c_id=`$cat.id` AND publish='on' `$param2_where`" limit=$paginator->limit sort="is_exist DESC,`$param1`"}


{if count($items)}
<table class="stuff-selector" width="100%" cellpadding="5" cellspacing="0" border="0">
  <tr>
                <td width="40%">Сортировать по: 
      {if $param1 != 'price' && $param1 != 'name'} <a href="{raURL node=catalog method=$mname prms="`$param0`.price"}">цене</a> <a href="{raURL node=catalog method=$mname prms="`$param0`.name"}">названию</a> {elseif $param1 == 'price'} <span>цене</span> <a href="{raURL node=catalog method=$mname prms="`$param0`.name"}">названию</a> {else} <a href="{raURL node=catalog method=$mname prms="`$param0`.price"}">цене</a> <span>названию</span> {/if} </td>
                <td width="60%" align="right">Показать товары: <span>таблицей</span> <a href="{raURL node=$node.name method=list prms=$param0}">списком</a> по
      <select name="cpage" onChange="setCatalogRTT(this, {$rtt}, {$page})">
                    <option value="6"{if $rtt == 6} selected{/if}>6</option>
                    <option value="12"{if $rtt > 6 && $rtt <= 12} selected{/if}>12</option>
                    <option value="24"{if $rtt > 12 && $rtt <= 24} selected{/if}>24</option>
                    <option value="48"{if $rtt > 24 && $rtt <= 48} selected{/if}>48</option>
                    <option value="1000"{if $rtt > 48 || $rtt < 6} selected{/if}>Все</option>
                  </select>
      на страницу </td>
              </tr>
</table>
{if is_object($paginator)}{$paginator->getText()}{/if}

<table class="stuff-table" cellpadding="0" cellspacing="0" border="0">
	{counter assign=cnt start=1}
	{foreach from=$items item=item}

	{if $cnt == 1}
	<tr>{/if}
	<td style="width:50%;padding:8px;vertical-align:top;height:100%;"><table style="height:100%;" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><img src="/img/stuff_lt.gif"></td>
			<td class="stuff-t"></td>
			<td><img src="/img/stuff_rt.gif"></td>
		<tr>
			<td class="stuff-l"></td>	
			<td class="stuff-content"><table style="height:100%" width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
				<td class="stuff-image"><a href="{raURL node=catalog method=stuff prms=$item.id}">{if $item.small_image}<img src="{$item.small_image}">{else}<img src="/img/noimage_small.jpg">{/if}</a></td>
				<td class="stuff-description"><table width="100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td height="100%" valign="top"><div class="stuff-name"><a href="{raURL node=catalog method=stuff prms=$item.id}"><span>{$item.name}</span></a></div>
						<div class="stuff-producer"><a href="{raURL node=catalog method=brand prms=$item.producer_id}">{$item.producer_id_name}</a> ({$item.producer_id_country})</div>
						{if $item.spec_description}
						<div class="stuff-description">{$item.spec_description}</div>
						{/if}

						<div class="stuff-exist">{if $item.is_exist}<img src="/img/vnalich.png">{else}<img src="/img/zakaz.png">{/if}</div>
						{if !$item.is_exist}<div class="know-when">
						<a href="#" onclick="return showMailForm({$item.id})">Узнать, когда появится в продаже</a>
						<div id="mailblock{$item.id}"><input type="text" onkeypress="checkEmailForm({$item.id})" onkeyup="checkEmailForm({$item.id})" onblur="procBlurEmail(this, 'Электронная почта', {$item.id})" onfocus="procFocus(this, 'Электронная почта')" value="Электронная почта" class="width-200" name="email{$item.id}" id="email{$item.id}"> <input type="button" class="btnEmail" id="btnEmail{$item.id}" onclick="sendStuffExist({$item.id})" disabled="true" value="Оставить заявку" /></div>
						</div>{/if}
						{raItems var=prices table=catalog_prices query="stuff_id=`$item.id` AND publish='on'" sort="ord,size_id"}
			{if count($prices)}
			<div class="stuff-sizes">
			Размерный ряд:<br> 
			<select name="stuff_price_{$item.id}" id="stuff_price_{$item.id}" onchange="setPrice({$item.id})">
			<option rel="{if $item.spec_price == '0.00'}{$item.price}{else}{$item.spec_price}{/if}" value="0">...</option>
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
								<table class="stuff-cart" width="100%" cellpadding="0" cellspacing="0" border="0">
								<tr>
									<td colspan="2">
									{if $item.spec_price != '0.00'}
									<div class="stuff-price-no"><span>{$item.price}</span> руб.</div>
									<div class="stuff-price"><span id="price_{$item.id}">{$item.spec_price}</span> руб.</div>
									{else}
									<div class="stuff-price"><span id="price_{$item.id}">{$item.price}</span> руб.</div>
									{/if}
									</td>
								</tr>
								<tr>
									<td width="100%"> Кол-во
									<input type="text" name="amount_{$item.id}" id="amount_{$item.id}" style="width:30px;" value="1">
									<a href="javascript:addCartItem({$item.id})">Купить</a></td>
									<td><a href="javascript:addCartItem({$item.id})"><img src="/img/cart0.gif" style="margin:0;" border="0"></a></td>
								</tr>
								</table></td>
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
	{if $cnt==2}</tr>
	{counter assign=cnt start=1}{else}{counter assign=cnt}{/if}
	{/foreach}
</table>
{if is_object($paginator)}{$paginator->getText()}{/if}
{/if} 
{/if}