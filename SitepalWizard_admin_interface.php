<div id="SitepalWizard_amin">
	<fieldset id="SitepalWizard_admin" class="dbx-box">
		<h3 class="dbx-handle">Sitepal Talking Avatar</h3>

<?php
	$pluginPath = '/wp-content/plugins/sitepal-talking-avatar/';
	$bLogin = false;	
	$ResultData = SitepalWizard_getDataFromDB();
	if ($ResultData == NULL){
		//Load Option Page 
		$bLogin = true;
		?>
		<ul id="submenu">
			<li id="LoginPageTitle"><a id="Login_link" href="<?php echo get_option('siteurl').$pluginPath.'SitepalWizard_admin_interface_Login.php';?>" onClick="return false;" class='current'>Sitepal Info</a></li>
		</ul>
		<div name="LayerContainer" id="LayerContainer" style="height:350">
			<div name="Login_Layer" id="Login_Layer" style="display:block" > <?php include_once("SitepalWizard_admin_interface_Login.php"); ?>        </div>
			<div name="AddRepDemo_Layer" id="AddRepDemo_Layer" style="display:none" > <?php include_once("SitepalWizard_admin_interface_AddRepDemo.php");	?>  </div>
		</div>
		<?php return;
	}else{
		//Load AddReplace Scene Page
		$bLogin = true;
		$CurrUsername = $ResultData[0]->username;
		$CurrPassword = $ResultData[0]->password;
		$CurrAccountID = $ResultData[0]->accountid;
		
	?>
	<!--<body onLoad="LoadTabPage('AddRep')">-->
		<ul id="submenu">
				<li id="AddRepPageTitle"><a id="AddRep_link" href="<?php echo get_option('siteurl').$pluginPath.'SitepalWizard_admin_interface_AddRep.php';?>" onClick="LoadTabPage('AddRep'); return false;" class='current'>Add or Replace A Scene</a></li>
				<li id="RemovePageTitile"><a id="Remove_link" href="<?php echo get_option('siteurl').$pluginPath.'SitepalWizard_admin_interface_Remove.php';?>" onClick="LoadTabPage('Remove'); return false;">Remove The Current Scene</a></li>
		</ul>
		
		<div name="LayerContainer" id="LayerContainer" style="height:390px">
			<div name="AddRep_Layer" id="AddRep_Layer" style="display:block" >
				<?php
					include_once("SitepalWizard_admin_interface_AddRep.php");
				?>
			</div>
			
			<div name="Remove_Layer" id="Remove_Layer" style="display:none">
				<?php
					include_once("SitepalWizard_admin_interface_Remove.php");
				?>
			</div>
		</div>
		<!--<div id="tempshow"></div>-->
		<input type="hidden" name="hiddenAccountID" id="hiddenAccountID" value="<?php echo $CurrAccountID; ?>" >

	<?php 
	}
	?>
	</fieldset>
</div>
<!--</body>-->
<script type="text/javascript" language="javascript">
	var gStrVisualModeErrorMsg = "Please switch to HTML mode, the plugin does not work properly in Visual mode.";
	var gBGColor = '000000';
	if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
		 document.write('<script language=\"VBScript\"\>\n');
		 document.write('On Error Resume Next\n');
		 document.write('Sub bgcolorPalette_FSCommand(ByVal command, ByVal args)\n');
		 document.write(' Call bgcolorPalette_DoFSCommand(command, args)\n');
		 document.write('End Sub\n');
		 document.write('</script\>\n');
	}

	window.onload= function(){LoadTabPage('AddRep');};
	
	function PopUp(sUrl){
		window.open(sUrl, 'Help', 'height=300, width=300');
		
	}

	
	function onWidthChanged(){
		var oTxtSceneWidth = document.getElementById('sceneWidth');
		var oTxtSceneHeight = document.getElementById('sceneHeight');
		var oKeppRatioChk = document.getElementById('sceneMaintainAspectRatio');
		
		if(oTxtSceneWidth.value>iScreenResX)
			oTxtSceneWidth.value=iScreenResX;
		
		if(oKeppRatioChk.checked){
			oTxtSceneHeight.value = Math.round(oTxtSceneWidth.value / fAspectRatio);
		}else{
			fAspectRatio = oTxtSceneWidth.value / oTxtSceneHeight.value;
		}
		
		SceneWidth = oTxtSceneWidth.value ;
		SceneHeight = oTxtSceneHeight.value;
	}

	function onHeightChanged(){
		var oTxtSceneWidth = document.getElementById('sceneWidth');
		var oTxtSceneHeight = document.getElementById('sceneHeight');
		var oKeppRatioChk = document.getElementById('sceneMaintainAspectRatio');
	
		if(oTxtSceneHeight.value>iScreenResY)
			oTxtSceneHeight.value=iScreenResY;
		
		if(oKeppRatioChk.checked){
			 oTxtSceneWidth.value = Math.round(oTxtSceneHeight.value * fAspectRatio);
		}else{
			fAspectRatio = oTxtSceneWidth.value / oTxtSceneHeight.value;
		}
		SceneWidth = oTxtSceneWidth.value ;
		SceneHeight = oTxtSceneHeight.value;
	
	}

	function bgcolorPalette_DoFSCommand(command, args){
		if (command == 'setColor')
			gBGColor = args;
	}
	

	function VHSS_DoFSCommand(command, args){
	
	}
	
	function AddCodeToPost(){
		//Make sure there is no embed code in the existed post
		//FindExistedScene();
		var objSecureProtocol = document.getElementById('SecureProtocol');
		var objPlayOnLoad = document.getElementById('PlayOnLoad');
		var oTxtSceneWidth = document.getElementById('sceneWidth');
		var oTxtSceneHeight = document.getElementById('sceneHeight');
		var sColor = gBGColor;
		var sSecureProtocol = 'Y';
		//var sMethod = 'HTML';
		var sMethod = 'FLASH';
		if (objSecureProtocol.checked)
			sSecureProtocol = 'Y';
		else
			sSecureProtocol = 'N';
		var sAPI = 'N'	;
			
		//InsertContentIntoPostAreaExt_VisualEditor("What the hell");
		//Getting duplicated scene ID first
		DuplicateNGetEmbedCodeScene(gSelectedScene.iShowID, sColor, oTxtSceneWidth.value, oTxtSceneHeight.value, sMethod, sAPI, sSecureProtocol);
		//GetEmbedCodes(gSelectedScene.iSceneID, sColor, oTxtSceneWidth.value, oTxtSceneHeight.value, sMethod, sAPI, sSecureProtocol);
	}

	function SetSceneAttr(iSceneID, iMode, iClick){
		var xmlHttpReq = CreateAjaxObj();
		if (!xmlHttpReq)
			return false;
			
		var dataToPost = '?APIName=SetSceneAttr&User='+'<?php echo $CurrUsername;?>'+'&Pswd='+'<?php echo $CurrPassword;?>'+'&SceneID='+iSceneID+'&PlayOnLoad='+iMode+'&PlayOnClick='+iClick+'&AccountID='+'<?php echo $CurrAccountID;?>'; //
		var sUrl = "<?php echo get_option('siteurl').$pluginPath;?>" + "SitepalWizard_Proxy.php"+ dataToPost;
		try{
			xmlHttpReq.onreadystatechange = function(){ HandleSetSceneAttrResponseXML(xmlHttpReq); };
			xmlHttpReq.open('GET', sUrl, true);
			xmlHttpReq.send(null);
		}catch(err){
			alert("SetSceneAttr Error: "+err);
			return false;
		}
		
	}
	
	function getScenes(){
		var xmlHttpReq = CreateAjaxObj();
		if (!xmlHttpReq)
			return false;
			
		var dataToPost = '?APIName=ListScene&User='+'<?php echo $CurrUsername;?>'+'&Pswd='+'<?php echo $CurrPassword;?>'+'&AccountID='+'<?php echo $CurrAccountID;?>';
		var sUrl = "<?php echo get_option('siteurl').$pluginPath;?>" + "SitepalWizard_Proxy.php"+ dataToPost;
		try{
			xmlHttpReq.onreadystatechange = function(){ HandleSceneResponseXML(xmlHttpReq); };
			xmlHttpReq.open('GET', sUrl, true);
			xmlHttpReq.send(null);
		}catch(err){
			alert("GetScene Error: "+err);
			return false;
		}
	
	}

	function GetPreviewEmbedCodes(iSceneID, sMethod, sMode){
		var xmlHttpReq = CreateAjaxObj();
		if (!xmlHttpReq)
			return false;
			
		var dataToPost = '?APIName=GetEmbedCode&User='+'<?php echo $CurrUsername;?>'+'&Pswd='+'<?php echo $CurrPassword;?>'+'&AccountID='+'<?php echo $CurrAccountID;?>'+'&SceneID='+iSceneID+'&Method='+sMethod;
		var sUrl = '<?php echo get_option('siteurl').$pluginPath;?>' + 'SitepalWizard_Proxy.php'+ dataToPost;	
		try{
			xmlHttpReq.onreadystatechange = function(){ HandlePreviewEmbedCodeResponseXML(xmlHttpReq, sMode); };
			xmlHttpReq.open('GET', sUrl, true);
			xmlHttpReq.send(null);
		}catch(err){
			alert("GetScene Error: "+err);
			return false;
		}
		return true;
	}	

	function DuplicateNGetEmbedCodeScene(iSceneID, sColor, Width, Height, sMethod, sAPI, sSecureProtocol){
		var xmlHttpReq = CreateAjaxObj();
		if (!xmlHttpReq)
			return false;

		var dataToPost = '?APIName=DuplicateScene&User='+'<?php echo $CurrUsername;?>'+'&Pswd='+'<?php echo $CurrPassword;?>'+'&AccountID='+'<?php echo $CurrAccountID;?>'+'&SceneID='+iSceneID;
		var sUrl = '<?php echo get_option('siteurl').$pluginPath;?>' + 'SitepalWizard_Proxy.php'+ dataToPost;	
	
		try{
			xmlHttpReq.onreadystatechange = function(){ HandleDuplicateSceneResponseXML(xmlHttpReq, iSceneID, sColor, Width, Height, sMethod, sAPI, sSecureProtocol); };
			xmlHttpReq.open('GET', sUrl, true);
			xmlHttpReq.send(null);
		}catch(err){
			alert("Duplicate Scene Error: "+err);
			return false;
		}
		
		return true;
		
	}
		
	function GetEmbedCodes(iSceneID, sColor, Width, Height, sMethod, sAPI, sSecureProtocol){
		var xmlHttpReq = CreateAjaxObj();
		if (!xmlHttpReq)
			return false;
			
		var dataToPost = '?APIName=GetEmbedCode&User='+'<?php echo $CurrUsername;?>'+'&Pswd='+'<?php echo $CurrPassword;?>'+'&AccountID='+'<?php echo $CurrAccountID;?>'+'&SceneID='+iSceneID+'&API='+sAPI+'^&BGColor='+sColor+'&Method='+sMethod;
		var sUrl = '<?php echo get_option('siteurl').$pluginPath;?>' + 'SitepalWizard_Proxy.php'+ dataToPost;	

		try{
			xmlHttpReq.onreadystatechange = function(){ HandleEmbedCodeResponseXML(xmlHttpReq, sSecureProtocol, Width, Height, iSceneID, <?php echo $CurrAccountID;?>, sColor); };
			xmlHttpReq.open('GET', sUrl, true);
			xmlHttpReq.send(null);
		}catch(err){
			alert("GetScene Error: "+err);
			return false;
		}
	
	}

	//function DisplayExistedScene(sID){
	function DisplayExistedScene(){
		//GetPreviewEmbedCodes(sID[0], 'HTML' , 'Remove');
		PreviewSceneOnRemove();
	}
	
	function getFlashUrlFromString(sText){
		
		var ind = sText.indexOf("movie=\"");
		var iStart = ind+7;
		var iEnd = sText.indexOf("\"", iStart);
		var sRealUrl = sText.substring(iStart, iEnd);
		sRealUrl = sRealUrl.replace('_SITEPAL_', 'vhost.oddcast.com/vhsssecure.php?doc=');
		//sRealUrl = sRealUrl.replace('_SITEPAL_', 'vhost.staging.oddcast.com/vhsssecure.php?doc=');
		
		return sRealUrl;
		
	}

	
	function PreviewSceneOnRemove(){
		var objTextArea = document.getElementById('content');
		if (!objTextArea)
			return;
		var position = FindExistingSceneInPostExt(objTextArea);	
		if (!position)
			return;
		
		var sUrl = getFlashUrlFromString(objTextArea.value.substring(position[0], position[1]));
		objDivEmbedCode = document.getElementById('PreviewExistedScene');
		if (objDivEmbedCode)
			objDivEmbedCode.innerHTML = "<embed type=\"application\/x-shockwave-flash\" src=\"" + sUrl +"\" height=\"150\" width=\"200\" >";
	
	}
	
	function DoRemove(){
		var bSwitched = false;	
		var bSwitched = false;
		if (document.getElementById('edButtonHTML')) {
			var classValue;
			if (document.getElementById('edButtonHTML').getAttribute('class') == null)//IE
				classValue = document.getElementById('edButtonHTML').getAttribute('className');
			else //FireFox
				classValue = document.getElementById('edButtonHTML').getAttribute('class');
		
			/*
			if (classValue == 'edButtonBack'){
				switchEditors('content');
				bSwitched = true;
			}
			*/
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

		/*if (document.getElementById('edButtonHTML') && document.getElementById('edButtonHTML').getAttribute('class') == 'edButtonBack'){
			switchEditors('content');
			bSwitched = true;
		}*/
	
		var objTextArea = document.getElementById('content');
		if (objTextArea){
			//RemoveScene(objTextArea);
			RemoveSceneExt(objTextArea);
			//window.tinyMCE.triggerSave();
		}
		/*
		if (bSwitched){
			switchEditors('content');
			bSwitched = false;
		}
		*/
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

	function CheckUpdate(){
		var xmlHttpReq = CreateAjaxObj();
		if (!xmlHttpReq)
			return false;
		var dataToPost = '?APIName=CheckUpdate&ID='+gID+'&version='+gVersion;
		var sUrl = '<?php echo get_option('siteurl').$pluginPath;?>' + 'SitepalWizard_Proxy.php'+ dataToPost;	
		
		try{
			xmlHttpReq.onreadystatechange = function(){ HandleCheckUpdateResponseXML(xmlHttpReq); };
			xmlHttpReq.open('GET', sUrl, true);
			xmlHttpReq.send(null);
		}catch(err){
			alert("GetScene Error: "+err);
			return false;
		}

	}
	
	function HandleCheckUpdateResponseXML(httpReq){
		if (httpReq.readyState == 4 ){
			if (httpReq.status != 200 ){
				alert("HTTP "+httpReq.status);
			}
			else{
				//alert(httpReq.responseText);
				var result = HandleCheckUpdateResult(httpReq.responseText);
				if (result[0] == 'yes'){
					CheckUpdateText(result[1]);
					
				}
				else if (result[0] == 'no'){
						//PopUp(result[1]);
				}
			}
		}
		else{
			//alert("Status: "+httpReq.readyState);
			//updateStatus(httpReq.readyState);
		}
	}
	
	function HandleCheckUpdateResult(sResultXML){
		try{
			objResDom = LoadXMLDOM(sResultXML);
			var objResTree = objResDom;
			var objResponse = objResTree.getElementsByTagName('response').item(0);
			var objPageLink = objResTree.getElementsByTagName('PageLink').item(0);
			var valResponse = (objResponse && objResponse.firstChild)?objResponse.firstChild.nodeValue:"";
			var valPageLink = (objPageLink && objPageLink.firstChild)?objPageLink.firstChild.nodeValue:""; 
			return [valResponse, valPageLink];
		}catch(e){
		// invalid xml or fail to create DOM xml parser
			return [null, e];
		}
	}
	
	function CheckUpdateText(sLink){
	
		var objUpdateDiv = document.getElementById('CheckUpdateDiv');	
		var content= "<a style=\"text-decoration:none;border:0px\" href=\"Javascript:PopUp\" onClick=\"window.open('" + sLink + "', 'Help', 'height=350, width=720, scrollbars=1'); return false;\"><span style=\"font-style:bold;font-size:12px;color:#ff0000;\">&nbsp;New Sitepal Wizard is available!</span></a>";
		
		if (objUpdateDiv){
			objUpdateDiv.innerHTML = content;
		}
	}


</script>
