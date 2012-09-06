var activeModalWin;
var bOk=false;

function getAction(){
	//Clean previous ffilter=...
	sQueryString=window.location.search.substring(1)
	sQueryString=sQueryString.replace(/ffilter=media/,"")
	sQueryString=sQueryString.replace(/ffilter=image/,"")
	sQueryString=sQueryString.replace(/ffilter=flash/,"")
	sQueryString=sQueryString.replace(/ffilter=/,"")
	if(sQueryString.substring(sQueryString.length-1)=="&")
		sQueryString=sQueryString.substring(0,sQueryString.length-1)
	if(sQueryString.indexOf("=")==-1) {//no querystring
		sAction="fmanager.php?ffilter="+document.getElementById("selFilter").value;
	} else {
		sAction="fmanager.php?"+sQueryString+"&ffilter="+document.getElementById("selFilter").value
	}
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	return sAction;
}

//ffilter
function applyFilter() {
	var Form1 = document.forms.Form1;
	
	Form1.elements.inpCurrFolder.value=document.getElementById("selCurrFolder").value;
	Form1.elements.inpFileToDelete.value="";

	Form1.action=getAction()
	Form1.submit()
}

function refreshAfterDelete(sDestination) {
	var Form1 = document.forms.Form1;
	
	Form1.elements.inpCurrFolder.value=sDestination;
	Form1.elements.inpFileToDelete.value="";
	
	Form1.action=getAction()
	Form1.submit();
}

function upload(){
	var Form2 = document.forms.Form2;
	
	if(Form2.elements.File1.value == "")return;

	var sFile=Form2.elements.File1.value.substring(Form2.elements.File1.value.lastIndexOf("\\")+1);
	for(var i=0;i<document.getElementById("inpNumOfFiles").value;i++)
		{
		if(sFile==document.getElementById("idFile"+(i*1+1)).innerHTML)
			{
			if(confirm(getText("File already exists. Do you want to replace it?"))!=true)return;
			}
		}

	Form2.elements.inpCurrFolder2.value=document.getElementById("selCurrFolder").value;
	document.getElementById("idUploadStatus").innerHTML=getText("Uploading...")
		
	Form2.action=getAction()
	Form2.submit();
}

//moz
function modalDialogShow(url,width,height){
    var left = screen.availWidth/2 - width/2;
    var top = screen.availHeight/2 - height/2;
    activeModalWin = window.open(url, "", "width="+width+"px,height="+height+",left="+left+",top="+top);
    window.onfocus = function(){if (activeModalWin.closed == false){activeModalWin.focus();};};
}

function newFolder(inpCurrFolder){
	if(navigator.appName.indexOf('Microsoft')!=-1)
		window.showModalDialog("foldernew.php?inpCurrFolder="+inpCurrFolder,window,"dialogWidth:250px;dialogHeight:192px;edge:Raised;center:Yes;help:No;resizable:No;");
	else
		modalDialogShow("foldernew.php?inpCurrFolder="+inpCurrFolder, 250, 150);
}

function deleteFolder(inpCurrFolder){
	if(inpCurrFolder==document.getElementById("inpAssetBaseFolder0").value.toLowerCase() ||
	inpCurrFolder==document.getElementById("inpAssetBaseFolder1").value.toLowerCase() ||
	inpCurrFolder==document.getElementById("inpAssetBaseFolder2").value.toLowerCase() ||
	inpCurrFolder==document.getElementById("inpAssetBaseFolder3").value.toLowerCase()){
		alert(getText("Cannot delete Asset Base Folder."));
		return;
	}
	
	if(navigator.appName.indexOf('Microsoft')!=-1)
		window.showModalDialog("folderdel.php?inpCurrFolder="+inpCurrFolder,window,"dialogWidth:250px;dialogHeight:192px;edge:Raised;center:Yes;help:No;resizable:No;");
	else
		modalDialogShow("folderdel.php?inpCurrFolder="+inpCurrFolder, 250, 150);
}

function selectFile(index){
	sFile_RelativePath = document.getElementById("inpFile"+index).value;

	//This will make an Absolute Path
	if(bReturnAbsolute){
		sFile_RelativePath = window.location.protocol + "//" + window.location.host.replace(/:80/,"") + sFile_RelativePath
		//Ini input dr yg pernah pake port:
		//sFile_RelativePath = window.location.protocol + "//" + window.location.host.replace(/:80/,"") + "/" + sFile_RelativePath.replace(/\.\.\//g,"")
	}
	
	document.getElementById("inpSource").value=sFile_RelativePath;
	
	var arrTmp = sFile_RelativePath.split(".");
	var sFile_Extension = arrTmp[arrTmp.length-1]	
	var sHTML="";
	
	//Image
	if(sFile_Extension.toUpperCase()=="GIF" || sFile_Extension.toUpperCase()=="JPG" || sFile_Extension.toUpperCase()=="PNG")
		{
		sHTML = "<img height=\"150\" src=\"" + sFile_RelativePath + "\" >"
		}
	//SWF
	else if(sFile_Extension.toUpperCase()=="SWF")
		{
		sHTML = "<object "+
			"classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' " +
			"width='100%' "+
			"height='100%' " +
			"codebase='http://active.macromedia.com/flash6/cabs/swflash.cab#version=6.0.0.0'>"+
			"	<param name=movie value='"+sFile_RelativePath+"'>" +
			"	<param name=quality value='high'>" +
			"	<embed src='"+sFile_RelativePath+"' " +
			"		width='100%' " +
			"		height='100%' " +
			"		quality='high' " +
			"		pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash'>" +
			"	</embed>"+
			"</object>";
		}
	//Video
	else if(sFile_Extension.toUpperCase()=="WMV"||sFile_Extension.toUpperCase()=="AVI"||sFile_Extension.toUpperCase()=="MPG")
		{
		sHTML = "<embed src='"+sFile_RelativePath+"' hidden=false autostart='true' type='video/avi' loop='true'></embed>";
		}
	//Sound
	else if(sFile_Extension.toUpperCase()=="WMA"||sFile_Extension.toUpperCase()=="WAV"||sFile_Extension.toUpperCase()=="MID")
		{
		sHTML = "<embed src='"+sFile_RelativePath+"' hidden=false autostart='true' type='audio/wav' loop='true'></embed>";
		}
	//Files (Hyperlinks)
	else
		{	
		sHTML = "<br><br><br><br><br><br>Not Available"
		}
		
	document.getElementById("idPreview").innerHTML = sHTML;
}

function deleteFile(index){
	if (confirm(getText("Delete this file ?")) == true) {	
		sFile_RelativePath = document.getElementById("inpFile"+index).value;

		var Form1 = document.getElementById("Form1");
		Form1.elements.inpCurrFolder.value=currFolder;
		Form1.elements.inpFileToDelete.value=sFile_RelativePath;

		Form1.action=getAction()
		Form1.submit();
	}
}

function doOk(){
	window.opener.tinyfck.document.forms[0].elements[window.opener.tinyfck_field].value = document.getElementById("inpSource").value;
	if (window.opener.tinyfck.document.forms[0].elements[window.opener.tinyfck_field].onchange != null) {
		window.opener.tinyfck.document.forms[0].elements[window.opener.tinyfck_field].onchange();
	}
	bOk=true;
	self.close();
	window.opener.tinyfck.focus();
}
	
function doOk1(){
	if(navigator.appName.indexOf('Microsoft')!=-1)
		window.returnValue=inpSource.value;
	else
		window.opener.setAssetValue(document.getElementById("inpSource").value);
	bOk=true;
	self.close();
}

function doUnload(){
	if(navigator.appName.indexOf('Microsoft')!=-1)
		if(!bOk)window.returnValue="";
	else
		if(!bOk)window.opener.setAssetValue("");
}

function changeFolder(sCurrent) {
	window.location = 'fmanager.php?mlang='+sLang+'&Type=File&inpCurrFolder='+sCurrent
}