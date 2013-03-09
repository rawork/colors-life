{if $vote}
<div id="{$vote.name}">
<form id="frm{$vote.name}" action="" method="post">
<input type="hidden" name="vote" value="{$vote.id}" />
<div class="vote-title"><b>{$vote.title}</b></div><br>	
{foreach from=$answers key=k item=answer}
<div class="vote-item">
<input type="radio" name="answer" value="{$answer.id}"> {$answer.name}</div>
{/foreach}
<div class="vote-button"><input type="button" onclick="voteProcess('{$vote.name}', 1)" value="Голосовать" /></div>
<div class="vote-button"><input type="button" onclick="voteProcess('{$vote.name}')" value="Результаты" /></div>
</form></div>
{/if}