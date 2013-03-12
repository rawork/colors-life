{if $user}
<b>{$user.name} {$user.lastname}</b> / <a href="{raURL node=cabinet}">Личный кабинет</a> / <a href="{raURL node=cabinet method=logout}">Выйти</a>
{else}
<a href="{raURL node=cabinet}">Вход в личный кабинет</a> / <a href="{raURL node=cabinet method=registration}">Регистрация</a>
{/if}