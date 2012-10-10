<div class="menu-catalog-title"><a href="javascript:toggleBlock('cat_block')">Каталог</a></div>
<div class="catsmenu-block" id="cat_block"{if $node.name == 'articles'} style="display:none;"{/if}>
{raItems var=items table=catalog_category query="publish=1 AND parent_id=0"}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
{foreach from=$items item=item}
  
  <tr>
    <td class="cat-logo"><img src="{$item.logo}"></td>
    <td class="cat-link"><a href="javascript:toggleCat({$item.id})">{$item.title}</a></td>
  </tr>
{/foreach}  
</table>
</div>
