{raItems var=items table=articles_tags sort=name limit=100}            
            <div class="tag-cloud">
	<ul>
	{foreach from=$items item=item}
	<li><a class="w{$item.position}" rel="tag" href="{raURL node=articles method=index}?tag={$item.id}">{$item.name}</a></li>
	{/foreach}
	</ul>
	<br><a href="{raURL node=articles method=tags}">все метки</a>
    </div>