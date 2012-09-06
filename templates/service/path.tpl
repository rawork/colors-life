<div class="mainpath">
{foreach from=$pathitems key=k item=p}
{if sizeof($p)}{if $k lt sizeof($pathitems)-1 || $urlprops.method != 'index'}<a href="{$p.ref}">{$p.title}</a>{if $k lt sizeof($pathitems)-1}&nbsp;{$delimeter}{/if} {else} &nbsp;<span>{$p.title}</span>&nbsp;{/if}{/if}
{/foreach}
</div>