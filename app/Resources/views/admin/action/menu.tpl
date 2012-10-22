{if count($links)}
<div class="btn-group">	
{foreach from=$links item=link}
<a class="btn" href="{$link.ref}">{$link.name}</a>
{/foreach}
</div>
{/if}
