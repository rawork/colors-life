          <h1><a href="/cart/">���� �������</a> &rarr; <a href="/cart/?action=info">���������� � ���</a> &rarr; ������ � �������� &rarr; <span>�������������</span> &rarr; <span>���������� � ������</span></h1>
          <br>
          
          <div class="reg-form">
          <form name="frmCart" action="/cart/?action=delivery" method="post">
		  <input type="hidden" name="submited" value="1">
          ������ ������ <br>
          <select name="pay_type" class="field-t3">
          {foreach from=$pays item=pay}
          <option value="{$pay.name}"{if $smarty.session.pay_type == $pay.name} selected{/if}>{$pay.name}</option>
		  {/foreach}
          </select>
          <br>
          ������ �������� <br>
          <select name="delivery_type" class="field-t3">
          {foreach from=$delivery item=deliv}
          <option value="{$deliv.name}"{if $smarty.session.delivery_type == $deliv.name} selected{/if}>{$deliv.name}</option>
		  {/foreach}
          </select><br>
		  ����� �������� <br>
		  <textarea style="width:350px;height: 70px;" name="delivery_address">{$smarty.session.delivery_address}</textarea> 
          </form>
          </div>
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
            <td width="50%">&nbsp;</td>
            <td width="50%" align="right"><a href=""><a href="#" onclick="document.frmCart.submit(); return false;"><img src="/img/next_btn.gif"></a></td>
            </tr>
            </table>
