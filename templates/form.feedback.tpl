{if $frmMessage[1]}<div class="tree-{$frmMessage[0]}">{$frmMessage[1]}</div>{/if}
<form name="frmfeedback" id="frmfeedback" action="" method="post" onsubmit="return checkForm(this)" enctype="multipart/form-data">
  <input type="hidden" name="submited" value="1">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
<td>                                
  <table width="100%" class="frmContainer" border="0" cellspacing="0" cellpadding="3">
            <tr id="tr_fio">
      <td><div>���&nbsp;<span class="required">*</span></div>
        <input type="string" class="txt" title="���" name="fio" value="" /></td>
    </tr>
                <tr id="tr_email">
      <td><div>E-mail&nbsp;<span class="required">*</span></div>
        <input type="string" class="txt" title="E-mail" name="email" value="" /></td>
    </tr>
                <tr id="tr_phone">
      <td><div>�������</div>
        <input type="string" class="txt" title="" name="phone" value="" /></td>
    </tr>
                <tr id="tr_address">
      <td><div>�����</div>
        <input type="string" class="txt" title="" name="address" value="" /></td>
    </tr>
                               
                
        <tr>
      <td><strong><span class="required">*</span></strong> &#8212; ������������ ����</td>
    </tr>    <tr>
      <td><input type="submit" class="btn" value="���������" /></td>
    </tr>
  </table>
</td>
<td>
<table width="100%" class="frmContainer" border="0" cellspacing="0" cellpadding="3">
            
                <tr id="tr_thema">
      <td><div>���� ���������</div>
        <select title="" name="thema"> 
        <option value="0">...</option>
                <option value="�����������">�����������</option>
                <option value="������� 0+">������� 0+</option>
                <option value="�������������� �������">�������������� �������</option>
                <option value="������������ ����������">������������ ����������</option>
                <option value="����">����</option>
                <option value="������� �����">������� �����</option>
                <option value="������ ��� �������������">������ ��� �������������</option>
                <option value="����� ��� ����������">����� ��� ����������</option>
                <option value="����� ��� �����">����� ��� �����</option>
                <option value="������� ������������� ���������">������� ������������� ���������</option>
                <option value="�������� ��� ����� ������">�������� ��� ����� ������</option>
                <option value="�������� ��� ������������� �����">�������� ��� ������������� �����</option>
                <option value="�������� ��� ������">�������� ��� ������</option>
                <option value="���������� �������">���������� �������</option>
                <option value="���� �� ��������">���� �� ��������</option>
                <option value="���� �� �����">���� �� �����</option>
                <option value="���� �� �����">���� �� �����</option>
                <option value="�������� ��������">�������� ��������</option>
                </select></td>
    </tr>
                <tr id="tr_body">
      <td><div>���������&nbsp;<span class="required">*</span></div>
        <textarea rows="5" title="���������" name="body" /></textarea></td>
    </tr>
                
        <tr>
      <td><strong><span class="required">*</span></strong> &#8212; ������������ ����</td>
    </tr>    
  </table>
</td>
</tr>
</table>  
</form> 