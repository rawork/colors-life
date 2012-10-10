{raItems var=cats table=catalog_category query="publish=1 AND parent_id=0"}
{foreach from=$cats item=cat}
<div class="selectors" id="cat_{$cat.id}">
{raItems var=subcats table=catalog_category query="publish=1 AND parent_id=`$cat.id`"}
{raCount var=count_subcats table=catalog_category query="publish=1 AND parent_id=`$cat.id`"}
{if count($subcats)}
<table width="640" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td class="celector-left-top-{$cat.id}"><img src="/img/0.gif" width="30" height="10" border="0"></td>
    <td class="celector-top-{$cat.id}"><img src="/img/0.gif" width="1" height="10" border="0"></td>
    <td class="celector-right-top-{$cat.id}"><img src="/img/0.gif" width="9" height="10" border="0"></td>
  </tr>
  <tr>
    <td class="celector-left-{$cat.id}"><img src="/img/0.gif" width="30" height="1" border="0"></td>
    <td class="celector-content-{$cat.id}"><table width="601" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td class="white-left-top"><img src="/img/0.gif" width="10" height="9" border="0"></td>
          <td class="white-top"><img src="/img/0.gif" width="1" height="9" border="0"></td>
          <td class="white-right-top"><img src="/img/0.gif" width="10" height="9" border="0"></td>
        </tr>
        <tr>
          <td class="white-right"><img src="/img/0.gif" width="10" height="1" border="0"></td>
          <td class="white-content" style="background-image: url('{$cat.image}')" valign="top"><table class="subcats-block" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td class="cat-subcats{$cat.id}"><table width="100%" cellpadding="5" cellspacing="0" border="0">
                    <tr> {math assign=maxPerColumn equation="ceil(x/y)" x=$count_subcats y=2}
                      {counter assign=cnt start=1}
                      {foreach from=$subcats item=subcat}
                      {if $cnt == 1}
                      <td>{/if}
                        <div class="cat-level2"><a href="{raURL node=catalog method=index prms=$subcat.id}"><b>{$subcat.title}</b></a></div>
                        <div class="cat-level3"> {raItems var=subcats2 table=catalog_category query="publish=1 AND parent_id=`$subcat.id`"}
                          {foreach from=$subcats2 item=subcat2}
                          &mdash; <a href="{raURL node=catalog method=index prms=$subcat2.id}">{$subcat2.title} </a> <br>
                          {/foreach} </div>
                        {if $cnt >= $maxPerColumn}</td>
                      {counter assign=cnt start=1}{else}{counter assign=cnt}{/if}
                      {/foreach} </tr>
                  </table></td>
              </tr>
            </table>
			<div class="cat-close-link"><a href="javascript:toggleCat({$cat.id})">Закрыть Х</a></div>
			</td>
          <td class="white-right"><img src="/img/0.gif" width="10" height="1" border="0"></td>
        </tr>
        <tr>
          <td class="white-left-bottom"><img src="/img/0.gif" width="10" height="9" border="0"></td>
          <td class="white-bottom"><img src="/img/0.gif" width="1" height="9" border="0"></td>
          <td class="white-right-bottom"><img src="/img/0.gif" width="10" height="9" border="0"></td>
        </tr>
      </table></td>
    <td class="celector-right-{$cat.id}"><img src="/img/0.gif" width="9" height="1" border="0"></td>
  </tr>
  <tr>
    <td class="celector-left-bottom-{$cat.id}"><img src="/img/0.gif" width="30" height="11" border="0"></td>
    <td class="celector-bottom-{$cat.id}"><img src="/img/0.gif" width="1" height="11" border="0"></td>
    <td class="celector-right-bottom-{$cat.id}"><img src="/img/0.gif" width="9" height="11" border="0"></td>
  </tr>
</table>
{/if}
</div>            
            {/foreach}
			