<div class="pager2"> ��������: 
{if $begin_link ne ''}<a href="{$begin_link}">���.</a>&nbsp;{/if}
{if $prevblock_link ne ''}<a href="{$prevblock_link}">...</a>&nbsp;{/if}	
{if $prev_link ne ''}<a href="{$prev_link}">&larr;&nbsp;����������</a>&nbsp;{/if}
{foreach from=$pages key=k item=i}
{if $page == $i.name}<span>{$i.name}</span>&nbsp;{else}<a href="{$i.ref}">{$i.name}</a>&nbsp;{/if}
{/foreach}
{if $next_link ne ''}<a href="{$next_link}">���������&nbsp;&rarr;</a>&nbsp;{/if}
{if $nextblock_link ne ''}<a href="{$nextblock_link}">...</a>&nbsp;{/if}
{if $end_link ne ''}<a href="{$end_link}">���.</a>&nbsp;{/if}
</div>