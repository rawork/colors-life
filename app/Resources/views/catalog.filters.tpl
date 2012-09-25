{if $methodName == 'index' && $param0}
{raItem var=cat table=catalog_categories query=$param0}
{if $cat} 
{raItems var=filters table=catalog_features query="id IN (`$cat.filters`)"}
{if count($filters)}
<div class="filter-title"><span>Фильтры</span></div>
<div class="filter-block">
<form name="frmFilters" action="" method="get"> 
{if $smarty.get.rtt}
<input type="hidden" name="rtt" value="{$smarty.get.rtt}">
{/if}
{foreach from=$filters item=filter}
{raItems var=filter_values table=catalog_features_variants query="filter_id=`$filter.id`"}
{foreach from=$filter_values item=filter_value}
{$filters_values2[$filter.id]}<input type="radio" name="filter_{$filter.id}" value="{$filter_value.id}"{if isset($filters_values2[$filter.id]) && $filters_values2[$filter.id] == $filter_value.id} checked{/if}>{$filter_value.name}<br>
{/foreach}
<br>
{/foreach}
<input type="submit" value="Выбрать"> 
<input type="button" onclick="unsetFilter()" value="Отключить"> 
<br>
</form>
 </div>
 {/if}
 {/if}
 {/if}
