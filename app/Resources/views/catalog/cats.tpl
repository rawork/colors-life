<h4>Каталог</h4>
<div class="cat-menu">
	{foreach from=$items item=item}
	<div class="cat-link">
		<span class="pull-left"><img src="{$item.logo}"></span>
		<a class="cat-item{$item.id}" href="javascript:toggleCat({$item.id})">{$item.title}</a>
	</div>
	{/foreach}
</div>
