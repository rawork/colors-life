<div class="cabinet-menu">
{if $urlprops.method == 'index'}
<span>������ ������</span>
{else}
<span><a href="/cabinet/">������ ������</a></span>
{/if}
{if $urlprops.method == 'password'}
<span>�������� ������</span>
{else}
<span><a href="/cabinet/password.htm">�������� ������</a></span>
{/if}
{if $urlprops.method == 'orders'}
<span>������� ������</span>
{else}
<span><a href="/cabinet/orders.htm">������� ������</a></span>
{/if}
<!--
{if $urlprops.method == 'orders-history'}
<span>������� �������</span>
{else}
<span><a href="/cabinet/orders-history.htm">������� �������</a></span>
{/if}
-->
<span><a href="/cabinet/logout.htm">�����</a></span>
</div>
<br><br>