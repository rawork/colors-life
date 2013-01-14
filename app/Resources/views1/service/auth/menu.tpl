<div class="cabinet-menu">
{if $methodName == 'index'}
<span>Личные данные</span>
{else}
<span><a href="/cabinet/">Личные данные</a></span>
{/if}
{if $methodName == 'password'}
<span>Изменить пароль</span>
{else}
<span><a href="/cabinet/password.htm">Изменить пароль</a></span>
{/if}
{if $methodName == 'orders'}
<span>Текущие заказы</span>
{else}
<span><a href="/cabinet/orders.htm">Текущие заказы</a></span>
{/if}
<span><a href="/cabinet/logout.htm">Выйти</a></span>
</div>
<br><br>