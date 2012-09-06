{if $smarty.get.message ne ''}<div class="tree-message">{$smarty.get.message}</div>
{elseif $smarty.get.accept ne ''}<div class="tree-accept">{$smarty.get.accept}</div>
{elseif $smarty.get.error ne ''}<div class="tree-error">{$smarty.get.error}</div>{/if}
{$urlprops.node.body}