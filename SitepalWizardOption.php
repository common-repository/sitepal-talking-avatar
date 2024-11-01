<?php
	$pluginPath = '/wp-content/plugins/sitepal-talking-avatar/';
	$gUsername = '';
	$gPassword = '';
	$gAccountID = '';
	$TempResultData = SitepalWizard_getDataFromDB();
	if ($TempResultData[0] != NULL){
		$gUsername = $TempResultData[0]->username;
		$gPassword = $TempResultData[0]->password;
		$gAccountID = $TempResultData[0]->accountid;
		
	}
		
?>
	<div name="message_FadeBar" id="message_FadeBar" class="error"></div>
	<div class=wrap>
	<form method="post" >
		<h2>Sitepal Configuration<a style="text-decoration:none;border:0px" href="Javascript:PopUp" onClick="window.open('<?php echo get_option('siteurl').$pluginPath.'help/setup_help.htm';?>', 'Help', 'height=700, width=600'); return false;"> <img width="18" heigh="18" src="<?php echo get_option('siteurl').$pluginPath.'/img/helpicon.jpg';?>"></a></h2>
		
		<fieldset name="set1">
		Speaking characters are a proven way to increase traffic and readership of your blog by keeping your blog unique and fresh.<br>
		A SitePal speaking character is a great way to personalize your blog, keep your content updated, and connect with your readers.<br>
		Using SitePal's powerful, self-service editing tool, it's a breeze to design a character to your liking, add your audio to it, and<br>
		publish it live on your blog. Be heard. Literally. And, you can easily update your character and its message anytime you want.<br>
		Start Your 15 day Free Trial Now!

		</fieldset>
		<br>
		<fieldset class="options"> 
		<legend>SitePal Information: </legend>
		<table>
		<tr><td><input type="button" style="width:70px" value=" See It " onMouseDown="window.open('http://www.sitepal.com/overview', null, 'width=700, height=400');"></td><td>A short overview movie about SitePal product <br></td></tr>
		<tr><td><input type="button" style="width:70px" value=" Try It " onMouseDown="window.open('http://www.sitepal.com/demoMovies/tryNow/tryit_demo.php?id=3&newuser=1', null, 'width=800, height=500');"></td><td> Try out the SitePal editor, you can even send your scene to a friend</td></tr>
		<tr><td><input type="button" style="width:70px" value=" Get It " onMouseDown="window.open('http://www.sitepal.com/?&affId=113630&bannerId=0&promotionId=10403 ');"></td><td> Click the Get It button to register for a 15 days free trial account</td></tr>
		</table>
		</fieldset>
		<br>
		<fieldset class="options"> 		
		<legend>SitePal Credentials:</legend>
		<table>
		<tr><td colspan="5">If you already have SitePal account enter your login and password information below:</td></tr>
		
		<tr><td>Login(email):</td><td><input type="text" name="tb_username" id="tb_username" value="<?php echo $gUsername; ?>" ></td><td>Password:</td><td><input type="password" name="tb_password"  id="tb_password" value="<?php echo $gPassword;?>" > </td><td> 
		<input type="button" name="btn_SaveLogin" onMouseDown="ValidateLogin();" value=" SAVE " /></td></tr>
		<tr><td colspan="5"><a href="https://vhost.oddcast.com/user/lost_password.php">Forgot Your Password?</a></td></tr>
		</table>
		<br>
		</fieldset>
		
		<div id="accountInfoArea" name="accountInfoArea" style="display:none">
			<fieldset class="options"> 
			<legend>SitePal Account Selection: </legend>
			Select your account from the list below:<br>
			<select name="SelectAccountInfo" id="SelectAccountInfo"> </select> 
			<input type="button" name="btn_SaveAccountInfo" id="btn_SaveAccountInfo" onMouseDown="SaveAccountInfo(1);" value=" SAVE " /> <br>
			</fieldset>
		</div>
		
		<p align="center" style="color:#999999" > Please be aware that WordPress is open source software available on http://wordpress.org/. Oddcast provides this related plug-in as a convenience to you, and in no way should it be taken to imply any type of association, sponsorship, endorsement, verification, monitoring, approval of, or responsibility for, this open source software. </p>
  	</form>
	 </div>
	 



 <script type="text/javascript" language="javascript">
	
	var objUsername = document.getElementById('tb_username');
	var objPassword = document.getElementById('tb_password');
	var objAccountSelect = document.getElementById('SelectAccountInfo');
	var objUpdateFadeStatusBar = document.getElementById('message_FadeBar');

	window.onload = ValidateLogin;
	
	function PopUp(){
	}
	
	function ValidateLogin(){
		var xmlHttpReq = CreateAjaxObj();
		if (!xmlHttpReq)
			return false;
		if (objUsername.value == '' || objPassword.value == '' ){
			updateStatus(objUpdateFadeStatusBar, "Please type in your password and username to login", 0);
			return false;
		}
		
		var dataToPost = '?APIName=ListAccount&User='+objUsername.value+'&Pswd='+objPassword.value;
		var sUrl = '<?php echo get_option('siteurl').$pluginPath;?>' + 'SitepalWizard_Proxy.php'+ dataToPost;
		
		try{
			xmlHttpReq.onreadystatechange = function(){ HandleLoginResponseXML(xmlHttpReq); };
			//alert(sUrl);
			xmlHttpReq.open('GET', sUrl, true);
			xmlHttpReq.send(null);
		}catch(err){
			//alert("Login Error: "+err);
			updateStatus(objUpdateFadeStatusBar, "Login Error: "+err, 1);
			return false;
		}
	}

	function SaveAccountInfo(Type){
		
		var xmlHttpReq = CreateAjaxObj();
		if (!xmlHttpReq)
			return false;
		
		var dataToPost = '';
		if (Type==0)
			dataToPost = '?DBAction=update&username='+objUsername.value+'&password='+objPassword.value+'&accountid='+objAccountSelect.options[objAccountSelect.selectedIndex].value;
		else
			dataToPost = '?DBAction=update&accountid='+objAccountSelect.options[objAccountSelect.selectedIndex].value;
		var sUrl = '<?php echo get_option('siteurl').$pluginPath;?>' +'SitepalWizard_Request.php' + dataToPost;
		try{
			xmlHttpReq.onreadystatechange = function(){ HandleDBResponseXML(xmlHttpReq); };
			//alert(sUrl);
			xmlHttpReq.open('GET', sUrl, true);
			xmlHttpReq.send(null);
		}catch(err){
			updateStatus(objUpdateFadeStatusBar, "Error: "+err, 1);
			return false;
		}
	}
	
 	
	function HandleDBResponseXML(httpReq){
		
		if (httpReq.readyState == 4 ){
			if (httpReq.status != 200 ){
				updateStatus(objUpdateFadeStatusBar, "HTTP "+ httpReq.status + " error.", 1);
				//return;
			}
			else{
				//alert("DBResponseXML: "+httpReq.responseText);
				updateStatus(objUpdateFadeStatusBar, "Account Information Updated.", 0);
			}
		}
		else{
			updateStatus(objUpdateFadeStatusBar, "Retrieving...", 0);
		}

	}
	
	function HandleLoginResponseXML(httpReq){
	var objUpdateFadeStatusBar = document.getElementById('message_FadeBar');
		if (httpReq.readyState == 4 ){
			if (httpReq.status != 200 ){
				updateStatus(objUpdateFadeStatusBar, "HTTP "+ httpReq.status + " error.", 1);
				
				//return;
			}
			else{
				//Good Save in Database
				var result = HandleListAccounts(httpReq.responseText);
				gObjAccountListResult = result[0];
				if (gObjAccountListResult != null){
					UpdateAccountInfoArea(true, gObjAccountListResult.objAccountItemList);
					SaveAccountInfo(0);
					updateStatus(objUpdateFadeStatusBar, "Login Success.", 0);
					
				}else{
					//alert('bad ' + result[1]);
					updateStatus(objUpdateFadeStatusBar, "Fail to get account information. "+ result[1], 1);
				
				}
			}
		}
		else{
			updateStatus(objUpdateFadeStatusBar, "Authenticating...", 0);
		}
	}
	
	function UpdateAccountInfoArea(bShow, accountListResult){
		var objAccInfoArea = getElementByName('div', 'accountInfoArea');
		var objAccountSelect = getElementByName('select', 'SelectAccountInfo');

		if (!bShow)
			objAccInfoArea.style.display='none';
		else{
			objAccInfoArea.style.display='block';
			PopulateAccountSelect(accountListResult, objAccountSelect);
			SelectCurAcc();
		}
			
	}
	
	function SelectCurAcc(){
		var objAccSelect = document.getElementById('SelectAccountInfo');
		if (!objAccSelect)
			return;
		for (i=0; i<objAccSelect.options.length; i++){
			if (objAccSelect.options[i].value == '<?php echo $gAccountID; ?>'){
				objAccSelect.options[i].selected = true;
				return;
			}
		}
			
	}	
	
 </script>
