<div class="cabinet-menu">
{if $methodName == 'index'}
<span>Личные данные</span>
{else}
<span><a href="{raURL node=cabinet}">Личные данные</a></span>
{/if}
{if $methodName == 'password'}
<span>Изменить пароль</span>
{else}
<span><a href="{raURL node=cabinet method=password}">Изменить пароль</a></span>
{/if}
{if $methodName == 'orders'}
<span>Текущие заказы</span>
{else}
<span><a href="{raURL node=cabinet method=orders}">Текущие заказы</a></span>
{/if}
<span><a href="{raURL node=cabinet method=logout}">Выйти</a></span>
</div>
<br><br>