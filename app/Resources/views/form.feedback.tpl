{if $frmMessage[1]}<div class="tree-{$frmMessage[0]}">{$frmMessage[1]}</div>{/if}
<form name="frmfeedback" id="frmfeedback" action="" method="post" onsubmit="return checkForm(this)" enctype="multipart/form-data">
<input type="hidden" name="submited" value="1">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
<td>                                
  <table width="100%" border="0" cellspacing="0" cellpadding="3">
    <tr>
      <td><div>ФИО <span class="required">*</span></div>
        <input type="text" title="ФИО" name="fio" /></td>
    </tr>
    <tr>
      <td><div>E-mail&nbsp;<span class="required">*</span></div>
        <input type="text" title="E-mail" name="email" /></td>
    </tr>
    <tr>
      <td><div>Телефон</div>
        <input type="text" title="" name="phone" /></td>
    </tr>
    <tr>
      <td><strong><span class="required">*</span></strong> &#8212; обязательные поля</td>
    </tr>    
	<tr>
      <td><input type="submit" class="btn" value="Отправить" /></td>
    </tr>
  </table>
</td>
<td>
<table width="100%">
    <tr>
      <td><div>Тема сообщения</div>
        <select title="" name="thema"> 
        <option value="0">...</option>
                <option value="Дезодоранты">Дезодоранты</option>
                <option value="Игрушки 0+">Игрушки 0+</option>
                <option value="Индивидуальная гигиена">Индивидуальная гигиена</option>
                <option value="Многоразовые подгузники">Многоразовые подгузники</option>
                <option value="Мыло">Мыло</option>
                <option value="Мыльные орехи">Мыльные орехи</option>
                <option value="Наборы для новорожденных">Наборы для новорожденных</option>
                <option value="Серия для беременных">Серия для беременных</option>
                <option value="Серия для детей">Серия для детей</option>
                <option value="Системы естественного пеленания">Системы естественного пеленания</option>
                <option value="Средства для мытья посуды">Средства для мытья посуды</option>
                <option value="Средства для посудомоечных машин">Средства для посудомоечных машин</option>
                <option value="Средства для стирки">Средства для стирки</option>
                <option value="Стиральные порошки">Стиральные порошки</option>
                <option value="Уход за волосами">Уход за волосами</option>
                <option value="Уход за лицом">Уход за лицом</option>
                <option value="Уход за телом">Уход за телом</option>
                <option value="Чистящие средства">Чистящие средства</option>
                </select></td>
    </tr>
    <tr>
      <td><div>Сообщение <span class="required">*</span></div>
        <textarea rows="5" title="Сообщение" name="body" /></textarea></td>
    </tr>
    <tr>
      <td><strong><span class="required">*</span></strong> &#8212; обязательные поля</td>
    </tr>    
  </table>
</td>
</tr>
</table>  
</form> 