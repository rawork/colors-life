<br>
{$paginator->render()}
<form id="frmGroupUpdate" name="frmGroupUpdate" action="{$baseRef}/groupedit" method="post">
<table class="table table-condensed table-normal">
<thead>
<tr>
<th width="1%"><input type="checkbox" id="list-checker"></th>
<th width="1%">#</th>
{foreach from=$fields item=field}
	{if $field.width}
	<th width="{$field.width}">{$field.title}</th>
	{/if}
{/foreach}
{if $showCredate}
<th width="10%">Дата создания</th>
{/if}
<th style="text-align:center;"><img alt="Действия" src="{$theme_ref}/img/action_head.gif" border=0></th>
</tr>
</thead>
{$tableData}
</table>
<div class="form-inline" id="control">
	{if $showGroupSubmit}
		<a class="btn btn-small" title="Сохранить" onclick="startGroupUpdate(false)"><i class="icon-film"></i></a>
	{/if}
	<a class="btn btn-small" title="Изменить" onclick="startGroupUpdate(true)"><i class="icon-pencil"></i></a>
	<a class="btn btn-small btn-danger" title="Удалить" onclick="startGroupDelete()"><i class="icon-trash icon-white"></i></a>
	{if $rpps}
	<label>&nbsp;&nbsp;На странице:&nbsp;&nbsp; <select class="input-mini" name="rpp" onChange="updateRpp(this, '{$tableName}')">';
	{foreach from=$rpps item=rpp}
	<option value="{$rpp}" {if $rowPerPage == $rpp} selected{/if}>{$rpp}</option>
	{/foreach}
	</select></label>
	{/if}
</div>
<input type="hidden" name="ids" id="ids" value="{$ids}"></form>
{$paginator->render()}