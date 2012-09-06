<div class="menu-catalog-title"><a href="javascript:toggleBlock('cat_block')">Каталог</a></div>
<div class="catsmenu-block" id="cat_block"{if $urlprops.node.name == 'articles'} style="display:none;"{/if}>
{raItems var=items table=catalog_categories query="publish='on' AND p_id=0"}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
{foreach from=$items item=item}
  
  <tr>
    <td class="cat-logo"><img src="{$item.logo}"></td>
    <td class="cat-link"><a href="javascript:toggleCat({$item.id})">{$item.name}</a>
      {raItems var=subitems table=catalog_categories query="publish='on' AND p_id=`$item.id`"}
      {if count($subitems)>1000}
      <div class="catmenu-sub" id="cat_{$item.id}">
      {foreach from=$subitems item=subitem}
        <li><a href="{raURL node=catalog method=index prms=$subitem.id}">{$subitem.name}</a></li>
      {/foreach}  
      </div>{/if}</td>
  </tr>
{/foreach}  
</table>
</div>
