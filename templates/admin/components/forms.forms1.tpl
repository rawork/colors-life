<div class="tabs">
  <ul>
    <li id="general_tab" class="current"><span><a href="javascript:mcTabs.displayTab('general_tab','general_panel');" onmousedown="return false;">Форма</a></span></li>
    {if $smarty.get.action == 's_update'}<li class="" id="fields_tab"><span><a href="javascript:mcTabs.displayTab('fields_tab','fields_panel');" onmousedown="return false;">Поля</a></span></li>{/if}
  </ul>
</div>
<div class="panel_wrapper">
  <div id="general_panel" class="current">
    <form enctype="text/plain" method="post" name="frmUpdateFor" id="frmUpdateForm" action="{$fullref}&action=update">
      <input type="hidden" name="id" value="{$a.id}">
      <table width="100%" cellspacing="0" class="tprops">
        <tr>
          <td><h4>Веб-форма</h4>
            <table style="width:350px;margin-left:15px" border="0" cellpadding="2" cellspacing="0">
              <tr>
                <td>Название:<br>
                  <input type="text" name="title" class="field-props" value="{$a.title}" /></td>
              </tr>
              <tr>
                <td>Системное имя:<br>
                  <input type="text" name="name" class="field-props" value="{$a.name}" /></td>
              </tr>
              <tr>
                <td>E-mail:<br>
                  <input type="text" name="email" class="field-props" value="{$a.email}" /></td>
              </tr>
              <tr>
                <td>Кнопка submit:<br>
                  <input type="text" name="submit_text" class="field-props" value="{$a.submit_text}" /></td>
              </tr>
              <tr>
                <td><table width="100%" border="0" cellpadding="2" cellspacing="0">
                    <tr>
                      <td><input type="checkbox" name="is_defense" {if $a.is_defense}checked{/if} value="on" />
                        антиспам</td>
                    </tr>
                  </table></td>
              </tr>
            </table></td>
        </tr>
      </table>
      <div class="ctlbtns">
        <input type="button" class="adm-btn" onClick="xajax_updateForm(xajax.getFormValues('frmUpdateForm'));" value="Сохранить">
      </div>
    </form>
  </div>
  <div id="fields_panel" class="panel">
    <form enctype="text/plain" method="post" name="frmFormFields" id="frmFormFields">
    <input type="hidden" name="form_id" value="{$a.id}">
      <table width="100%" cellspacing="0" class="tprops">
        <tr>
          <td><h4>Поля</h4>
            <table class="tfields" align="center" width="720" border="0" cellpadding="2" cellspacing="0">
              <tr>
                <th width="2%">ID</th>
                <th width="30%">Название</th>
                <th width="30%">Системное имя</th>
                <th width="30%">Тип</th>
                <th width="8%">Сорт.</th>
                <th width="8%">Изм.</th>
              </tr>
              {raItems var=fields table=forms_fields query="form_id=`$a.id`"}
              {foreach from=$fields item=f}
              {if $f.id}
              <tr>
                <td>{$f.id}&nbsp;</td>
                <td><input type="text" name="title_{$f.id}" value="{$f.title}" /></td>
                <td><input type="text" name="name_{$f.id}" value="{$f.name}" /></td>
                <td><select name="type_{$f.id}">
		          	{foreach from=$types item=t}	
                    <option value="$t[1]" {if $t[1] eq $f.type}selected{/if}>{$t[0]}</option>
                    {/foreach}
                  </select></td>
                <td><input type="text" name="ord_{$f.id}" value="{$f.ord}" /></td>
                <td align="center"><a href="#" onclick="xajax_editField({$f.id});return false;"><img src="{$theme_ref}/img/icons/icon_edit.gif" border="0" /></a></td>
              </tr>
              {/if}{/foreach}
            </table></td>
        </tr>
      </table>
      <div class="ctlbtns">
        <input type="button" class="adm-btn" onClick="xajax_updateFormFields({$a.id}, xajax.getFormValues('frmFormFields'));" value="Сохранить">
      </div>
    </form>
  </div>
</div>