<div class="cabinet-menu">
{if $smarty.get.action == 'orders'}
<span><a href="/cabinet/">��������� ��������</a></span>
<span>������</span>
{else}
<span>��������� ��������</span>
<span><a href="/cabinet/?action=orders">������</a></span>
{/if}
<span><a href="/cabinet/?action=logout">�����</a></span>
</div>
<br><br>