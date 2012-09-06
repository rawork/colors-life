{if $q}
<div id="{$q.name}">
<form id="frm{$q.name}" action="" method="post">
<input type="hidden" name="vote_question" value="{$q.id}" />
<div class="vote-title"><b>{$q.title}</b></div><br>	
{foreach from=$a key=k item=v}
<div class="vote-item">
<input type="radio" name="vote" value="{$v.id}"> {$v.name}</div>
{/foreach}
<div class="vote-button"><input type="button" onclick="xajax_vote('{$q.name}', xajax.getFormValues('frm{$q.name}'))" value="Голосовать" /></div>
<div class="vote-button"><input type="button" onclick="xajax_voteResult('{$q.name}', {$q.id})" value="Результаты" /></div>
</form></div>
{/if}