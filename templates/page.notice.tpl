{if !$smarty.get.order}
�� ������ ����� ������!
{else}
{assign var=iOrder value=$smarty.get.order-100000}
{raItem var=oOrder table=cart_order query=$iOrder}
{if count($oOrder)}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<title>���������</title>
	<link href="css/docs.css" rel="stylesheet" type="text/css">
</head><body>
	<br><br><br>
	<table class="account2" align="center" border="0" cellpadding="0" cellspacing="0" width="736">
	<tbody><tr>
		<td rowspan="8" class="bord_r" valign="top" width="211"><br>&nbsp;&nbsp;&nbsp;���������</td>
		<td rowspan="8" valign="top" width="11"></td>
		<td colspan="13" class="bord_n" align="center" valign="bottom" width="382"><strong><br>�������������� ��������������� ����� ��������� �����������</strong></td>
		<td colspan="3" rowspan="2" valign="bottom" width="133"></td>
	</tr>
	<tr>
		<td colspan="13" align="center" valign="top" width="382"><sup>(������������    ���������� �������)</sup></td>
	</tr>
	<tr>
		<td colspan="6" class="bord_n" align="center" valign="bottom" width="251">772155915054</td>
		<td colspan="3" align="center" valign="bottom" width="26">�</td>
		<td colspan="7" class="bord_n" align="center" valign="bottom" width="238">40802810860610518601</td>
	</tr>
	<tr>
		<td colspan="6" align="center" valign="top" width="251"><sup>(��� ���������� �������)</sup></td>
		<td colspan="3" valign="bottom" width="26"></td>
		<td colspan="7" align="center" valign="top" width="238"><sup>(����� �����    ���������� �������)</sup></td>
	</tr>
	<tr>
		<td valign="bottom" width="75">�</td>
		<td colspan="15" class="bord_n" align="center" valign="bottom" width="439">��� �������������� �. ������</td>
	</tr>
	<tr>
		<td valign="bottom" width="75"></td>
		<td colspan="15" align="center" valign="top" width="439"><sup>(������������ �����    ���������� �������)</sup></td>
	</tr>
	<tr>
		<td valign="bottom" width="75">���</td>
		<td colspan="5" class="bord_n" align="center" valign="bottom" width="175">044525555</td>
		<td colspan="3" align="center" valign="bottom" width="26">�</td>
		<td colspan="7" class="bord_n" align="center" valign="bottom" width="238">30101810400000000555</td>
	</tr>
	<tr>
		<td colspan="10" valign="bottom" width="278"></td>
		<td colspan="6" align="center" nowrap="nowrap" valign="top" width="237"><sup>(����� ���./��.    ����� ���������� �������)</sup>&nbsp;&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td rowspan="9" class="bord_rn" valign="bottom" width="211">&nbsp;&nbsp;&nbsp;������<br><br></td>
		<td rowspan="9" class="bord_n" valign="bottom" width="11"></td>
		<td colspan="16" class="bord_n" valign="bottom" width="515">������ ������ �� ����� ��-{$oOrder.id+100000} �� {$oOrder.credate|fdate} </td>
	</tr>
	<tr>
		<td valign="bottom" width="75"></td>
		<td colspan="3" valign="bottom" width="75"></td>
		<td valign="bottom" width="75"></td>
		<td colspan="11" valign="top" width="289"><sup>(������������  �������)</sup></td>
	</tr>
	<tr>
		<td colspan="4" valign="bottom" width="151">�.�.�.    �����������</td>
		<td colspan="12" class="bord_n" valign="bottom" width="364">{$oOrder.fio}</td>
	</tr>
	<tr>
		<td colspan="4" valign="bottom" width="151">�����  �����������</td>
		<td colspan="12" class="bord_n" valign="bottom" width="364">{$oOrder.address}</td>
	</tr>
	<tr>
		<td colspan="4" valign="bottom" width="151">�����    �������</td>
		<td colspan="11" class="bord_n" valign="bottom" width="75">{$oOrder.summa} ���.</td>
		<td valign="bottom" width="56"></td>
	</tr>
	<tr>
		<td colspan="16" valign="bottom" width="515"><sup>�    ��������� 
������ ��������� � ��������� ��������� �����, � �.�. � ������    
���������  ����� �� ������ �����    ���������� � ��������.</sup></td>
	</tr>
	<tr>
		<td colspan="2" valign="bottom" width="94">����    �������</td>
		<td class="bord_n" valign="bottom" width="38">(&nbsp;&nbsp;&nbsp;&nbsp;) </td>
		<td colspan="2" valign="bottom" width="94"> </td>
		<td colspan="2" valign="bottom" width="29">  201</td>
		<td class="bord_n" valign="bottom" width="14"></td>
		<td colspan="3" valign="bottom" width="14">�.</td>
		<td colspan="3" nowrap="nowrap" valign="bottom" width="149">&nbsp;&nbsp;&nbsp;�������    �����������</td>
		<td colspan="2" class="bord_n" valign="bottom" width="82"></td>
	</tr>
	<tr>
		<td colspan="16" height="5" valign="bottom">    </td>
	</tr>
	<tr>
		<td colspan="16" class="bord_n" valign="bottom" width="515"></td>
	</tr>
	<tr>
		<td rowspan="8" class="bord_r" valign="top" width="211"><br>&nbsp;&nbsp;&nbsp;���������</td>
		<td rowspan="8" valign="top" width="11"></td>
		<td colspan="13" class="bord_n" align="center" valign="bottom" width="382"><strong><br>�������������� ��������������� ����� ��������� �����������</strong></td>
		<td colspan="3" rowspan="2" valign="bottom" width="133"></td>
	</tr>
	<tr>
		<td colspan="13" align="center" valign="top" width="382"><sup>(������������    ���������� �������)</sup></td>
	</tr>
	<tr>
		<td colspan="6" class="bord_n" align="center" valign="bottom" width="251">772155915054 </td>
		<td colspan="3" align="center" valign="bottom" width="26">�</td>
		<td colspan="7" class="bord_n" align="center" valign="bottom" width="238">40802810860610518601 </td>
	</tr>
	<tr>
		<td colspan="6" align="center" valign="top" width="251"><sup>(��� ���������� �������)</sup></td>
		<td colspan="3" valign="bottom" width="26"></td>
		<td colspan="7" align="center" valign="top" width="238"><sup>(����� �����    ���������� �������)</sup></td>
	</tr>
	<tr>
		<td valign="bottom" width="75">�</td>
		<td colspan="15" class="bord_n" align="center" valign="bottom" width="439">��� �������������� �. ������</td>
	</tr>
	<tr>
		<td valign="bottom" width="75"></td>
		<td colspan="15" align="center" valign="top" width="439"><sup>(������������ �����    ���������� �������)</sup></td>
	</tr>
	<tr>
		<td valign="bottom" width="75">���</td>
		<td colspan="5" class="bord_n" align="center" valign="bottom" width="175">044525555 </td>
		<td colspan="3" align="center" valign="bottom" width="26">�</td>
		<td colspan="7" class="bord_n" align="center" valign="bottom" width="238">30101810400000000555 </td>
	</tr>
	<tr>
		<td colspan="10" valign="bottom" width="278"></td>
		<td colspan="6" align="center" nowrap="nowrap" valign="top" width="237"><sup>(����� ���./��.    ����� ���������� �������)</sup>&nbsp;&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td rowspan="9" class="bord_r" valign="bottom" width="211">&nbsp;&nbsp;&nbsp;������<br><br></td>
		<td rowspan="9" valign="bottom" width="11"></td>
		<td colspan="16" class="bord_n" valign="bottom" width="515">������ ������ �� ����� ��-{$oOrder.id+100000} �� {$oOrder.credate|fdate}</td>
	</tr>
	<tr>
		<td valign="bottom" width="75"></td>
		<td colspan="3" valign="bottom" width="75"></td>
		<td valign="bottom" width="75"></td>
		<td colspan="11" valign="top" width="289"><sup>(������������  �������)</sup></td>
	</tr>
	<tr>
		<td colspan="4" valign="bottom" width="151">�.�.�.    �����������</td>
		<td colspan="12" class="bord_n" valign="bottom" width="364">{$oOrder.fio}</td>
	</tr>
	<tr>
		<td colspan="4" valign="bottom" width="151">�����  �����������</td>
		<td colspan="12" class="bord_n" valign="bottom" width="364">{$oOrder.address}</td>
	</tr>
	<tr>
		<td colspan="4" valign="bottom" width="151">�����    �������</td>
		<td colspan="11" class="bord_n" valign="bottom" width="75">{$oOrder.summa} ���.</td>
		<td valign="bottom" width="56"></td>
	</tr>
	<tr>
		<td colspan="16" valign="bottom" width="515"><sup>�    ��������� 
������ ��������� � ��������� ��������� �����, � �.�. � ������    
���������  ����� �� ������ �����    ���������� � ��������.</sup></td>
	</tr>
	<tr>
		<td colspan="2" valign="bottom" width="94">����    �������</td>
		<td class="bord_n" valign="bottom" width="38">(&nbsp;&nbsp;&nbsp;&nbsp;)</td>
		<td colspan="2" valign="bottom" width="94">  </td>
		<td colspan="2" valign="bottom" width="29">  201</td>
		<td class="bord_n" valign="bottom" width="14"></td>
		<td colspan="3" valign="bottom" width="14">�.</td>
		<td colspan="3" nowrap="nowrap" valign="bottom" width="149">&nbsp;&nbsp;&nbsp;�������    �����������</td>
		<td colspan="2" class="bord_n" valign="bottom" width="82"></td>
	</tr>
	<tr>
		<td colspan="2" height="5" valign="bottom">
		</td>
		<td valign="bottom"></td>
		<td colspan="2" valign="bottom"></td>
		<td colspan="2" valign="bottom"></td>
		<td valign="bottom"></td>
		<td colspan="3" valign="bottom"></td>
		<td colspan="3" valign="bottom"></td>
		<td colspan="2" valign="bottom"></td>
	</tr>
	<tr>
		<td colspan="16" valign="bottom" width="515"></td>
	</tr>
	</tbody></table>
	</body></html>
{else}
������ ��������� � ����� ���������!
{/if}	
{/if}	