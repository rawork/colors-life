{if sizeof($tables)}<table border="0" cellpadding="2" cellspacing="0" width="100%">
  {foreach item=t from=$tables}<tr>
    <td  width="10%" valign="top"><img src="{$theme_ref}/img/icons/icon_table.gif"></td>
    <td  width="90%" valign="top" nowrap><a class="tbl" href="{$t.ref}">{$t.name}</a><br>
	</td>
  </tr>{/foreach}
</table>{/if}