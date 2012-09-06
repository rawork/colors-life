{raItems var=items table=catalog_offers query="publish='on'" limit=5}
{if count($items)}
<table cellpadding="0" cellspacing="0" border="0">
  <tr>
    {foreach from=$items key=k item=item}
    <td height="100%" id="adv_text_{$k+1}" {if $k > 0}style="display:none;"{/if} class="adv-image">
		<div>
		{if $item.file}
		<div id="flashcontent{$item.id}"></div>
		<script type="text/javascript">
			// <![CDATA[
				
			var so = new SWFObject("{$item.file}", "advert{$item.id}", "640", "163", "8", "#FFFFFF");
			//so.addVariable("flashVarText", "this is passed in via FlashVars for example only"); // this line is optional, but this example uses the variable and displays this text inside the flash movie
			so.write("flashcontent{$item.id}");
				
			// ]]>
		</script>
		{else}
		<a title="{$item.name}" href="{if $item.link}{$item.link}{else}{raURL node=catalog method=promotion prms=$item.id}{/if}"><img alt="{$item.name}" src="{$item.image}"></a>
		{/if}
		</div>
	</td>
    {/foreach}
    <td valign="top"><table class="adv-selectors" cellpadding="0" cellspacing="0" border="0">
        {counter start=0 print=false}
        {foreach from=$items key=k item=item}
        {if $k == 0}
        <tr>
          <td class="adv-btn-active" id="adv_tab_{$k+1}" onclick="showAdvertClick({$k+1});">{counter}</td>
        </tr>
        {else}
        <tr>
          <td class="adv-btn" id="adv_tab_{$k+1}" onclick="showAdvertClick({$k+1});">{counter}</td>
        </tr>
        {/if}
        {/foreach}
      </table></td>
     <td><img src="/img/0.gif" width="1" height="163" /></td> 
  </tr>
</table>
<script type="text/javascript"> max_advert = {$k+1}; showAdvertTime(1)</script>
{/if}
