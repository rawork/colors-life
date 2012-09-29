{if count($links)}
<div class="btn-group">	
{foreach from=$links item=link}
<button class="btn"><a href="{$link.ref}">{$link.name}</a></button>
{/foreach}
</div>
{/if}
