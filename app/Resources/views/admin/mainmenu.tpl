<table class="leftmenu" border="0" cellpadding="2" cellspacing="0" width="95%">
  {foreach from=$modules item=mod}
  <tr>
    <td valign="top"><a href="javascript:getTableList('{$state}', '{$mod.name}');"><img border="0" src="{$theme_ref}/img/icons/icon_folder_{$state}.gif"></a></td>
    <td width="99%" valign="top" nowrap><a href="javascript:getTableList('{$state}', '{$mod.name}');">{$mod.title}</a><br>
	<div class="submenu{if $module ne $mod.name} closed{/if}" id="tableMenu_{$mod.name}">{$mod.tablelist}</div></td>
  </tr>
  {/foreach}
</table>
