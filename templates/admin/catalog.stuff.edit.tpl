<div class="tabs">
  <ul>
    <li class="{if !stristr($smarty.server.REQUEST_URI, '#') || stristr($smarty.server.REQUEST_URI, '#main')}current{/if}" id="general_tab"><span><a href="#main" onclick="mcTabs.displayTab('general_tab','general_panel'); return false">Основное</a></span></li>
    <li class="{if stristr($smarty.server.REQUEST_URI, '#sizes')}current{/if}" id="sizes_tab"><span><a href="#sizes" onclick="mcTabs.displayTab('sizes_tab','sizes_panel'); return false">Размеры</a></span></li>
    <li class="{if stristr($smarty.server.REQUEST_URI, '#files')}current{/if}" id="files_tab"><span><a href="#files" onclick="mcTabs.displayTab('files_tab','files_panel'); return false">Доп. файлы</a></span></li>
  </ul>
</div>
<div class="panel_wrapper">
  <div id="general_panel" class="{if !stristr($smarty.server.REQUEST_URI, '#') || stristr($smarty.server.REQUEST_URI, '#main')}current{else}panel{/if}">
    {$update_form}
  </div>
  <div id="sizes_panel" class="{if stristr($smarty.server.REQUEST_URI, '#sizes')}current{else}panel{/if}">
    {$sizes_form}
  </div>
  <div id="files_panel" class="{if stristr($smarty.server.REQUEST_URI, '#files')}current{else}panel{/if}">
    {$files_form}
  </div>
</div>
