{raItems var=items table=catalog_category query="publish=1 AND parent_id=0"}
<h4>Каталог</h4>
<div class="cat-menu">
{foreach from=$items item=item}
	<div class="cat-link">
    <div class="pull-left"><img src="{$item.logo}"></div>
    <a class="cat-item{$item.id}" href="javascript:toggleCat({$item.id})">{$item.title}</a>
	</div>
	<div class="clearfix"></div>
{/foreach}  
</div>
