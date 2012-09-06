{raItems var=items table=catalog_partners query="publish='on'"}
<noindex>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td><img src="/img/partner_lt.gif"></td>
    <td class="partners-top"><img src="/img/partner_t.gif"></td>
    <td><img src="/img/partner_rt.gif"></td>
  </tr>
  <tr>
    <td class="partners-left"><img src="/img/partner_l.gif"></td>
    <td class="partners-content" style="width"100%;"><table style="width"100%;height=100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td class="partners-title">Наши партнеры</td>
          <td style="width:100%"><table style="width:100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td><img src="/img/partner2_lt.gif"></td>
                <td style="background-color: #fff;"></td>    
                <td><img src="/img/partner2_rt.gif"></td>
			  </tr>  
			  <tr>
                <td style="height:100%;background-color:#fff;"><img src="/img/0.gif" width="7"></td>  
                <td style="width:100%" class="partner2-content"><table class="partners-table" cellpadding="0" cellspacing="0" border="0">
                    {counter assign=cnt start=1}
                    {foreach from=$items item=item}
                    {if $cnt==1}<tr>{/if}
                      <td width="20%"><a target="_blank" href="{$item.link}"><img src="{$item.logo}" alt="{$item.name}"></a></td>
                    {if $cnt<5}{counter assign=cnt}{else}</tr>{counter assign=cnt start=1}{/if}
                    {/foreach}
                  </table></td>
				  <td style="height:100%;background-color:#fff;"><img src="/img/0.gif" width="6"></td>
			  </tr>
              <tr>			  
				<td><img src="/img/partner2_lb.gif"></td>
                <td style="background-color: #fff;"></td>
                <td><img src="/img/partner2_rb.gif"></td>
              </tr>
            </table></td>
        </tr>
      </table></td>
    <td class="partners-right"><img src="/img/partner_r.gif"></td>
  </tr>
  <tr>
    <td><img src="/img/partner_lb.gif"></td>
    <td class="partners-bottom"><img src="/img/partner_b.gif"></td>
    <td><img src="/img/partner_rb.gif"></td>
  </tr>
</table>
</noindex>