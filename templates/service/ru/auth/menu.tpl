<div class="cabinet-menu">
{if $smarty.get.action == 'orders'}
<span><a href="/cabinet/">Настройки аккаунта</a></span>
<span>Заказы</span>
{else}
<span>Настройки аккаунта</span>
<span><a href="/cabinet/?action=orders">Заказы</a></span>
{/if}
<span><a href="/cabinet/?action=logout">Выйти</a></span>
</div>
<br><br>