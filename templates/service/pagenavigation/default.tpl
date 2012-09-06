<div class="nums">
Записи {$recs} из {$rec_count}	
<div class="numbers" id="pager">
{if $begin_link ne ''}<a href="{$begin_link}">Нач.</a>{/if}
{if $prevblock_link ne ''}<a href="{$prevblock_link}">&laquo;</a>{/if}
{if $prev_link ne ''}<a href="{$prev_link}">&lt;</a>{/if}
{foreach from=$pages key=k item=i}
{if $page == $i.name}<span class="active">{$i.name}</span>{else}<a href="{$i.ref}">{$i.name}</a>
{/if}
{/foreach}
{if $next_link ne ''}<a href="{$next_link}">&gt;</a>{/if}
{if $nextblock_link ne ''}<a href="{$nextblock_link}">&raquo;</a>{/if}
{if $end_link ne ''}<a href="{$end_link}">Кон.</a>{/if}
</div></div>