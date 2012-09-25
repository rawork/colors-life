{$message}
<div class="vote-title"><b>{$q.title}</b></div>
{$vote_dia}
{foreach from=$a key=k item=v}
<div class="vote-item">{$v.name} - {$v.quantity} ({$v.percent}%)</div>
<div style="height:16px;background-color:{$v.color};width:{$v.percent+1}%"></div>
{/foreach}
<!--<div class="vote-total">Всего голосов: {$q.quantity}</div> -->