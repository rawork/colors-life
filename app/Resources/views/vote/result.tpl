{if $error}<div style="color:red">{$error}</div>{/if}
<div class="vote-title"><b>{$vote.title}</b></div>
{if $diagram}<img src="{$diagram->fname}" width="{$diagram->width}" height="{$diagram->height}">{/if}
{foreach from=$answers item=answer}
<div class="vote-item">{$answer.name} - {$answer.quantity} ({$answer.percent}%)</div>
<div style="height:16px;background-color:{$answer.color};width:{$answer.percent+1}%"></div>
{/foreach}
<div class="vote-total">Всего голосов: {$vote.quantity}</div>