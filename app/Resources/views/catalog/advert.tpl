{if count($items)}
<div class="advert">
	{counter start=0 print=false}
    {foreach from=$items item=item}
	{counter assign=key}	
	<div class="item{if $key > 1} closed{/if}" id="adv_text_{$key}">
	{if $item.file}
	<div id="flashcontent{$key}"></div>
	<script type="text/javascript">
		// <![CDATA[

		var so = new SWFObject("{$item.file}", "advert{$key}", "640", "163", "8", "#FFFFFF");
		so.write("flashcontent{$key}");

		// ]]>
	</script>
	{else}
	<a title="{$item.name}" href="{if $item.link}{$item.link}{else}{raURL node=catalog method=promotion prms=$item.id}{/if}"><img alt="{$item.name}" src="{$item.image}"></a>
	{/if}
	</div>
    {/foreach}
</div>
<div class="advert-buttons">
	{counter start=0 print=false}
	{foreach from=$items item=item}
	{counter assign=key}	
	<div class="adv-btn{if $key == 1}-active{/if}" id="adv_tab_{$key}" onclick="showAdvert({$key});">{$key}</div>
	{/foreach}
</div>
<div class="clearfix"></div>
<script type="text/javascript"> max_advert = {$key}; showAdvertTime(1)</script>
{/if}
