{raItem var=item table=catalog_commercial query=$param0}
{if $item}
<h1>{$item.name}</h1>
<div class="promotion-dates"> C {$item.datefrom|fdate:'d F Y'} по {$item.datetill|fdate:'d F Y'}</div>
{if $item.file}
<br>
<div id="flashcontent{$item.id}"></div>
<br>
<script type="text/javascript">
	// <![CDATA[
				
	var so = new SWFObject("{$item.file}", "advert{$item.id}", "640", "163", "8", "#FFFFFF");
	//so.addVariable("flashVarText", "this is passed in via FlashVars for example only"); // this line is optional, but this example uses the variable and displays this text inside the flash movie
	so.write("flashcontent{$item.id}");
		
	// ]]>
</script>
{elseif $item.image}
<br>
<div><img src="{$item.image}"></div>
<br>
{/if}
<div class="article-text">{$item.body}</div>
{else}
Статья не найдена
{/if}