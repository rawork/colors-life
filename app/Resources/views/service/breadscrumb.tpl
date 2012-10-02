<div class="mainpath">
{foreach from=$nodes key=k item=p}
{if sizeof($p)}{if $k lt sizeof($nodes)-1 || $methodName != 'index'}<a href="{$p.ref}">{$p.title}</a>{if $k lt sizeof($nodes)-1}&nbsp;{$delimeter}{/if} {else} &nbsp;<span>{$p.title}</span>&nbsp;{/if}{/if}
{/foreach}
</div>