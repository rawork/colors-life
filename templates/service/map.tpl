<div class="map{$block}_links">
{foreach key=k item=it from=$items}
<a href="{$it.ref}">{$it.title}</a>
{$it.sub}
{/foreach}
</div>