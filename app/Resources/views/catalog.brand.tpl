{raItem var=prod table=catalog_producer query=$param0}
<h1>{$prod.name}</h1>
{raSetVar var=title value=$prod.name}
<div>{$prod.description}</div>

<h3><span>Товары производителя:</span></h3>
{raItems var=items table=catalog_product nquery="SELECT cc.id, cc.root_id, cc.title, cc2.title as root_title, st.producer_id, count(st.id) as quantity FROM catalog_product st JOIN catalog_producer prod ON prod.id=st.producer_id JOIN catalog_category cc ON cc.id=st.category_id JOIN catalog_category cc2 ON cc.root_id=cc2.id WHERE st.producer_id=`$prod.id` GROUP BY st.category_id ORDER BY cc.root_id, cc.title"}
<table class="producer-categories" width="100%" cellpadding="0" cellspacing="0" border="0">
{assign var=cur_root value=$items[0].root_id}
<tr>
<td class="root-cat">{$items[0].root_title}</td>
</tr>
{foreach from=$items key=k item=item}
{if $cur_root != $item.root_id}
<tr>
<td class="root-cat">{$item.root_title}</td>
</tr>
{assign var=cur_root value=$item.root_id}
{/if}
<tr>
<td><a href="{raURL node=catalog method=index prms="`$item.id`.name.`$prod.id`"}">{$item.title}</a> ({$item.quantity})</td>
</tr>
{/foreach}
</table>