<p>Результаты поиска по запросу &laquo;<b>{$smarty.get.text}</b>&raquo;:</p>
{if $ptext}{$ptext}<br>{/if}
{foreach from=$items key=k item=it}<p>{$it.num}. <a href='{$it.ref}'>{$it.text}&nbsp;  &#8594;</a></p><br>
{/foreach}
{if $ptext}{$ptext}{/if}