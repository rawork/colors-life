{raItem var=prod table=catalog_producers query=$param0}
<h1>{$prod.name}</h1>
{raSetVar var=title value=$prod.name}
<div>{$prod.description}</div>

<h3><span>Товары производителя:</span></h3>
{raItems var=items table=catalog_stuff nquery="SELECT cc.id, cc.root_c_id, cc.name, cc2.name as root_name, st.producer_id, count(st.id) as quantity FROM catalog_stuff st JOIN catalog_producers prod ON prod.id=st.producer_id JOIN catalog_categories cc ON cc.id=st.c_id JOIN catalog_categories cc2 ON cc.root_c_id=cc2.id WHERE st.producer_id=`$prod.id` GROUP BY st.c_id ORDER BY cc.root_c_id, cc.name"}
<table class="producer-categories" width="100%" cellpadding="0" cellspacing="0" border="0">
{assign var=cur_root value=$items[0].root_c_id}
<tr>
<td class="root-cat">{$items[0].root_name}</td>
</tr>
{foreach from=$items key=k item=item}
{if $cur_root != $item.root_c_id}
<tr>
<td class="root-cat">{$item.root_name}</td>
</tr>
{assign var=cur_root value=$item.root_c_id}
{/if}
<tr>
<td><a href="{raURL node=catalog method=index prms="`$item.id`.name.`$prod.id`"}">{$item.name}</a> ({$item.quantity})</td>
</tr>
{/foreach}
</table>