{if !$cat}
	<div class="catalog-index-cats">
	{foreach from=$cats item=cat}
	<a class="cat-item{$cat.id}" href="javascript:toggleCatBlock({$cat.id})">{$cat.title}</a>
	{/foreach}
	</div>
	{foreach from=$cats item=cat}
	<div class="index-category-back cat{$cat.id}" id="index_cat_{$cat.id}" style="display:none">
	<div class="index-category" style="background-image: url('{$cat.image}')">
		<h4 class="cat-item{$cat.id}"><span>{$cat.title}</span></h4>
		<table class="subcats-block" width="640">
			<tr>
			<td width="50%" class="cat-subcats{$cat.id}">
				<table width="100%" cellpadding="5">
				<tr> 
					{counter assign=cnt start=1}
					{foreach from=$cat.children item=subcat}
						{if $cnt == 1}<td>{/if}
						<div class="cat-level2">
							<a href="{raURL node=$node.name method=index prms=$subcat.id}"><strong>{$subcat.title}</strong></a>
						</div>
						<div class="cat-level3"> 
							{foreach from=$subcat.children item=subcat2}
							&mdash; <a href="{raURL node=$node.name method=index prms=$subcat2.id}">{$subcat2.title} </a> <br>
							{/foreach} </div>
						{if $cnt >= $cat.per_column}
							</td>{counter assign=cnt start=1}
						{else}
							{counter assign=cnt}
						{/if}
					{/foreach} 
				</tr>
				</table>
			</td>
			</tr>
		</table>
	</div>	
	</div>
	{/foreach}
	{raMethod path=Fuga:Public:Catalog:hit}           
{else}
	<div class="cat-description">{$cat.description}</div>		
	{if $cat.parent_id == 0}			
		<table class="subcats-block" width="640">
			<tr>
			<td width="50%" class="cat-subcats{$cat.id}">
				<table width="100%" cellpadding="5">
				<tr>
					{counter assign=cnt start=1}
					{foreach from=$cats item=subcat}
					{if $cnt == 1}<td>{/if}
					<div class="cat-level2"><a href="{raURL node=catalog method=index prms=$subcat.id}"><b>{$subcat.title}</b></a></div>
					<div class="cat-level3"> 
						{foreach from=$subcat.children item=subcat2}
						&mdash; <a href="{raURL node=catalog method=index prms=$subcat2.id}">{$subcat2.title} </a> <br>
						{/foreach} </div>
					{if $cnt >= $per_column}
						</td>{counter assign=cnt start=1}
					{else}
					{counter assign=cnt}
					{/if}
					{/foreach} 
				</tr>
				</table>
			</td>
			</tr>
		</table>
		{raMethod path=Fuga:Public:Catalog:hit args="['id':`$cat.id`]"} 
	{else}
		<table class="subcats-block" width="640">
			<tr>
			<td class="cat-subcats{$cat_tree[0].id}">
				<table width="100%" cellpadding="5">
				<tr> 
					{counter assign=cnt start=1}
					{foreach from=$cats item=subcat}
					{if $cnt == 1}<td>{/if}
					<div class="cat-level2"><a href="{raURL node=catalog method=index prms=$subcat.id}">{$subcat.title}</a></div>
					{if $cnt >= $per_column}</td>
					{counter assign=cnt start=1}{else}{counter assign=cnt}{/if}
					{/foreach} 
				</tr>
				</table>
			</td>
			</tr>
		</table>
		{raMethod path=Fuga:Public:Catalog:hit args="['id':`$cat.id`]"}
	{/if}

	{if count($products)}
	<table class="product-selector" width="100%">
		<tr>
			<td width="40%">Сортировать по: 
				{if $sort != 'price' && $sort != 'name'} 
				<a href="{raURL node=catalog method=index prms="`$params[0]`.price"}">цене</a> 
				<a href="{raURL node=catalog method=index prms="`$params[0]`.name"}">названию</a> 
				{elseif $sort == 'price'} 
				<strong>цене</strong> 
				<a href="{raURL node=catalog method=index prms="`$params[0]`.name"}">названию</a> 
				{else} 
				<a href="{raURL node=catalog method=index prms="`$params[0]`.price"}">цене</a> 
				<strong>названию</strong> 
				{/if} 
			</td>
			<td width="60%" align="right">
				по 
				<select style="height: 26px;" class="span7" name="cpage" onChange="setCatalogRTT(this, {$rtt})">
					<option value="6"{if $rtt == 6} selected{/if}>6</option>
					<option value="12"{if $rtt > 6 && $rtt <= 12} selected{/if}>12</option>
					<option value="24"{if $rtt > 12 && $rtt <= 24} selected{/if}>24</option>
					<option value="48"{if $rtt > 24 && $rtt <= 48} selected{/if}>48</option>
					<option value="1000"{if $rtt > 48 || $rtt < 6} selected{/if}>Все</option>
				</select> на страницу 
			</td>
		</tr>
	</table>
	<table class="product-table">
		{foreach from=$products item=item name=product}
		{raItem var=cat0 table=catalog_category query=$item.category_id_root_id}
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
					{if $item.price_count == 0}
					<a class="btn btn-warning btn-large" href="javascript:addCartItem({$item.id})">Купить</a>
					<span class="plusminus">
						<a href="javascript:void(0);" class="btn" onclick="downQuantity({$item.id})">&minus;</a>
						<input class="input-mini" id="amount_{$item.id}" type="text" readonly="readonly" value="1">
						<a href="javascript:void(0);" class="btn" onclick="upQuantity({$item.id})">&plus;</a>
					</span>
					{else}
					<a class="btn btn-warning btn-large" href="{raURL node=catalog method=stuff prms=$item.id}">Купить</a>
					{/if}
					<input type="hidden" value="0" id="product_price_{$item.id}">                        
					<div class="product-exists">{if $item.is_exist}<img src="/img/vnalich.png">{else}<img src="/img/zakaz.png">{/if}</div>
				</div>
				<div class="clearfix"></div>
			</td>
		{if $smarty.foreach.product.iteration % 2 == 0}</tr><tr>{/if}
		{/foreach}
	</table>
	{$paginator->render()}
	{/if} 
{/if}