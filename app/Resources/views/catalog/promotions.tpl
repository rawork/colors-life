{foreach from=$items item=item}
<div class="article-block"> 
<div class="article-title"><a href="{raURL node=catalog method=promotion prms=$item.id}">{$item.name}</a></div>
<div class="promotion-dates"> C {$item.datefrom|fdate:'d F Y'} по {$item.datetill|fdate:'d F Y'}</div>
<br>
{if $item.file}
<div id="flashcontent{$item.id}"></div>
<script type="text/javascript">
	// <![CDATA[
				
	var so = new SWFObject("{$item.file}", "advert{$item.id}", "640", "163", "8", "#FFFFFF");
	//so.addVariable("flashVarText", "this is passed in via FlashVars for example only"); // this line is optional, but this example uses the variable and displays this text inside the flash movie
	so.write("flashcontent{$item.id}");
		
	// ]]>
</script>
{elseif $item.image}
<div><img src="{$item.image}"></div>
{/if}
</div>
{/foreach}
