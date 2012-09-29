<?php
include('settings.php');

/*** Permission ***/
$bReadOnly0=false;
$bReadOnly1=true;
$bReadOnly2=false;
$bReadOnly3=false;
/*** /Permission ***/

$sBaseRoot0="";
$sBaseRoot1="";
$sBaseRoot2="";
$sBaseRoot3="";
$sBaseRoot0=str_replace($sBaseVirtual0,'',$sBase0); 
if($sBase1!="")$sBaseRoot1=str_replace($sBaseVirtual1,'',$sBase1);
if($sBase2!="")$sBaseRoot2=str_replace($sBaseVirtual2,'',$sBase2); 
if($sBase3!="")$sBaseRoot3=str_replace($sBaseVirtual3,'',$sBase3);

$sMsg = "";
$currFolder=$sBase0;
$ffilter="";
$sUploadedFile="";

$MaxFileSize = $MAX_FILE_SIZE;
//$AllowedTypes = "/(gif|jpg|png|doc|xls|pdf|zip)$/i";
$AllowedTypes = "*";

function isTypeAllowed($sFileName) {
	global $AllowedTypes;
	if($AllowedTypes=="*") return true;
	return preg_match($AllowedTypes,getExt($sFileName));
}

if(isset($_FILES["File1"]))
	{
	if(isset($_POST["inpCurrFolder2"]))$currFolder=$_POST['inpCurrFolder2'];
	if(isset($_REQUEST["inpFilter"]))$ffilter=$_REQUEST["inpFilter"]; 
	if($MaxFileSize && ($_FILES['File1']['size'] > $MaxFileSize))
		{
		$sMsg = "The file exceeds the maximum size allowed.";
		}
	elseif(!isTypeAllowed($_FILES['File1']['name']))
		{
		$sMsg = "The File Type is not allowed.";
		}
	elseif (move_uploaded_file($_FILES['File1']['tmp_name'], $currFolder."/".basename(strtolower($container->get('util')->translitStr($_FILES['File1']['name']))))) 
		{
		$sMsg = "";
		$sUploadedFile=$container->get('util')->translitStr($_FILES['File1']['name']);
		@chmod($currFolder."/".basename(strtolower($container->get('util')->translitStr($_FILES['File1']['name']))), 0644);
		}
	else 
		{
		$sMsg = "Upload failed.";
		}
	}
else
	{
	if(isset($_GET["inpCurrFolder"]))$currFolder=$_GET['inpCurrFolder'];
	if(isset($_REQUEST["ffilter"]))$ffilter=$_REQUEST["ffilter"]; 
	}

if(isset($_POST["inpFileToDelete"]))
	{
	$filename=pathinfo($_POST["inpFileToDelete"]);
	$filename=$filename['basename'];
	if($filename!="")
		unlink($currFolder . "/" . $filename);
	$sMsg = "";
	}
	

/*** Permission ***/
$bWriteFolderAdmin=false;
if($sBase0!="")
	{
	if(strtolower($currFolder)!=str_replace(strtolower($sBase0),"",strtolower($currFolder)) AND $bReadOnly0==true) $bWriteFolderAdmin=true;
	}
if($sBase1!="")
	{
	if(strtolower($currFolder)!=str_replace(strtolower($sBase1),"",strtolower($currFolder)) AND $bReadOnly1==true) $bWriteFolderAdmin=true;
	}
if($sBase2!="")
	{
	if(strtolower($currFolder)!=str_replace(strtolower($sBase2),"",strtolower($currFolder)) AND $bReadOnly2==true) $bWriteFolderAdmin=true;
	}
if($sBase3!="")
	{
	if(strtolower($currFolder)!=str_replace(strtolower($sBase3),"",strtolower($currFolder)) AND $bReadOnly3==true) $bWriteFolderAdmin=true;
	}
$sFolderAdmin="";
if($bWriteFolderAdmin)$sFolderAdmin="style='display:none'";
/*** /Permission ***/
	
function writeFolderSelections() {
	global $sBase0;
	global $sBase1;
	global $sBase2;
	global $sBase3;
	global $sName0;
	global $sName1;	
	global $sName2;
	global $sName3;	
	global $currFolder;
	
	$ret = '';
	$ret .= '<ul id="browser" class="filetree">';
	$ret .= recursive($sBase0,$sBase0,$sName0);
	if($sBase1!="") $ret .= recursive($sBase1,$sBase1,$sName1);
	if($sBase2!="") $ret .= recursive($sBase2,$sBase2,$sName2);
	if($sBase3!="") $ret .= recursive($sBase3,$sBase3,$sName3);
	$ret .= "</ul>";
	echo $ret;
}

function recursive($sPath,$sPath_base,$sName){
	global $sLang;
	global $sBase0;
	global $sBase1;
	global $sBase2;
	global $sBase3;
	global $currFolder;
	$ret = '';
	if($sPath==$sBase0 ||$sPath==$sBase1 ||$sPath==$sBase2 ||$sPath==$sBase3) {
		if($currFolder==$sPath || stristr($currFolder, $sPath))
			$ret .= '<li><span class="folder"><a href="fmanager.php?mlang='.$sLang.'&Type=File&inpCurrFolder='.$sPath.'">'.$sName.'</a>&nbsp;</span>';
		else
			$ret .= '<li class="closed"><span class="folder"><a href="fmanager.php?mlang='.$sLang.'&Type=File&inpCurrFolder='.$sPath.'">'.$sName.'</a>&nbsp;</span>';
	}	
	$oItem=opendir($sPath);   
	while($sItem=readdir($oItem)) 
		{   
		if($sItem=="."||$sItem=="..") {
		
		} else { 
			$sCurrent=$sPath."/".$sItem;
			$fIsDirectory=is_dir($sCurrent);
			
			$sDisplayed=str_replace($sBase0,"",$sCurrent);
			if($sBase1<>"") $sDisplayed=str_replace($sBase1,"",$sDisplayed);
			if($sBase2<>"") $sDisplayed=str_replace($sBase2,"",$sDisplayed);
			if($sBase3<>"") $sDisplayed=str_replace($sBase3,"",$sDisplayed);
			$sDisplayed=$sName.$sDisplayed;
			
			if($fIsDirectory) {
				if($currFolder==$sCurrent || stristr($currFolder, $sCurrent))
					$ret .= '<li><a href="fmanager.php?mlang='.$sLang.'&Type=File&inpCurrFolder='.$sCurrent.'"><span class="folder">'.$sItem.'</span></a>';
				else
					$ret .= '<li class="closed"><a href="fmanager.php?mlang='.$sLang.'&Type=File&inpCurrFolder='.$sCurrent.'"><span class="folder">'.$sItem.'</span></a>'; 
				$subret = recursive($sCurrent,$sPath,$sName);
				if ($subret) {
					$ret .= '<ul>'.$subret.'</ul>';
				}
				$ret .= '</li>'."\r\n";
			}				
		} 
	}  
	closedir($oItem);
	if($sPath==$sBase0 ||$sPath==$sBase1 ||$sPath==$sBase2 ||$sPath==$sBase3) {
		$ret .= '</li>'."\r\n";
	}
	return $ret;
}

//ffilter
function getExt($sFileName){
	$sTmp=$sFileName;
	while($sTmp!="") 
		{
		$sTmp=strstr($sTmp,".");
		if($sTmp!="")
			{
			$sTmp=substr($sTmp,1);
			$sExt=$sTmp;
			}
		}
	return strtolower($sExt);
}

function writeFileSelections(){
	global $sFolderAdmin;
	global $ffilter;
	global $sUploadedFile;
	global $sBaseRoot0;
	global $sBaseRoot1;
	global $sBaseRoot2;
	global $sBaseRoot3;
	global $currFolder;
	
	$nIndex=0;
	$bFileFound=false;
	$iSelected="";
	
	echo "<div style='overflow:auto;height:222px;width:100%;margin-top:3px;margin-bottom:2px;'>";
	echo "<table border=0 cellpadding=2 cellspacing=0 width=100% height=100% >";
	$sColor = "#e7e7e7";
	
	$oItem=opendir($currFolder);
	$files = array();
	while($sItem=readdir($oItem)) 
		{
		if($sItem=="."||$sItem=="..") 
			{
			} 
		else 
			{ 
			$sCurrent=$currFolder."/".$sItem;
			$fIsDirectory=is_dir($sCurrent);

			
			if(!$fIsDirectory) 
				{
				
				//ffilter ~~~~~~~~~~
				$bDisplay=false;
				$sExt=getExt($sItem);
				if($ffilter=="flash")
					{
					if($sExt=="swf")$bDisplay=true;
					}
				else if($ffilter=="media")
					{
					if ($sExt=="avi" || $sExt=="wmv" || $sExt=="mpg" || $sExt=="mpeg" || $sExt=="wav" || $sExt=="wma" || $sExt=="mid" || $sExt=="mp3") $bDisplay=true;
					}
				else if($ffilter=="image")
					{
					if ($sExt=="gif" || $sExt=="jpg" || $sExt=="png") $bDisplay=true;
					}
				else //all
					{
					$bDisplay=true;
					}				
				//~~~~~~~~~~~~~~~~~~				
				
				if($bDisplay)
					{
					$nIndex=$nIndex+1;
					$bFileFound=true;
					
					$sCurrent_virtual=str_replace($sBaseRoot0,"",$sCurrent);
					if($sBaseRoot1!="")$sCurrent_virtual=str_replace($sBaseRoot1,"",$sCurrent_virtual);
					if($sBaseRoot2!="")$sCurrent_virtual=str_replace($sBaseRoot2,"",$sCurrent_virtual);
					if($sBaseRoot3!="")$sCurrent_virtual=str_replace($sBaseRoot3,"",$sCurrent_virtual);
					
					if($sColor=="#f5f5f5")
						$sColor = "";
					else
						$sColor = "#f5f5f5";
						
					//icons
					$sIcon="/icons/default.icon.gif";
					switch ($sExt) {
						case 'mid':
						case 'wav':
						case 'wma': $sIcon="/icons/sound.gif"; break;
						case 'avi':
						case 'mpeg':
						case 'mpg':
						case 'wmv':
						case 'flv': $sIcon="/icons/video.gif"; break;
						default: if (file_exists($GLOBALS['PRJ_DIR'].'/admin/editor/fmanager/images'.'/icons/'.$sExt.'.gif')) {$sIcon="/icons/".$sExt.".gif";}
					}
						
					$sTmp1=strtolower($sItem);
					$sTmp2=strtolower($sUploadedFile);
					if($sTmp1==$sTmp2)
						{
						$sColorResult="yellow";
						$iSelected=$nIndex;
						}
					else
						{
						$sColorResult=$sColor;
						}
					$files[$sItem] = array('sColorResult' => $sColorResult, 'sIcon' => $sIcon, 'nIndex' => $nIndex, 'sItem' => $sItem, 'sCurrent_virtual' => $sCurrent_virtual, 'sCurrent' => $sCurrent, 'sFolderAdmin' => $sFolderAdmin);
					}
				}				
			} 
		}
	ksort($files);	 
	reset($files);
	foreach($files as $f) {
		echo "<tr style='background:".$f['sColorResult']."'>";
		echo "<td><img src='images/".$f['sIcon']."'></td>";
		echo "<td valign=top style='cursor:pointer;' onclick=\"selectFile(".$f['nIndex'].")\" width=100% ><u id=\"idFile".$f['nIndex']."\">".$f['sItem']."</u></td>";
		echo "<input type=hidden name=inpFile".$f['nIndex']." id=inpFile".$f['nIndex']." value=\"".$f['sCurrent_virtual']."\">";
		echo "<td valign=top align=right nowrap>".round(filesize($f['sCurrent'])/1024,1)." kb&nbsp;</td>";
		echo "<td valign=top nowrap onclick=\"deleteFile(".$f['nIndex'].")\"><u style='font-size:10px;cursor:pointer;color:crimson' ".$f['sFolderAdmin']."><script>document.write(getText('del'))</script></u></td>";
		echo "</tr>";
	}				
	if($bFileFound==false)
		echo "<tr><td colspan=4 height=100% align=center><script>document.write(getText('Empty...'))</script></td></tr></table></div>";
	else
		echo "<tr><td colspan=4 height=100% ></td></tr></table></div>";
		
	echo "<input type=hidden name=inpUploadedFile id=inpUploadedFile value='".$iSelected."'>";
	echo "<input type=hidden name=inpNumOfFiles id=inpNumOfFiles value='".$nIndex."'>";
		
	closedir($oItem); 	
}
?>
<base target="_self">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="style.css" rel="stylesheet" type="text/css">
<link href="jquery.treeview.css" rel="stylesheet" type="text/css">
<?php
$sLang="english";
if(!empty($_REQUEST["mlang"])){
	$sLang=$_REQUEST["mlang"]; 
}
?>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>
<script type="text/javascript" src="js/jquery.treeview.js"></script>
<script>
	var sLang="<?php echo $sLang ?>";
	document.write("<scr"+"ipt src='language/"+sLang+"/asset.js'></scr"+"ipt>");
	$(document).ready(function(){
		$("#browser").treeview();
	});
</script>
<script>writeTitle()</script>
<script>
var bReturnAbsolute=<?php if($bReturnAbsolute){echo "true";} else{echo "false";} ?>;
var currFolder='<?php echo $currFolder; ?>';
</script>
<script type="text/javascript" src="js/common.js"></script>
</head>
<body onUnload="doUnload()" onLoad="loadText();this.focus();if(document.getElementById('inpUploadedFile').value!='')selectFile(document.getElementById('inpUploadedFile').value);" style="overflow:hidden;margin:0px;">
<input type="hidden" name="inpAssetBaseFolder0" id="inpAssetBaseFolder0" value="<?php echo $sBase0 ?>">
<input type="hidden" name="inpAssetBaseFolder1" id="inpAssetBaseFolder1" value="<?php echo $sBase1 ?>">
<input type="hidden" name="inpAssetBaseFolder2" id="inpAssetBaseFolder2" value="<?php echo $sBase2 ?>">
<input type="hidden" name="inpAssetBaseFolder3" id="inpAssetBaseFolder3" value="<?php echo $sBase3 ?>">
<input type="hidden" name="curFolder" id="selCurrFolder" value="<?php echo $currFolder ?>">
<table width="100%" height="100%" align=center style="" cellpadding=0 cellspacing=0 border=0 >
<tr>
<td valign=top style="background:url('bg.gif') no-repeat right bottom;padding-top:5px;padding-left:5px;padding-right:5px;padding-bottom:0px;">
	<!--ffilter-->
	<form method=post name="Form1" id="Form1">
		<input type="hidden" name="inpFileToDelete">
		<input type="hidden" name="inpCurrFolder">
	</form>
	<table width=100% style="height:100%" border="0">
	<tr>
		<td rowspan="3" valign="top" style="border-right:1px dotted #999999;background-color:#ffffff;"><?php writeFolderSelections(); ?></td>	
		<td>
				<table cellpadding="2" cellspacing="2" border="0">
				<tr>
				<td nowrap>
					<span onClick="newFolder('<?php echo $currFolder;?>')" style="cursor:pointer;" <?php echo $sFolderAdmin;?>><u><span name="txtLang" id="txtLang">New&nbsp;Folder</span></u></span>&nbsp;
					<span onClick="deleteFolder('<?php echo $currFolder;?>')" style="cursor:pointer;" <?php echo $sFolderAdmin;?>><u><span name="txtLang" id="txtLang">Del&nbsp;Folder</span></u></span>
				</td>
				<td  width=100% align="right">

				<?php			
				//ffilter~~~~~~~~~
					$sHTMLFilter = "<select name=selFilter id=selFilter onchange='applyFilter()' class='inpSel'>"; //ffilter
					$sAll="";
					$sMedia="";
					$sImage="";
					$sFlash="";	
					if($ffilter=="") $sAll="selected";
					if($ffilter=="media") $sMedia="selected";
					if($ffilter=="image") $sImage="selected";
					if($ffilter=="flash") $sFlash="selected";
					$sHTMLFilter = $sHTMLFilter."	<option name=optLang id=optLang value='' ".$sAll."></option>";
					$sHTMLFilter = $sHTMLFilter."	<option name=optLang id=optLang value='media' ".$sMedia."></option>";
					$sHTMLFilter = $sHTMLFilter."	<option name=optLang id=optLang value='image' ".$sImage."></option>";
					$sHTMLFilter = $sHTMLFilter."	<option name=optLang id=optLang value='flash' ".$sFlash."></option>";
					$sHTMLFilter = $sHTMLFilter."</select>";
					echo $sHTMLFilter;
				//~~~~~~~~~
				?>

				</td>
				</tr>
				</table>
		</td>
	</tr>		
	<tr>
		<td valign=top align="center">
		
				<table width=100% cellpadding=0 cellspacing=0>
				<tr>
				<td>
					<div id="idPreview" style="text-align:center;overflow:auto;width:297;height:245;border:#d7d7d7 5px solid;border-bottom:#d7d7d7 3px solid;background:#ffffff;margin-right:2;"></div>
					<div align=center><input type="text" id="inpSource" name="inpSource" style="border:#cfcfcf 1px solid;width:295" class="inpTxt"></div>
				</td>
				<td valign=top width=100%>				
					<?php writeFileSelections(); ?>
				</td>
				</tr>
				</table>
							
		</td>
	</tr>
	<tr>
		<td>
			<div <?php echo $sFolderAdmin;?>>
			<div style="height:12px">
				<font color=red><?php echo $sMsg ?></font>
				<span style="font-weight:bold" id=idUploadStatus></span>
			</div>
				<form enctype="multipart/form-data" method="post" runat="server" name="Form2" id="Form2">
				<input type="hidden" name="inpCurrFolder2" ID="inpCurrFolder2">
				<!--ffilter-->
				<input type="hidden" name="inpFilter" ID="inpFilter" value="<? echo $ffilter ?>">
				<span name="txtLang" id="txtLang">Upload File</span>: <input type="file" id="File1" name="File1" class="inpTxt">&nbsp;
				<input name="btnUpload" id="btnUpload" type="button" value="upload" onClick="upload()" class="inpBtn" onMouseOver="this.className='inpBtnOver';" onMouseOut="this.className='inpBtnOut'">
				</form>
			</div>
		</td>
	</tr>
	</table>
</td>
</tr>
<tr>
<td class="dialogFooter" style="height:40px;padding-right:15px;" align=right valign=middle>
	<table cellpadding=0 cellspacing=0 ID="Table2">
	<tr>
	<td>
	<input name="btnOk" id="btnOk" type="button" value=" ОК " onClick="doOk()" class="inpBtn" onMouseOver="this.className='inpBtnOver';" onMouseOut="this.className='inpBtnOut'">
    <input name="btnClose" id="btnClose" type="button" value=" Закрыть " onClick="window.close();" class="inpBtn" onMouseOver="this.className='inpBtnOver';" onMouseOut="this.className='inpBtnOut'">
	</td>
	</tr>
	</table>
</td>
</tr>
</table>

</body>
</html>