{if $frmMessage[1]}<div class="tree-{$frmMessage[0]}">{$frmMessage[1]}</div>{/if}
<form name="frm{$dbform.name}" id="frm{$dbform.name}" action="{$action}" method="post" onsubmit="return checkForm(this)" enctype="multipart/form-data">
  <input type="hidden" name="submited" value="1">
  {foreach from=$items key=myId item=i}
  {if $i.type eq 'hidden'}<input type="hidden" name="{$i.name}" value="{$i.value}">{/if}
  {/foreach}  
  <table width="250" class="frmContainer" border="0" cellspacing="0" cellpadding="3">
    {foreach from=$items key=myId item=i}
    {if $i.type eq 'file'}
    <tr id="tr_{$i.name}">
      <td><div>{$i.title}{if $i.not_empty}&nbsp;<span class="required">*</span>{/if}</div>
        <input style="width:100%" type="file" class="fls" title="{if $i.not_empty}{$i.title}{/if}" name="{$i.name}" /></td>
    </tr>
    {elseif $i.type eq 'password'}
    <tr id="tr_{$i.name}">
      <td><div>{$i.title}{if $i.not_empty}&nbsp;<span class="required">*</span>{/if}</div>
        <input style="width:100%" type="{$i.type}" class="txt" title="{if $i.not_empty}{$i.title}{/if}" name="{$i.name}" value="" /></td>
    </tr>
    {if $i.is_check}
    <tr id="tr_{$i.name}{$pass_postfix}">
      <td><div>{$i.title} еще раз{if $i.not_empty}&nbsp;<span class="required">*</span>{/if}</div>
        <input style="width:100%" type="{$i.type}" class="txt" title="{if $i.not_empty}{$i.title} еще раз{/if}" name="{$i.name}{$pass_postfix}" value="" /></td>
    </tr>{/if}
    {elseif $i.type eq 'string'}
    <tr id="tr_{$i.name}">
      <td><div>{$i.title}{if $i.not_empty}&nbsp;<span class="required">*</span>{/if}</div>
        <input style="width:100%" type="{$i.type}" class="txt" title="{if $i.not_empty}{$i.title}{/if}" name="{$i.name}" value="{$i.value}" /></td>
    </tr>
    {elseif $i.type eq 'checkbox'}
    <tr id="tr_{$i.name}">
      <td><div>{$i.title}</div>
        <input style="width:100%" type="{$i.type}" class="chk" title="{if $i.not_empty}{$i.title}{/if}" name="{$i.name}" {if $i.value ne ''}checked{/if} /></td>
    </tr>
    {elseif $i.type eq 'text'}
    <tr id="tr_{$i.name}">
      <td><div>{$i.title}{if $i.not_empty}&nbsp;<span class="required">*</span>{/if}</div>
        <textarea style="width:100%" rows="5" title="{if $i.not_empty}{$i.title}{/if}" name="{$i.name}" />{$i.value}</textarea></td>
    </tr>
    {elseif $i.type eq 'select'}
    <tr id="tr_{$i.name}">
      <td><div>{$i.title}{if $i.not_empty eq true}&nbsp;<span class="required">*</span>{/if}</div>
        <select style="width:100%" title="{if $i.not_empty}{$i.title}{/if}" name="{$i.name}"{$i.more}> 
        <option value="0">...</option>
        {foreach from=$i.select_values key=opId item=op}
        <option value="{$op.value}"{$op.sel}>{$op.name}</option>
        {/foreach}
        </select></td>
    </tr>
	{elseif $i.type eq 'enum'}
    <tr id="tr_{$i.name}">
      <td><div>{$i.title}{if $i.not_empty eq true}&nbsp;<span class="required">*</span>{/if}</div>
        <select style="width:100%" title="{if $i.not_empty}{$i.title}{/if}" name="{$i.name}"{$i.more}> 
        <option value="0">...</option>
        {foreach from=$i.select_values key=opId item=op}
        <option value="{$op.value}"{$op.sel}>{$op.name}</option>
        {/foreach}
        </select></td>
    </tr>
    {/if}
    {/foreach}
    {if $dbform.is_defense}
    <tr>
      <td><div>Введите символы на картинке внизу  <span class="required">*</span></div>
        <p><input type="text" title="Код безопасности" style="width:120px" name="securecode"></p>
        <p><img id="secure_image" src="/secureimage/?{$smarty.session.name}={$smarty.session.id}"> <a href="#" onclick="document.getElementById('secure_image').src='/secureimage/?rnd='+Math.random()+'&{$smarty.session.name}={$smarty.session.id}';return false">обновить код</a></p>
      </td>
    </tr>
    {/if}
    {if $dbform.needed}<tr>
      <td><strong><span class="required">*</span></strong> &#8212; обязательные поля</td>
    </tr>{/if}
    <tr>
      <td><input type="submit" class="btn" value="{$dbform.submit_text}" /></td>
    </tr>
  </table>
</form>
