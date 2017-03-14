{if count($links)}
<div class="btn-group">	
{foreach from=$links item=link}
<a class="btn{if $link.style} {$link.style}{/if}" href="{$link.ref}">{$link.name}</a>
{/foreach}
</div>
{/if}
