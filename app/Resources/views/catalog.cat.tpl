{raItem var=cat table=catalog_category query=$param0}
{$cat.description}
{raItems var=items table=catalog_product query="category_id=`$param0` AND publish=1"}
{foreach from=$items key=k item=it}
<div><a href="{raURL node=$node.name method=stuff prms=$it.id}">{$it.name}</a></div>
{/foreach}
