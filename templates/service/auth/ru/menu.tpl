<div class="cabinet-menu">
{if $urlprops.method == 'index'}
<span>Личные данные</span>
{else}
<span><a href="/cabinet/">Личные данные</a></span>
{/if}
{if $urlprops.method == 'password'}
<span>Изменить пароль</span>
{else}
<span><a href="/cabinet/password.htm">Изменить пароль</a></span>
{/if}
{if $urlprops.method == 'orders'}
<span>Текущие заказы</span>
{else}
<span><a href="/cabinet/orders.htm">Текущие заказы</a></span>
{/if}
<!--
{if $urlprops.method == 'orders-history'}
<span>История заказов</span>
{else}
<span><a href="/cabinet/orders-history.htm">История заказов</a></span>
{/if}
-->
<span><a href="/cabinet/logout.htm">Выйти</a></span>
</div>
<br><br>