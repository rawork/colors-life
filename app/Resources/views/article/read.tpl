<h1>{$item.name}</h1>	
<div id="fb-root"></div>
<script>initFB()</script>
<script type="text/javascript" src="//vk.com/js/api/openapi.js?56"></script>
<script type="text/javascript">initVK();</script>
<div class="fb-like" style="display: inline-block;" data-href="http://{$smarty.server.SERVER_NAME}{raURL node=articles method=read prms=$item.id}" data-send="false" data-layout="button_count" data-width="100" data-show-faces="true"></div>
<div style="display: inline-block;" id="vk_like"></div>
<script type="text/javascript">initVKLike()</script>
<div class="article-text">{$item.body}</div>
<div class="fb-comments" data-href="http://{$smarty.server.SERVER_NAME}{raURL node=$item.node_id_name method=read prms=$item.id}" data-num-posts="2" data-width="670"></div>
