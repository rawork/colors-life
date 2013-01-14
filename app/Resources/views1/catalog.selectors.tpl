{raItems var=cats table=catalog_category query="publish=1 AND parent_id=0"}
{foreach from=$cats item=cat}
{raItems var=subcats table=catalog_category query="publish=1 AND parent_id=`$cat.id`"}
{raCount var=count_subcats table=catalog_category query="publish=1 AND parent_id=`$cat.id`"}	
<div class="triangle triangle{$cat.id} cat-item{$cat.id}" id="triangle_{$cat.id}">&#9668;</div>	
<div class="selectors width-640" id="cat_{$cat.id}">
	<div class="index-category-back cat{$cat.id}" id="index_cat_{$cat.id}">
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
					<div class="cat-level2"><a href="{raURL node=catalog method=index prms=$subcat.id}"><b>{$subcat.title}</b></a></div>
					<div class="cat-level3"> {raItems var=subcats2 table=catalog_category query="publish=1 AND parent_id=`$subcat.id`"}
						{foreach from=$subcats2 item=subcat2}
						&mdash; <a href="{raURL node=catalog method=index prms=$subcat2.id}">{$subcat2.title} </a> <br>
						{/foreach} </div>
					{if $cnt >= $maxPerColumn}</td>
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
			