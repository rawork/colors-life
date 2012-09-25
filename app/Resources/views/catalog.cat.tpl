{raItem var=cat table=catalog_categories query=$param0}
{$cat.description}
{raItems var=items table=catalog_stuff query="c_id=`$param0` AND publish='on'"}
{foreach from=$items key=k item=it}
<div><a href="{raURL node=$node.name method=stuff prms=$it.id}">{$it.name}</a></div>
{/foreach}
