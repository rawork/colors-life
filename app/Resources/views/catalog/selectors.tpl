{foreach from=$cats item=cat}
<div class="triangle triangle{$cat.id} cat-item{$cat.id}" id="triangle_{$cat.id}">&#9668;</div>	
<div class="selectors width-640" id="cat_{$cat.id}">
	<div class="index-category-back cat{$cat.id}" id="index_cat_{$cat.id}">
	<div class="index-category" style="background-image: url('{$cat.image}')">
		<h4 class="cat-item{$cat.id}"><span>{$cat.title}</span></h4>
		<table class="subcats-block" width="100%">
			<tr>
			<td class="cat-subcats{$cat.id}">
				<table width="100%" cellpadding="5">
				<tr> 
					{counter assign=cnt start=1}
					{foreach from=$cat.children item=subcat}
					{if $cnt == 1}<td>{/if}
					<div class="cat-level2"><a href="{raURL node=catalog method=index prms=$subcat.id}"><b>{$subcat.title}</b></a></div>
					<div class="cat-level3"> 
						{foreach from=$subcat.children item=subcat2}
						&mdash; <a href="{raURL node=catalog method=index prms=$subcat2.id}">{$subcat2.title} </a> <br>
						{/foreach} </div>
					{if $cnt >= $cat.per_column}</td>
					{counter assign=cnt start=1}{else}{counter assign=cnt}{/if}
					{/foreach} 
				</tr>
				</table>
				<div class="cat-close-link"><a href="javascript:toggleCat({$cat.id})">Закрыть Х</a></div>
			</td>
			</tr>
		</table>
	</div>	
	</div>
</div>            
{/foreach}
			