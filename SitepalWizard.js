<script type="text/javascript" language="javascript">
var sAPIHost = "vhost.oddcast.com";
var iAPIPort = 443;
var sAPIProtocol = "https"; 
var sHeadCodePrefixTempl = '<!-- SceneWizAcc%sHeadStart-->'; // use with account ID
var sHeadCodeSuffixTempl = '<!-- SceneWizAcc%sHeadEnd-->';
var sBodyCodePrefixTempl = '<!-- SceneWizSc%sAcc%sBodyStart-->'; // use with Scene ID
var sBodyCodeSuffixTempl = '<!-- SceneWizSc%sAcc%sBodyEnd-->';
var sHeadCodePrefix = sHeadCodePrefixTempl.substr(0, 16);
var sHeadCodeSuffix = sHeadCodeSuffixTempl.substr(sHeadCodeSuffixTempl.length -11);
var sBodyCodePrefix = sBodyCodePrefixTempl.substr(0, 15);
var sBodyCodeSuffix = sBodyCodeSuffixTempl.substr(sBodyCodeSuffixTempl.length -11);

var gObjAccountListResult = null;
var gObjSceneListResult = null;
var gCurrSceneEmbedCode = null;
var gSelectedScene = null;
var gCurrAccountID = '';
var gExistedSceneID = null;
var gExistedScenePos = null;
var gExsitedSceneBodyCodePrefix = '';
var gExistedSceneBodyCodeSuffix = '';
var gID = 'wp';
var gVersion = '1.8';

//////// Scene size UI
var SceneWidth = 400;
var SceneHeight = 300;
var fAspectRatio = 4.0/3.0;
var iScreenResX = 1024;
var iScreenResY =  768;

var gStrVisualModeErrorMsg = "Please switch to HTML mode, the plugin does not work properly in Visual mode.";
// FUNCTIONS DEFINITION
function CreateAjaxObj() 
{
	var ajaxObj;
	var msxmlhttp = new Array('Msxml2.XMLHTTP.5.0','Msxml2.XMLHTTP.4.0','Msxml2.XMLHTTP.3.0','Msxml2.XMLHTTP','Microsoft.XMLHTTP'); 
	//Browser Compatibility: IE, Mozzila, Safari
	for (var i = 0; i < msxmlhttp.length; i++) 
	{
		try 
		{
			ajaxObj = new ActiveXObject(msxmlhttp[i]);
			break;
		} 
		catch (err) 
		{
			ajaxObj = null;
		}
	}

	if (!ajaxObj && (typeof(XMLHttpRequest) != "undefined"))
		ajaxObj = new XMLHttpRequest();

	return ajaxObj;
}


function updateStatus(SW_objDIVStatus, sStatus, iErrorType){
	//iErrorType 0: general information
	//iErrorType 1: Error message
	
	if (SW_objDIVStatus.getAttribute('class') != null){//Firefox
		if (iErrorType == 0)
			SW_objDIVStatus.setAttribute('class', 'updated fade');
		else if (iErrorType == 1)
			SW_objDIVStatus.setAttribute('class', 'error');
	}
	else{//IE
		if (iErrorType == 0)
			SW_objDIVStatus.setAttribute('className', 'updated fade');
		else if (iErrorType == 1)
			SW_objDIVStatus.setAttribute('className', 'error');
	}
		
	SW_objDIVStatus.innerHTML="<p><strong>"+sStatus+"</strong></p>";
	
	var sCommand = "document.getElementById('" + SW_objDIVStatus.getAttribute('Id') + "').innerHTML=''";
	//window.setTimeout(sCommand, 3000);
	//window.setTimeout("document.getElementById('message_FadeBar').innerHTML=''", 2000);
}


function LoadXMLDOM(sResultXML){
	var xmlDoc;
	try{
		if (window.ActiveXObject){
		  xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
		  xmlDoc.async = false;
		  xmlDoc.loadXML(sResultXML);
		  //alert("IE: "+xmlDoc.getElementsByTagName('NumSS').length);
		}else{
			 var parser = new DOMParser();
			 xmlDoc = parser.parseFromString(sResultXML,"text/xml");
			 //alert("Firefox: "+xmlDoc.getElementsByTagName('NumSS').length);
		}	
	}catch(e){
		xmlDoc = null;
		throw(e);
	}
		
	return xmlDoc;
}



function getFormElementByName(oForm, sName){
	for(i = 0; i< oForm.elements.length; i++){
		if(oForm.elements[i].name == sName)
			return oForm.elements[i] ;
	}
	return null;
}


function getElementByName(sTag, sElementName){
	var aElements = document.getElementsByTagName(sTag);
	for(i = 0; i < aElements.length; i++){
		var oElem = aElements.item(i);
		if(oElem.id == sElementName || oElem.name == sElementName )
			return oElem;
	} 
	
	return null;
}


function ValidateAccount(sResultXML){
	var errString = '';
	var objResDom;
	try{
		objResDom = LoadXMLDOM(sResultXML);
	} catch(e){
		// invalid xml or failed to create DOM xml parser
		return [false, e];
	}
	
    var objResTree = objResDom;
	var objStatus =  objResTree.getElementsByTagName('Status').item(0);
	var iStatusCode = objStatus.firstChild.nodeValue;

	if ( iStatusCode > 0){
		var objErrString = objResTree.getElementsByTagName('Error').item(0);
		errString = (objErrString && objErrString.firstChild)?objErrString.firstChild.nodeValue:'Uknown Error';
		return [false, errString];
	}
	else
		return [true, ''];
}


function HandleListAccounts(sResultXML){
 	var errString = '';
	var objResDom;
	try{
		objResDom = LoadXMLDOM(sResultXML);
	    var objResTree = objResDom;
		var objStatus =  objResTree.getElementsByTagName('Status').item(0);
		var iStatusCode = objStatus.firstChild.nodeValue;
		if (iStatusCode >0){
			var objErrString = objResTree.getElementsByTagName('Error').item(0);
			errString = (objErrString && objErrString.firstChild)?objErrString.firstChild.nodeValue:'Uknown Error';
			return [null, errString];
		}
		
		return [new ListAccountsResult(objResTree), ''];
	} catch(e){
		// invalid xml or failed to create DOM xml parser
		return [null, e];
	}

}


function ListAccountsResult(objResTree){
	var objStatus =  objResTree.getElementsByTagName('Status').item(0);
	this.iStatusCode = (objStatus && objStatus.firstChild)?objStatus.firstChild.nodeValue:'';
	
	var objError = objResTree.getElementsByTagName('Error').item(0);
	this.ErrorString = (objError && objError.firstChild)?objError.firstChild.nodeValue:'';
	
	var objAccounts =  objResTree.getElementsByTagName('AccountsData').item(0);

	//Filter out the non-'SP' scenes
	var ItemNode;
	var DeleteItemNode;
	ItemNode = objAccounts.firstChild;
	while (ItemNode){
		
		if ( ItemNode.getElementsByTagName('Edition').item(0).firstChild == null ){
			DeleteItemNode = ItemNode;
			ItemNode = ItemNode.nextSibling;
			objAccounts.removeChild(DeleteItemNode);
			continue;
		}else if (ItemNode.getElementsByTagName('Edition').item(0).firstChild.nodeValue != 'SP'){
			DeleteItemNode = ItemNode;
			ItemNode = ItemNode.nextSibling;
			objAccounts.removeChild(DeleteItemNode);
			continue;
		}
		ItemNode = ItemNode.nextSibling;
	}
	
	this.objAccountItemList = objAccounts?objAccounts.getElementsByTagName('ITEM'):null;
	this.aAccounts = new Array();
	if(this.objAccountItemList ){
		for(i = 0; i < this.objAccountItemList.length; i++){
			var objAccountItem = this.objAccountItemList.item(i);
			this.aAccounts[i] = new AccountInfo(objAccountItem);
		}
	}
}


function AccountInfo(objAccountItem){
	this.Active = 1;
	
	var objID = objAccountItem.getElementsByTagName('Id').item(0);
	this.iAccountID = (objID && objID.firstChild)?objID.firstChild.nodeValue:'';
	
	var objEdition = objAccountItem.getElementsByTagName('Edition').item(0);
	this.Edition = (objEdition && objEdition.firstChild)?objEdition.firstChild.nodeValue:'';

	var objNumSS = objAccountItem.getElementsByTagName('NumSS').item(0);
	this.NumSS = (objNumSS && objNumSS.firstChild)?objNumSS.firstChild.nodeValue:0;

	var objPackage = objAccountItem.getElementsByTagName('Package').item(0);
	this.Package = (objPackage && objPackage.firstChild)?objPackage.firstChild.nodeValue:'';
	
	var objExpiration = objAccountItem.getElementsByTagName('Expiration').item(0);
	this.Expiration = (objExpiration && objExpiration.firstChild)?objExpiration.firstChild.nodeValue:0;

	//this.sHeadCodePrefix = FillInID(sHeadCodePrefixTempl, this.iAccountID);
	//this.sHeadCodeSuffix = FillInID(sHeadCodeSuffixTempl, this.iAccountID);

}


function PopulateAccountSelect(objAccountItemList, formAccountSelect){
	
	formAccountSelect.options.length=0;
	var iExistingAccountOptionIndex = -1;
	
	for(i = 0; i<objAccountItemList.length; i++){
		var sAccountName = 'Account '+(i+1)+': ';
		var objAccountItem = objAccountItemList.item(i);
		var iAccountID = objAccountItem.getElementsByTagName('Id').item(0).firstChild.nodeValue;
		var objAccountName = objAccountItem.getElementsByTagName('Name').item(0);
		if (objAccountName.firstChild)
			sAccountName += objAccountName.firstChild.nodeValue;
			
		formAccountSelect.options[i] = new Option(sAccountName, iAccountID);
	}
	
	if(i>0)
		formAccountSelect.options[0].selected  = true;
	
}

// =========== Scenes Functions =================

function HandleSceneResponseXML(httpReq){
	if (httpReq.readyState == 4 ){
		if (httpReq.status != 200 ){
			alert("HTTP "+httpReq.status);
		}
		else{
			//alert(httpReq.responseText);
			var result = HandleScenesResult(httpReq.responseText);
			gObjSceneListResult = result[0];
			if (gObjSceneListResult != null){
				UpdateSceneSelect(gObjSceneListResult.objSceneItemList);
				//updateStatus("Login Success.");
			}else{
				alert('Error: ' + result[1]);
				//updateStatus("Fail to get account information. "+ result[1]);
			}
		}
	}
	else{
		//alert("Status: "+httpReq.readyState);
		//updateStatus(httpReq.readyState);
	}

}

function HandleScenesResult(sResultXML){
 	var errString = '';
	var objResDom;
	
	try{
		objResDom = LoadXMLDOM(sResultXML);
		var objResTree = objResDom;
		var objStatus =  objResTree.getElementsByTagName('Status').item(0);
		var iStatusCode = objStatus.firstChild.nodeValue;
		if (iStatusCode >0){
			var objErrString = objResTree.getElementsByTagName('Error').item(0);
			errString = (objErrString && objErrString.firstChild)?objErrString.firstChild.nodeValue:'Uknown Error';
			return [null, errString];
		}
		
		return [new SceneListResult(objResTree), ''];
	}catch(e){
		//invalid xml or failed to craete DOM xml parser
		return [null, e];
	}

}

function SceneListResult(objResTree){
	var objStatus =  objResTree.getElementsByTagName('Status').item(0);
	this.iStatusCode = objStatus.firstChild.nodeValue;
	var objBaseURL =  objResTree.getElementsByTagName('BaseURL').item(0);
	if (objBaseURL && objBaseURL.firstChild)
		this.sBaseURL = objBaseURL.firstChild.nodeValue;
	else
		this.sBaseURL = '';
	var objScenes =  objResTree.getElementsByTagName('Scenes').item(0);
	
	// =========================================================================
	//Filter out the empty scenes
	// Might not need if server side takes care of filtering in the future
	var ItemNode;
	var DeleteItemNode;
	ItemNode = objScenes.firstChild;
	while (ItemNode){
		if ( ItemNode.getElementsByTagName('CharID').item(0).firstChild == null){
			DeleteItemNode = ItemNode;
			ItemNode = ItemNode.nextSibling;
			objScenes.removeChild(DeleteItemNode);
			continue;
		}else if ( ItemNode.getElementsByTagName('CharID').item(0).firstChild.nodeValue == '-1'){
			DeleteItemNode = ItemNode;
			ItemNode = ItemNode.nextSibling;
			objScenes.removeChild(DeleteItemNode);
			continue;
		}
		ItemNode = ItemNode.nextSibling;
	}
	// ===========================================================================
	
	this.objSceneItemList = objScenes.getElementsByTagName('ITEM');
	this.aScenes = new Array();
	for(i = 0; i < this.objSceneItemList.length; i++){
			var objSceneItem = this.objSceneItemList.item(i);
			this.aScenes[i] = new SceneInfo(objSceneItem);
	}
}


function SceneInfo(objSceneItem){

	var objShowID = objSceneItem.getElementsByTagName('ShowID').item(0);
	this.iShowID = (objShowID && objShowID.firstChild)?objShowID.firstChild.nodeValue:'';
	
	// Scene Id could be empty since some show might have no scene
	var objId = objSceneItem.getElementsByTagName('Id').item(0);
	this.iSceneID = (objId && objId.firstChild)?objId.firstChild.nodeValue:'';
	
	// Name could be empty
	var objSceneName = objSceneItem.getElementsByTagName('Name').item(0);
	this.sSceneName = (objSceneName && objSceneName.firstChild)?objSceneName.firstChild.nodeValue:'';
	
	// Ind could be empty since some show might have no scene	
	var objSceneIndex = objSceneItem.getElementsByTagName('Ind').item(0);
	this.iSceneIndex = (objSceneIndex && objSceneIndex.firstChild)?objSceneIndex.firstChild.nodeValue:-1;
	
	var objSceneThumb = objSceneItem.getElementsByTagName('Thumb').item(0);
	this.sThumbnail = (objSceneThumb && objSceneThumb.firstChild)?objSceneThumb.firstChild.nodeValue:'';
	
	//this.sBodyCodePrefix = FillInID(sBodyCodePrefixTempl, this.iSceneID, gCurrAccount.iAccountID);
	//this.sBodyCodeSuffix = FillInID(sBodyCodeSuffixTempl, this.iSceneID, gCurrAccount.iAccountID);
	gCurrAccountID = document.getElementById('hiddenAccountID').value;
	
	this.sBodyCodePrefix = FillInID(sBodyCodePrefixTempl, this.iSceneID, gCurrAccountID);
	
	this.sBodyCodeSuffix = FillInID(sBodyCodeSuffixTempl, this.iSceneID, gCurrAccountID);
	this.sSWFURL = '';
	this.sHeadCode = '';
	this.sBodyCode = '';
	this.sHeadHtmlCode = '';
	this.sBodyHtmlCode = '';
	//just comment out for now
	//Get Flash code
	this.GetFlashURL = function(){
		GetEmbedCodes(this.iSceneID, 'FLASH');
		if (gCurrSceneEmbedCode)
			this.sSWFURL = gCurrSceneEmbedCode.sBodyCode;
	};

	this.GetCodeSnippets = function(){
		GetEmbedCodes(this.iSceneID, 'FULL');
		this.sHeadCode = "";
		if (gCurrSceneEmbedCode)
			this.sBodyCode = gCurrSceneEmbedCode.sBodyCode;
	};

	//Get HTML(non-javascript) code
	this.GetHtmlCodeSnippets = function(){
		GetEmbedCodes(this.iSceneID, 'HTML');
		this.sHeadHtmlCode = "";
		if (gCurrSceneEmbedCode)
			this.sBodyHtmlCode = gCurrSceneEmbedCode.sBodyCode;
	};
	
	this.GetPreviewCodeSnippets = function(){
		GetPreviewEmbedCodes(this.iSceneID, 'HTML', 'AddRep');
	}

}


function PopulateSceneSelect(objSceneItemList, formSceneSelect){
	
	formSceneSelect.options.length=0;
	var iExistingSceneOptionIndex = -1;
	var offset = 0;
	
	if (objSceneItemList.length<1){
		formSceneSelect.options[0] = new Option("No available scene", -1);
		formSceneSelect.options[0].selected  = true;
		//PreviewScene(formSelect, offset);
		return;
	}
	
	
	for(i = 0; i < objSceneItemList.length; i++){
		var objSceneItem = objSceneItemList.item(i);
		var sSceneName = 'Scene ' + (i+1) + ': ';

		var iSceneID = objSceneItem.getElementsByTagName('Id').item(0).firstChild.nodeValue;
		
		// Name could be empty
		var objSceneName = objSceneItem.getElementsByTagName('Name').item(0);
		if(objSceneName && objSceneName.firstChild)
			sSceneName += objSceneName.firstChild.nodeValue;
		// Ind should be there	
		//alert(iSceneID+"  "+iShowID+"  "+sSceneName);
		
		var iSceneIndex = objSceneItem.getElementsByTagName('Ind').item(0).firstChild.nodeValue;
		formSceneSelect.options[i+offset] = new Option(sSceneName , iSceneID);
	
		/*if( gExistingScene && gExistingScene.iSceneID==iSceneID ){
			iExistingSceneOptionIndex = i+offset;
		}*/
	}
	
	//default selection is always the first item in the drop down menu
	formSceneSelect.options[0].selected  = true;
	PreviewScene(formSceneSelect, 0);
	
}

function PreviewDemoScene(objSelect){

	if (objSelect.selectedIndex < 0)
   		return;
	var objDivEmbedCode = document.getElementById('PreviewDemoEmbedScene');
	var sceneValue = objSelect.options[objSelect.selectedIndex].value;
	var embedcode = "";
	//alert(sceneValue);
	//sceneValue = 2;
	switch(sceneValue)
	{
		case '0':
			embedcode = "<OBJECT id='VHSS' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0' WIDTH=200 HEIGHT=150><PARAM NAME='movie' VALUE='http://vhost.oddcast.com/vhsssecure.php?doc=http%3A%2F%2Fvhost.oddcast.com%2Fgetshow.php%3Facc%3D38177%26ss%3D712126%26sl%3D0%26embedid%3Daebc4eec16537a3a5436ca70caa49c9d&edit=0&acc=38177&loading=1&bgcolor=0xFFFFFFF&firstslide=1'><PARAM NAME=quality VALUE=high><PARAM NAME=scale VALUE=noborder><PARAM NAME=bgcolor VALUE=#FFFFFFF><EMBED src='http://vhost.oddcast.com/vhsssecure.php?doc=http%3A%2F%2Fvhost.oddcast.com%2Fgetshow.php%3Facc%3D38177%26ss%3D712126%26sl%3D0%26embedid%3Daebc4eec16537a3a5436ca70caa49c9d&edit=0&acc=38177&loading=1&bgcolor=0xFFFFFFF&firstslide=1' swLiveConnect=true NAME='VHSS' quality=high scale=noborder bgcolor=#FFFFFFF WIDTH=200 HEIGHT=150 TYPE='application/x-shockwave-flash' PLUGINSPAGE='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash'></EMBED></OBJECT>";
			break;
		case '1':
			embedcode = "<OBJECT id='VHSS' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0' WIDTH=200 HEIGHT=150><PARAM NAME='movie' VALUE='http://vhost.oddcast.com/vhsssecure.php?doc=http%3A%2F%2Fvhost.oddcast.com%2Fgetshow.php%3Facc%3D38177%26ss%3D712127%26sl%3D0%26embedid%3D4d6cc40c1c3cccae62dd56c68c99d5e6&edit=0&acc=38177&loading=1&bgcolor=0xFFFFFFF&firstslide=1'><PARAM NAME=quality VALUE=high><PARAM NAME=scale VALUE=noborder><PARAM NAME=bgcolor VALUE=#FFFFFFF><EMBED src='http://vhost.oddcast.com/vhsssecure.php?doc=http%3A%2F%2Fvhost.oddcast.com%2Fgetshow.php%3Facc%3D38177%26ss%3D712127%26sl%3D0%26embedid%3D4d6cc40c1c3cccae62dd56c68c99d5e6&edit=0&acc=38177&loading=1&bgcolor=0xFFFFFFF&firstslide=1' swLiveConnect=true NAME='VHSS' quality=high scale=noborder bgcolor=#FFFFFFF WIDTH=200 HEIGHT=150 TYPE='application/x-shockwave-flash' PLUGINSPAGE='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash'></EMBED></OBJECT>";
			break;
		case '2':
			embedcode = "<OBJECT id='VHSS' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0' WIDTH=200 HEIGHT=150><PARAM NAME='movie' VALUE='http://vhost.oddcast.com/vhsssecure.php?doc=http%3A%2F%2Fvhost.oddcast.com%2Fgetshow.php%3Facc%3D38177%26ss%3D712128%26sl%3D0%26embedid%3Dc61cb244f823f817ce057860e364ba8c&edit=0&acc=38177&loading=1&bgcolor=0xFFFFFFF&firstslide=1'><PARAM NAME=quality VALUE=high><PARAM NAME=scale VALUE=noborder><PARAM NAME=bgcolor VALUE=#FFFFFFF><EMBED src='http://vhost.oddcast.com/vhsssecure.php?doc=http%3A%2F%2Fvhost.oddcast.com%2Fgetshow.php%3Facc%3D38177%26ss%3D712128%26sl%3D0%26embedid%3Dc61cb244f823f817ce057860e364ba8c&edit=0&acc=38177&loading=1&bgcolor=0xFFFFFFF&firstslide=1' swLiveConnect=true NAME='VHSS' quality=high scale=noborder bgcolor=#FFFFFFF WIDTH=200 HEIGHT=150 TYPE='application/x-shockwave-flash' PLUGINSPAGE='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash'></EMBED></OBJECT>";
			break;
		
	}

	objDivEmbedCode.innerHTML = embedcode;
	
}


function AddDemoCodeToPost(){
	
	var objSelect = document.getElementById('demosceneSelect');
	var sceneValue = objSelect.options[objSelect.selectedIndex].value;
	var embedCode = '';
	switch(sceneValue)
	{
		case '0':
			embedCode = "http://vhost.oddcast.com/vhsssecure.php?doc=http%3A%2F%2Fvhost.oddcast.com%2Fgetshow.php%3Facc%3D38177%26ss%3D712126%26sl%3D0%26embedid%3Daebc4eec16537a3a5436ca70caa49c9d&edit=0&acc=38177&loading=1&bgcolor=0xFFFFFFF&firstslide=1";
			break;
		case '1':
			embedCode = "http://vhost.oddcast.com/vhsssecure.php?doc=http%3A%2F%2Fvhost.oddcast.com%2Fgetshow.php%3Facc%3D38177%26ss%3D712127%26sl%3D0%26embedid%3D4d6cc40c1c3cccae62dd56c68c99d5e6&edit=0&acc=38177&loading=1&bgcolor=0xFFFFFFF&firstslide=1";
			break;
		case '2':
			embedCode = "http://vhost.oddcast.com/vhsssecure.php?doc=http%3A%2F%2Fvhost.oddcast.com%2Fgetshow.php%3Facc%3D38177%26ss%3D712128%26sl%3D0%26embedid%3Dc61cb244f823f817ce057860e364ba8c&edit=0&acc=38177&loading=1&bgcolor=0xFFFFFFF&firstslide=1";
			break;
		
	}
	embedCode = embedCode.replace('vhost.oddcast.com/vhsssecure.php?doc=', '_SITEPAL_');			   
	sCompleteCode = "[sitepal_flashembed movie=\""+ embedCode + "\" height=\""+ "150" + "\" width=\""+ "200"+"\" /]";
	//switch to text mode before embeding code.

	var bSwitched = false;
	if (document.getElementById('edButtonHTML')) {
		
		var classValue;
		if (document.getElementById('edButtonHTML').getAttribute('class') == null)//IE
			classValue = document.getElementById('edButtonHTML').getAttribute('className');
		else //FireFox
			classValue = document.getElementById('edButtonHTML').getAttribute('class');
			
		if (classValue == 'edButtonBack' || classValue != 'active'){
		
			alert(gStrVisualModeErrorMsg);
		
		/*
		try{
		 	switchEditors('content');
			bSwitched = true;
		}
		catch(err){
		  	switchEditors.go('content','html');
		  	bSwitched = true;		  	
		}		
		*/
						
	}
		
	}
	
	var objTextArea = document.getElementById('content');
	if (objTextArea){
		InsertContentIntoPostAreaExt(objTextArea, sCompleteCode);
		document.getElementById('btn_AddRepDemoScene').value = "Replace the Scene";
	}
		
	if (bSwitched){
	try{
		switchEditors('content');
		bSwitched = false;
	}
	catch(err){
	  	switchEditors.go('content','tinymce');
	  	bSwitched = false;
	}		
	}

}


function PreviewScene(objSelect, offset){
	
	if (objSelect.selectedIndex < 0){
		gSelectedScene = null;	
   		return;
	}

	if (objSelect.selectedIndex == 0 ){
		if (objSelect.options[objSelect.selectedIndex].value == 0){ 
			// When 'ENTIRE SHOW' is selected
			//gSelectedScene = gEntireShowItem;
		}
		else if (objSelect.options[objSelect.selectedIndex].value == -1){ 
			// No Available Scene.
			// Each sitepal account has default non-empty scenes. So there shouldn't be "no available scene" show up in sitepal account
			gSelectedScene = null;
		}else 
			gSelectedScene = gObjSceneListResult.aScenes[objSelect.selectedIndex-offset];
		
	}
	else{ 
	  gSelectedScene = gObjSceneListResult.aScenes[objSelect.selectedIndex-offset];
	}
	
	//alert("gSelectedScene.iSceneID: "+gSelectedScene.iSceneID);
	onPreviewSceneSelected(objSelect, objSelect.selectedIndex);
  
}

function onPreviewSceneSelected(objSelect, iSceneIndex){
   	UpdatePreviewLauncher(objSelect, iSceneIndex);
}


function UpdatePreviewLauncher(objSelect, iSceneIndex){
	var objScene;
	objScene = gObjSceneListResult.aScenes[iSceneIndex];
	objScene.GetPreviewCodeSnippets();
}


function UpdateSceneSelect(sceneListResult){
	var objSceneSelect = getElementByName('select', 'sceneSelect');
	if (objSceneSelect)
		PopulateSceneSelect(sceneListResult, objSceneSelect);
}
	



// ========== GetEmbedCode Functions ================

function HandlePreviewEmbedCodeResponseXML(httpReq, sMode){
	if (httpReq.readyState == 4 ){
		if (httpReq.status != 200 )
			alert("HTTP "+httpReq.status);
		else{
			//alert(httpReq.responseText);
			var result = HandleEmbedSceneResult(httpReq.responseText);
			gCurrSceneEmbedCode = result[0];
			if (gCurrSceneEmbedCode != null){
				//update preview area
				var objDivEmbedCode;
				if (sMode == 'AddRep')
					objDivEmbedCode = document.getElementById('PreviewEmbedScene');
				else if (sMode == 'Remove')
					objDivEmbedCode = document.getElementById('PreviewExistedScene');
				
				if (objDivEmbedCode)
					objDivEmbedCode.innerHTML = gCurrSceneEmbedCode.sBodyCode;
				
			}else{
				alert('Error: ' + result[1]);
				//updateStatus("Fail to get embed code information. "+ result[1]);
			}
		}
	}else{
		//alert("Status: "+httpReq.readyState);
		//updateStatus(httpReq.readyState);
	}
}


function HandleDuplicateSceneResponseXML(httpReq, iSceneID, sColor, Width, Height, sMethod, sAPI, sSecureProtocol){
	
	if (httpReq.readyState == 4 ){
		if (httpReq.status != 200 ){
			alert("HTTP "+httpReq.status);
		}
		else{
			var result = HandleDuplicateSceneID(httpReq.responseText);
			if (result[0] != null){
				// Set Scene Attribute
				var iPlayOnLoad, iPlayOnClick;
				if (document.getElementById('PlayOnLoad').checked)
					iPlayOnLoad = 1;
				else
					iPlayOnLoad = 2;
				
				if (document.getElementById('PlayOnClick').checked)
					iPlayOnClick = 1;
				else
					iPlayOnClick = 2;
	
				SetSceneAttr(result[0], iPlayOnLoad, iPlayOnClick);

				GetEmbedCodes(result[0], sColor, Width, Height, sMethod, sAPI, sSecureProtocol)	;
			}else{
				alert('Error: '+result[1]);
			}
		}
	}
	else{
		//alert("Status: "+httpReq.readyState);
		//updateStatus(httpReq.readyState);
	}

}


function HandleDuplicateSceneID(sResultXML){
	try{
		objResDom = LoadXMLDOM(sResultXML);
	    var objResTree = objResDom;
		var objStatus =  objResTree.getElementsByTagName('Status').item(0);
		if (!objStatus)
			return [null, 'Unknown Error. objStatus Node is empty'];
			
		var iStatusCode = objStatus.firstChild.nodeValue;
		//alert(iStatusCode);
		if (iStatusCode !=0){
			var objErrString = objResTree.getElementsByTagName('Error').item(0);
			errString = (objErrString && objErrString.firstChild)?objErrString.firstChild.nodeValue:'Uknown Error';
			return [null, errString];
		}else{
			var objNewSceneId = objResTree.getElementsByTagName('newSceneId').item(0);
			var iNewSceneId = (objNewSceneId && objNewSceneId.firstChild.nodeValue)?objNewSceneId.firstChild.nodeValue:'';
			return [iNewSceneId, ''];
		}
		
	}catch(e){
	// invalid xml or fail to create DOM xml parser
		return [null, e];
	}

}


function HandleEmbedCodeResponseXML(httpReq, sSecureProtocol, iWidth, iHeight, SceneID, AccountID, sColor){
	if (httpReq.readyState == 4 ){
		if (httpReq.status != 200 )
			alert("HTTP "+httpReq.status);
		else{
			//alert(httpReq.responseText);
			//document.getElementById('tempshow').innerHTML = httpReq.responseText;
			var result = HandleEmbedSceneResult(httpReq.responseText);
			var embedCode;
			var completeEmbedCode;
			gCurrSceneEmbedCode = result[0];
			if (gCurrSceneEmbedCode != null){
				if (sSecureProtocol == 'Y')
					embedCode = AddS(gCurrSceneEmbedCode.sBodyCode);
				else
					embedCode = gCurrSceneEmbedCode.sBodyCode;
				//sCompleteCode = gSelectedScene.sBodyCodePrefix +"[sitepal_flashembed movie=\""+ embedCode + "\" height=\""+ iHeight + "\" width=\""+ iWidth+"\" /]"+ gSelectedScene.sBodyCodeSuffix;
				// Detecting '[sitepal_flashembed movie="' string to detect scene instead of embeding html comment
				//On Wordpress.com, users are not allowed to embed flash content which are not provided from Wordpress.com's parteners. 
				//So we need to block user from taking the advantage		
				//document.getElementById('tempDiv').innerHTML = embedCode;
				embedCode = embedCode.replace('vhost.oddcast.com/vhsssecure.php?doc=', '_SITEPAL_');			   
				sCompleteCode = "[sitepal_flashembed movie=\""+ embedCode + "\" height=\""+ iHeight + "\" width=\""+ iWidth+"\" /]";
				//sCompleteCode = "[sitepal_flashembed movie=\""+ "\" height=\""+ iHeight + "\" width=\""+ iWidth+"\" accid=\"" + AccountID + "\" sceneid=\""+ SceneID + "\" bgcolor=\"" +sColor + "\" /]";
				//switch to text mode before embeding code.

				var bSwitched = false;
				if (document.getElementById('edButtonHTML')) {
					var classValue;
					if (document.getElementById('edButtonHTML').getAttribute('class') == null)//IE
						classValue = document.getElementById('edButtonHTML').getAttribute('className');
					else //FireFox
						classValue = document.getElementById('edButtonHTML').getAttribute('class');
						
					if (classValue == 'edButtonBack' || classValue != 'active'){
						alert(gStrVisualModeErrorMsg);
						/*
						try{
						 	switchEditors('content');
							bSwitched = true;
						}
						catch(err){
						  	switchEditors.go('content','html');
						  	bSwitched = true;
						}	
						*/					
					}					
				}
				
				/*if (document.getElementById('edButtonHTML') && document.getElementById('edButtonHTML').getAttribute('className') == 'edButtonBack'){
					
					switchEditors('content');
					bSwitched = true;
				}*/
				
				var objTextArea = document.getElementById('content');
				if (objTextArea)
					//InsertContentIntoPostArea(objTextArea, sCompleteCode);
					InsertContentIntoPostAreaExt(objTextArea, sCompleteCode);
					
				if (bSwitched){
					try{
						switchEditors('content');
						bSwitched = false;
					}
					catch(err){
					  	switchEditors.go('content','tinymce');
					  	bSwitched = false;
					}		
				}
			}else{
				alert('Error ' + result[1]);
				//updateStatus("Fail to get embed code information. "+ result[1]);
			}
		}
	}else{
		//alert("Status: "+httpReq.readyState);
		//updateStatus(httpReq.readyState);
	}
}

function HandleEmbedSceneResult(sResultXML){
   	try{
		objResDom = LoadXMLDOM(sResultXML);
	    var objResTree = objResDom;
		return [new EmbedSceneResults(objResTree), ''];
	}catch(e){
	// invalid xml or fail to create DOM xml parser
		return [null, e];
	}
}

function EmbedSceneResults(objResultTree){
	
	this.ResultNode = objResultTree;
	
	var objStatus =  objResultTree.getElementsByTagName('Status').item(0);
	this.iStatusCode = (objStatus && objStatus.firstChild)?objStatus.firstChild.nodeValue:'';
	
	var objError = objResultTree.getElementsByTagName('Error').item(0);
	this.ErrorString = (objError && objError.firstChild)?objError.firstChild.nodeValue:'';
	
	this.sHeadCode = '';
	this.sBodyCode = '';
	var objCode = objResultTree.getElementsByTagName('Code').item(0);
	this.sBodyCode = (objCode && objCode.firstChild)?objCode.firstChild.nodeValue:'';
		
}


function HandleSetSceneAttrResponseXML(httpReq){
	if (httpReq.readyState == 4 ){
		if (httpReq.status != 200 ){
			alert("HTTP "+httpReq.status);
		}
		else{
			//alert(httpReq.responseText);
		}
	}
	else{
		//alert("Status: "+httpReq.readyState);
		//updateStatus(httpReq.readyState);
	}

}


// ====== Utility Functions ============
function FillInID(sDest, sSourceSceneID, sSourceAccountID){

	sDest = sDest.replace('%s', sSourceSceneID); 
	sDest = sDest.replace('%s', sSourceAccountID);
	return sDest;
}

function getIDFromString(sText){
	
	gExsitedSceneBodyCodePrefix = sText;
	
	var ID = new Array();
	var i = 0; var j = 0; var k = 0; var m = 0;
	// skip over leading non-digits
	for(i = 0; i < sText.length; i++){
		if(!isNaN(parseInt(sText.charAt(i))))
			break;
	}
	// count valid digits
	for(j = i; j < sText.length; j++){
		if(isNaN(parseInt(sText.charAt(j))))
			break;
	}
	
	ID[0]= parseInt(sText.substring(i,j)); // Scene ID
	
	// skip over 'ACC' to read the AccountID
	for(k=j; k<sText.length; k++){
		if(!isNaN(parseInt(sText.charAt(k))))
			break;
	}
	
	for(m=k; m<sText.length; m++){
		if(isNaN(parseInt(sText.charAt(m))))
			break;
	}
	
	ID[1] = parseInt(sText.substring(k,m)); // Account ID
	
	gExistedSceneBodyCodeSuffix =  sText.substring(0,m+4) + "End-->";
	
	return ID;

}

function FindExistingSceneInPostExt(myField){
	var ind = myField.value.indexOf('[sitepal_flashembed');
	if (ind == -1)
		return null;
	else{
		var end = myField.value.indexOf('/]', ind);
		//var ID = getIDFromString(myField.value.substring(ind, end+4));
		return [ind, end+2];
	}

}

function FindExistingSceneInPost(myField){
	var ind = myField.value.indexOf(sBodyCodePrefix);
	if (ind == -1)
		return null;
	else{
		var end = myField.value.indexOf('BodyStart', ind);
		var ID = getIDFromString(myField.value.substring(ind, end+4));
		return ID;
	}

}

function RemoveSceneExt(myField){
	
	var iExistingSceneStart = myField.value.indexOf('[sitepal_flashembed');
	if (iExistingSceneStart < 0){
		document.getElementById('RemovePageStatus').innerHTML = 'No existed scene in your post.' ;
		return;
	}
	var answer = confirm("Do you want to delete this scene from your post?");
	if (!answer)
	  return;
	var iExistingSceneEnd = myField.value.indexOf('/]', iExistingSceneStart)+2;
	myField.value = myField.value.substring(0, iExistingSceneStart)+ " " + myField.value.substring(iExistingSceneEnd, myField.value.length);
	document.getElementById('PreviewExistedScene').innerHTML = '';
	document.getElementById('RemovePageStatus').innerHTML = 'The current scene has been removed.' ;
	
}


function RemoveScene(myField){
	
	var iExistingSceneStart = myField.value.indexOf(gExsitedSceneBodyCodePrefix);
	if (iExistingSceneStart < 0){
		document.getElementById('RemovePageStatus').innerHTML = 'No existed scene in your post.' ;
		return;
	}
	var answer = confirm("Do you want to delete this scene from your post?");
	if (!answer)
	  return;
	var iExistingSceneEnd = myField.value.indexOf(gExistedSceneBodyCodeSuffix, iExistingSceneStart)+(gExistedSceneBodyCodeSuffix.length);
	myField.value = myField.value.substring(0, iExistingSceneStart)+ '' + myField.value.substring(iExistingSceneEnd, myField.value.length);
	document.getElementById('PreviewExistedScene').innerHTML = '';
	document.getElementById('RemovePageStatus').innerHTML = 'The existed scene has been removed.' ;
	
}

function InsertContentIntoPostAreaExt_VisualEditor(sText) {
/*	if(window.tinyMCE) {
		window.tinyMCE.getInstanceById('content').selectiton();
		//window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, sText);
		//window.tinyMCE.execInstanceCommand('content',"selectall", false, [1,20]);
		//window.tinyMCE.execInstanceCommand('content',"mceSelectNode", false, 'tr' );
		alert(window.tinyMCE.getInstanceById('content').selection.getSelectedHTML());
		//window.tinyMCE.execInstanceCommand('content', 'mceReplaceContent', '####');
		tinyMCE.execCommand("mceCleanup");
	} else {
		alert('edInsertContent');
		edInsertContent(edCanvas, sText);
	}
*/	
}

function InsertContentIntoPostAreaExt(myField, myValue) {
	var objTextArea = document.getElementById('content');
	if (!objTextArea)
		return;

	gExistedScenePos = FindExistingSceneInPostExt(objTextArea);

	if (gExistedScenePos != null){	
		var iExistingSceneStart = myField.value.indexOf('[sitepal_flashembed');
		if (iExistingSceneStart < 0)
			return;
		var iExistingSceneEnd = myField.value.indexOf('/]', iExistingSceneStart) + 2;
		myField.value = myField.value.substring(0, iExistingSceneStart)+ myValue + myField.value.substring(iExistingSceneEnd, myField.value.length);

		return;
	}
	
	//IE support
	if (document.selection) {
		try{
			myField.focus();
		}catch(e){
			alert('System Error: '+e.message);
			return;
		}
		sel = document.selection.createRange();
		sel.text = myValue;
		myField.focus();
	}	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		myField.focus();
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
		myField.selectionStart = startPos + myValue.length;
		myField.selectionEnd = startPos + myValue.length;
	} else {
		myField.value += myValue;
		myField.focus();
	}

	document.getElementById('AddRep_link').innerHTML = "Replace The Current Scene";
	document.getElementById('btn_AddRepScene').value = "Replace the Scene";

}


function InsertContentIntoPostArea(myField, myValue) {

	//if (gExistedSceneID != null){
	  if (gExistedScenePos != null){
		var iExistingSceneStart = myField.value.indexOf(gExsitedSceneBodyCodePrefix);
		if (iExistingSceneStart < 0)
			return;
		var iExistingSceneEnd = myField.value.indexOf(gExistedSceneBodyCodeSuffix, iExistingSceneStart)+(gExistedSceneBodyCodeSuffix.length);
		myField.value = myField.value.substring(0, iExistingSceneStart)+ myValue + myField.value.substring(iExistingSceneEnd, myField.value.length);
		return;
	}

	//IE support
	if (document.selection) {
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
		myField.focus();
	}	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		myField.focus();
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
		
		myField.selectionStart = startPos + myValue.length;
		myField.selectionEnd = startPos + myValue.length;
	} else {
		myField.value += myValue;
		myField.focus();
	}
	
}

function AddS(Oldcode){
	return Oldcode.replace(/http:/g, 'https:');
}


	function LoadTabPage(sPage){
		
		var objTextArea = document.getElementById('content');
		if (objTextArea){
			//gExistedSceneID = FindExistingSceneInPost(objTextArea);
			gExistedScenePos = FindExistingSceneInPostExt(objTextArea);
		}
			
		if (sPage == 'AddRep'){
			CheckUpdate();
			//if ( gExistedSceneID != null ){
			if ( gExistedScenePos != null ){
				document.getElementById('AddRep_link').innerHTML = "Replace The Current Scene";
				document.getElementById('btn_AddRepScene').value = "Replace the Scene";
			}
			else{
				document.getElementById('AddRep_link').innerHTML = "Add A New Scene";
				document.getElementById('btn_AddRepScene').value = "Add To Post";
			}
			
			if (document.getElementById('AddRep_link').getAttribute('class') != null){//FireFox
				document.getElementById('AddRep_link').setAttribute('class', 'current');
				document.getElementById('Remove_link').setAttribute('class', '');
			}else{// IE
				document.getElementById('AddRep_link').setAttribute('className', 'current');
				document.getElementById('Remove_link').setAttribute('className', '');
			}
			
			document.getElementById('AddRep_Layer').style.display = 'block';
			document.getElementById('Remove_Layer').style.display = 'none';
			CheckUpdate();
			getScenes();
		}
		else if (sPage == 'Remove'){
			//if (gExistedSceneID == null){
			if (gExistedScenePos == null ){
				alert("Ooops, there is no scene in your post.");
				return;
			}
			
			if (document.getElementById('Remove_link').getAttribute('class') != null ){ //FireFox
				document.getElementById('Remove_link').setAttribute('class', 'current');
				document.getElementById('AddRep_link').setAttribute('class', '');
			}else{
				document.getElementById('Remove_link').setAttribute('className', 'current');
				document.getElementById('AddRep_link').setAttribute('className', '');
			}
			
			document.getElementById('AddRep_Layer').style.display = 'none';
			document.getElementById('Remove_Layer').style.display = 'block';
			
			//if (gExistedSceneID != null)
			//DisplayExistedScene(gExistedSceneID);
			DisplayExistedScene();
		}
		else if (sPage == 'AddRepDemo'){
			
			document.getElementById('Login_link').innerHTML = 'Demo';
			document.getElementById('Login_Layer').style.display = 'none';
			document.getElementById('AddRepDemo_Layer').style.display = 'block';
			PreviewDemoScene(document.getElementById('demosceneSelect'));
			
			if ( gExistedScenePos != null )
				document.getElementById('btn_AddRepDemoScene').value = "Replace the Scene";
			else
				document.getElementById('btn_AddRepDemoScene').value = "Add To Post";

		}else if (sPage == 'LoginPage'){
			
			document.getElementById('Login_link').innerHTML = 'Sitepal Intro';
			document.getElementById('Login_Layer').style.display = 'block';
			document.getElementById('AddRepDemo_Layer').style.display = 'none';
		}

	}

</script>