<table class="leftmenu" border="0" cellpadding="2" cellspacing="0" width="95%">
  {foreach from=$units item=u}
  <tr>
    <td valign="top"><a href="javascript:getTableList('{$state}', '{$u.name}');"><img border="0" src="{$theme_ref}/img/icons/icon_folder_{$state}.gif"></a></td>
    <td width="99%" valign="top" nowrap><a href="javascript:getTableList('{$state}', '{$u.name}');">{$u.title}</a><br>
	<div class="submenu{if $unit ne $u.name} closed{/if}" id="tableMenu_{$u.name}">{$u.tablelist}</div></td>
  </tr>
  {/foreach}
</table>
