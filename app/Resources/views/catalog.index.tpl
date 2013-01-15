{if empty($param0)}
	{raItems var=cats table=catalog_category query="publish=1 AND parent_id=0"}
	<div class="catalog-index-cats">
	{foreach from=$cats item=cat}
	<a class="cat-item{$cat.id}" href="javascript:toggleCatBlock({$cat.id})">{$cat.title}</a>
	{/foreach}
	</div>
	{foreach from=$cats item=cat}
	{raItems var=subcats table=catalog_category query="publish=1 AND parent_id=`$cat.id`"}
	{raCount var=count_subcats table=catalog_category query="publish=1 AND parent_id=`$cat.id`"}
	{if count($subcats)}
	<div class="index-category-back cat{$cat.id}" id="index_cat_{$cat.id}" style="display:none">
	<div class="index-category" style="background-image: url('{$cat.image}')">
		<h4 class="cat-item{$cat.id}"><span>{$cat.title}</span></h4>
		<table class="subcats-block" width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
			<td class="cat-subcats{$cat.id}">
				<table width="100%" cellpadding="5" cellspacing="0" border="0">
				<tr> {math assign=maxPerColumn equation="ceil(x/y)" x=$count_subcats y=2}
					{counter assign=cnt start=1}
					{foreach from=$subcats item=subcat}
					{if $cnt == 1}<td>{/if}
					<div class="cat-level2"><a href="{raURL node=$node.name method=index prms=$subcat.id}"><b>{$subcat.title}</b></a></div>
					<div class="cat-level3"> {raItems var=subcats2 table=catalog_category query="publish=1 AND parent_id=`$subcat.id`"}
						{foreach from=$subcats2 item=subcat2}
						&mdash; <a href="{raURL node=$node.name method=index prms=$subcat2.id}">{$subcat2.title} </a> <br>
						{/foreach} </div>
					{if $cnt >= $maxPerColumn}</td>
					{counter assign=cnt start=1}{else}{counter assign=cnt}{/if}
					{/foreach} 
				</tr>
				</table>
			</td>
			</tr>
		</table>
	</div>	
	</div>
	{/if}
	{/foreach}
	{raMethod ref=/catalog/hit.htm}           
{else}
	{raItem var=cat table=catalog_category query=$param0}
	{raSetVar var=title value=$cat.title}
	<h1>{$cat.title}</h1>
	<div class="cat-description">{$cat.description}</div>		
	{if $cat.parent_id == 0}			
	{raItems var=subcats table=catalog_category query="publish=1 AND parent_id=`$cat.id`"}
	{raCount var=count_subcats table=catalog_category query="publish=1 AND parent_id=`$cat.id`"}	
	<table class="subcats-block" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
		<td class="cat-subcats{$cat.id}"><table width="100%" cellpadding="5" cellspacing="0" border="0">
			<tr> {math assign=maxPerColumn equation="ceil(x/y)" x=$count_subcats y=2}
				{counter assign=cnt start=1}
				{foreach from=$subcats item=subcat}
				{if $cnt == 1}
				<td>{/if}
				<div class="cat-level2"><a href="{raURL node=$node.name method=index prms=$subcat.id}"><b>{$subcat.title}</b></a></div>
				<div class="cat-level3"> {raItems var=subcats2 table=catalog_category query="publish=1 AND parent_id=`$subcat.id`"}
					{foreach from=$subcats2 item=subcat2}
					&mdash; <a href="{raURL node=$node.name method=index prms=$subcat2.id}">{$subcat2.title} </a> <br>
					{/foreach} </div>
				{if $cnt >= $maxPerColumn}</td>
				{counter assign=cnt start=1}{else}{counter assign=cnt}{/if}
				{/foreach} </tr>
			</table></td>
		</tr>
	</table>
	{raMethod ref=/catalog/hit.htm} 
	{else}
	{raItems var=subcats table=catalog_category query="publish=1 AND parent_id=`$cat.id`"}
	{raCount var=count_subcats table=catalog_category query="publish=1 AND parent_id=`$cat.id`"}
	{if $count_subcats > 0}	
	<table class="subcats-block" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
		<td class="cat-subcats{$cats_tree[0].id}">
			<table width="100%" cellpadding="5" cellspacing="0" border="0">
			<tr> {math assign=maxPerColumn equation="ceil(x/y)" x=$count_subcats y=2}
				{counter assign=cnt start=1}
				{foreach from=$subcats item=subcat}
				{if $cnt == 1}<td>{/if}
				<div class="cat-level2"><a href="{raURL node=catalog method=index prms=$subcat.id}">{$subcat.title}</a></div>
				{if $cnt >= $maxPerColumn}</td>
				{counter assign=cnt start=1}{else}{counter assign=cnt}{/if}
				{/foreach} 
			</tr>
			</table>
	    </td>
		</tr>
	</table>
	{raMethod ref=/catalog/hit.htm}
	{/if}
{/if}


{if $param1 != 'sort' && $param1 != 'price' && $param1 != 'name'}
{assign var=param1 value=sort}
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

{if $cat.title}
{raSetVar var=title value=$cat.title}
{/if}
{raLinkedItems var=products table=catalog_products_categories query="category_id=`$cat.id`" value=product_id}
{if $products} 
{assign var=products_where value=" OR id IN(`$products`)"}	
{/if}
{raPaginator var=paginator table=catalog_product query="publish=1 AND (category_id=`$cat.id`  `$products_where`)" pref="index.`$param0`.`$param1`.htm?page=###&rtt=`$rtt`" per_page=$rtt page=$page tpl=public}
{raItems var=items table=catalog_product query="(category_id=`$cat.id`  `$products_where`) AND publish=1" limit=$paginator->limit sort="is_exist DESC,`$param1`"}

{if count($items)}
<table class="product-selector" width="100%">
	<tr>
		<td width="40%">Сортировать по: 
			{if $param1 != 'price' && $param1 != 'name'} 
			<a href="{raURL node=catalog method=$methodName prms="`$param0`.price"}">цене</a> 
			<a href="{raURL node=catalog method=$methodName prms="`$param0`.name"}">названию</a> 
			{elseif $param1 == 'price'} 
			<strong>цене</strong> 
			<a href="{raURL node=catalog method=$methodName prms="`$param0`.name"}">названию</a> 
			{else} 
			<a href="{raURL node=catalog method=$methodName prms="`$param0`.price"}">цене</a> 
			<strong>названию</strong> 
			{/if} 
		</td>
		<td width="60%" align="right">
			по
			<select style="height: 26px;" class="span7" name="cpage" onChange="setCatalogRTT(this, {$rtt}, {$page})">
				<option value="6"{if $rtt == 6} selected{/if}>6</option>
				<option value="12"{if $rtt > 6 && $rtt <= 12} selected{/if}>12</option>
				<option value="24"{if $rtt > 12 && $rtt <= 24} selected{/if}>24</option>
				<option value="48"{if $rtt > 24 && $rtt <= 48} selected{/if}>48</option>
				<option value="1000"{if $rtt > 48 || $rtt < 6} selected{/if}>Все</option>
			</select> на страницу 
		</td>
	</tr>
</table>
<table class="product-table" cellpadding="0" cellspacing="0" border="0">
	{foreach from=$items item=item name=product}
	{raItem var=cat0 table=catalog_category query=$item.category_id_root_id}
	{raItems var=prices table=catalog_price query="product_id=`$item.id` AND publish=1" sort="sort,size_id"}
	{if $smarty.foreach.product.iteration == 1}<tr>{/if}
		<td class="product-content">
			<div class="product-image pull-left">
				<a href="{raURL node=catalog method=stuff prms=$item.id}">{if $item.small_imagenew}<img src="{$item.small_imagenew}">{else}<img src="/img/noimage_small.jpg">{/if}</a>
			</div>
			<div class="product-description pull-left">
				<div class="product-title"><a href="{raURL node=catalog method=stuff prms=$item.id}"><span>{$item.name}</span></a></div>	
				<div class="product-producer"><a href="{raURL node=catalog method=brand prms=$item.producer_id}">{$item.producer_id_name}</a> ({$item.producer_id_country})</div>
				{if $item.discount_price != '0.00'}
				<div class="product-price-no">{$item.price} руб.</div>
				<div class="product-price">{if count($prices)}от {/if}<span id="price_{$item.id}">{$item.discount_price}</span> руб.</div>
				{else}
				<div class="product-price">{if count($prices)}от {/if}<span id="price_{$item.id}">{$item.price}</span> руб.</div>
				{/if}
				<input type="hidden" id="amount_{$item.id}" value="1">
				<a class="btn btn-warning btn-large" href="javascript:addCartItem({$item.id})">Купить</a>
				{if count($prices)}
				<div class="product-sizes">
				<h5>Размерный ряд</h5> 
				<select class="span35" id="product_price_{$item.id}" onchange="setPrice({$item.id})">
				<option rel="{if $item.discount_price == '0.00'}{$item.price}{else}{$item.discount_price}{/if}" value="0">...</option>
				{foreach from=$prices item=price}
				<option rel="{$price.price}" value="{$price.id}">Размер: {$price.size_id_name}{if $price.color_id}, цвет: {$price.color_id_name}{/if} - {$price.price|number_format:2:',':' '} руб.</option>
				{/foreach}
				</select>
				</div>
				{else}
				<input type="hidden" value="0" id="product_price_{$item.id}">                        
				{/if}
				<div class="product-exists">{if $item.is_exist}<img src="/img/vnalich.png">{else}<img src="/img/zakaz.png">{/if}</div>
			</div>
			<div class="clearfix"></div>
		</td>
	{if $smarty.foreach.product.iteration % 2 == 0}</tr><tr>{/if}
	{/foreach}
</table>
{if is_object($paginator)}{$paginator->render()}{/if}
{/if} 
{/if}