{if sizeof($goods)}
            <h1><a href="/cart/">���� �������</a> &rarr; <a href="/cart/?action=info">���������� � ���</a> &rarr; <a href="/cart/?action=delivery">������ � ��������</a> &rarr; ������������� &rarr; <span>���������� � ������</span></h1>
            <br>
            <!--<div class="cart-order-total">��� ����� �� ����� 
			{if is_array($discount)}<span>{$list_total-$list_total*$discount.discount/100|number_format:2:',':' '} ���.</span> (� ������ ������: <b>{$discount.discount}%</b>){else}
			<span>{$list_total|number_format:2:',':' '} ���.</span>{/if}
			</div> -->
<form name="frmCart" id="frmCart" action="./?action=order" method="post">
<input type="hidden" name="submited" value="1"> 
 <table class="cart-order-stuff" width="100%" cellpadding="0" cellspacing="0" border="0">
              {foreach from=$goods item=g}
			  <tr>
                <td><a target="_blank" href="{raURL node=$g.stuff.dir_id_name method=stuff prms=$g.stuff.id}">{$g.stuff.name}</a>
				{if $g.priceEntity.id} 
                <div class="stuff-sizes">������� ����������: {$g.priceEntity.size_id_name} - {$g.priceEntity.color_id_name}</div>
                {/if}
                </td>
                <td>{$g.price|number_format:2:',':' '} ���.</td>
                <td>{$g.counter} ��.</td>
                <td>{$g.price*$g.counter|number_format:2:',':' '} ���.</td>
              </tr>
			  {/foreach}
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
            </table>
			<div class="cart-total">����� {$smarty.session.number} ������(��) �� �����: <span>{$list_total|number_format:2:',':' '} ���.</span></div>
			{if is_array($discount)}<div class="cart-total">��� ����� ������ �� {$discount.sum_min|number_format:2:',':' '} �� {$discount.sum_max|number_format:2:',':' '} ���. ��������� ������ <b style="color:#EE2A2A;font-size:14px;">{$discount.discount} %</b></div>
			<div class="cart-total">����� � ������ ������: <span>{$list_total-$list_total*$discount.discount/100|number_format:2:',':' '} ���.</span></div>{/if}
			{if count($gifts)}<h3><span>�������:</span></h3>
			<table class="cart-order-stuff" width="100%" cellpadding="0" cellspacing="0" border="0">
              {foreach from=$gifts item=gift}
			  <tr>
                <td><a target="_blank" href="{raURL node=catalog method=stuff prms=$gift.gift_id}">{$gift.gift_id_name}</a></td>
              </tr>
			  {/foreach}
              <tr>
                <td>&nbsp;</td>
              </tr>
            </table>{/if}
            <div class="cart-contacts">
			<b>���:</b> {$uauth->user.name}<br>
			<b>�������:</b> {$uauth->user.phone}<br>
			<b>E-mail:</b> {$uauth->user.email}<br>
			<b>������ ������:</b> {$smarty.session.pay_type}<br>
            <b>��������:</b> {$smarty.session.delivery_type}<br>
            <b>����� ��������:</b> {$smarty.session.delivery_address}<br>
			<b>����������:</b>
			<textarea name="additions" style="width:100%;height:70px" ></textarea>
            </div>
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="50%">&nbsp;</td>
                <td width="50%" align="right"><a href=""><a href="#" onClick="document.frmCart.submit();return false;"><img src="/img/order_btn.gif"></a></td>
              </tr>
            </table>
</form>
{else}
<div class="lst-item2">
<h1>� ����� ������� ��� �������</h1>
{raInclude var=delivery}
</div>
{/if}