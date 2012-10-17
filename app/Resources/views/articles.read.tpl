{if !$param0}{raException}{/if}
{raItem var=item table=article_article query=$param0}
{if $item.publish}
<div id="fb-root"></div>
<script>initFB()</script>
<script type="text/javascript" src="//vk.com/js/api/openapi.js?56"></script>
<script type="text/javascript">initVK();</script>
{raSetVar var=title value=$item.name}	
<h1>{$item.name}</h1>
<div class="fb-like" style="display: inline-block;" data-href="http://{$smarty.server.SERVER_NAME}{raURL node=articles method=read prms=$item.id}" data-send="false" data-layout="button_count" data-width="100" data-show-faces="true"></div>
<div style="display: inline-block;" id="vk_like"></div>
<script type="text/javascript">initVKLike()</script>
<div class="article-text">{$item.body}</div>
<div class="fb-comments" data-href="http://{$smarty.server.SERVER_NAME}{raURL node=$item.node_id_name method=read prms=$item.id}" data-num-posts="2" data-width="670"></div>
{else}
{raException}
{/if}