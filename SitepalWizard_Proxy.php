<?php
	header("Content-Type: text/xml");
	//$sAPIHost = "vhost.oddcast.com";
	$sAPIHost = "www.oddcast.com/plugins/";
	$sAPIProtocol = "https"; //"http";

	$APIName = isset($_REQUEST['APIName'])?$_REQUEST['APIName']:'';  
	$Username = isset($_REQUEST['User'])?$_REQUEST['User']:''; 
	$Password = isset($_REQUEST['Pswd'])?$_REQUEST['Pswd']:''; 
	$AccountID = isset($_REQUEST['AccountID'])?$_REQUEST['AccountID']:'';
	//$ShowID = isset($_REQUEST['ShowID'])?$_REQUEST['ShowID']:'';
	$SceneID = isset($_REQUEST['SceneID'])?$_REQUEST['SceneID']:'';
	$Width = isset($_REQUEST['Width'])?$_REQUEST['Width']:200;
	$Height = isset($_REQUEST['Height'])?$_REQUEST['Height']:150;
	$sAPI = isset($_REQUEST['sAPI'])?$_REQUEST['sAPI']:'N';
	$Color = isset($_REQUEST['BGColor'])?$_REQUEST['BGColor']:'';
	$Method = isset($_REQUEST['Method'])?$_REQUEST['Method']:'';
	$PlayOnLoad = isset($_REQUEST['PlayOnLoad'])?$_REQUEST['PlayOnLoad']:'';
	$PlayOnClick = isset($_REQUEST['PlayOnClick'])?$_REQUEST['PlayOnClick']:'';
	$ID = isset($_REQUEST['ID'])?$_REQUEST['ID']:'';
	$Version = isset($_REQUEST['version'])?$_REQUEST['version']:'';
	
	$url = '';
	$DataToPost = '';
	$bIsCheckUpdate = false;
	$url = 'vhostapi_proxy.php';	
	switch ($APIName){
	case 'ListAccount':
		//$DataToPost = 'User='.$Username.'&Pswd='.$Password.'&xml=1';
		$DataToPost = 'funcName=ListAccounts&User='.$Username.'&Pswd='.$Password;
		
		break;
	case 'ListScene':
		//$DataToPost = 'User='.$Username.'&Pswd='.$Password.'&xml=1'.'&AccountID='.$AccountID;
		$DataToPost = 'funcName=ListScenes&User='.$Username.'&Pswd='.$Password.'&AccountID='.$AccountID;
		break;
	case 'ListShow':
		//$DataToPost += "&SceneID=".$SceneID
		//$DataToPost = 'funcName=ListScenes&SceneID='.$SceneID;
		break;
	case 'GetEmbedCode':
		//$DataToPost = 'User='.$Username.'&Pswd='.$Password.'&xml=1'.'&AccountID='.$AccountID.'&SceneID='.$SceneID.'&Width='.$Width.'&Height='.$Height."&Color=".$Color."&Method=".$Method."&API=".$sAPI;
		$DataToPost = 'funcName=EmbedScene&User='.$Username.'&Pswd='.$Password.'&AccountID='.$AccountID.'&SceneID='.$SceneID.'&Width='.$Width.'&Height='.$Height."&Color=".$Color."&Method=".$Method."&API=".$sAPI;

		break;
	case 'DuplicateScene'://get a duplicated scene
		//$DataToPost = 'User='.$Username.'&Pswd='.$Password.'&xml=1'.'&AccountID='.$AccountID.'&SceneID='.$SceneID.'&Status=2'/*.'&Name='.$Name*/;
		$DataToPost = 'funcName=DuplicateScene&User='.$Username.'&Pswd='.$Password.'&AccountID='.$AccountID.'&SceneID='.$SceneID.'&Status=2'/*.'&Name='.$Name*/;
		break;
	case 'SetSceneAttr':
		//$DataToPost = 'User='.$Username.'&Pswd='.$Password.'&xml=1'.'&SceneID='.$SceneID.'&PlayMode='.$PlayOnLoad.'&PlayOnClick='.$PlayOnClick.'&AccountID='.$AccountID;
		$DataToPost = 'funcName=SetSceneAttr&User='.$Username.'&Pswd='.$Password.'&SceneID='.$SceneID.'&PlayMode='.$PlayOnLoad.'&PlayOnClick='.$PlayOnClick.'&AccountID='.$AccountID;
		break;
	case 'CheckUpdate':
		$bIsCheckUpdate = true;
		$url = 'http://www.oddcast.com/plugins/upgrade.php?ID='.$ID.'&version='.$Version;
		break;
	}
	
	if ($bIsCheckUpdate){
		HttpGet($url);
	}else{
		$url = $sAPIProtocol.'://'.$sAPIHost.$url;
		HttpPost($url, $DataToPost);
	}
	
	function HttpGet($url){
		if (!function_exists('curl_init')){
			echo "<RESPONSE>";
			echo "<Status>65553</Status>";
			echo "<Error>Please contact your web administrator to enable php_curl.dll in php.ini file</Error>";
			echo "</RESPONSE>";
			return;
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 8);
		
		$xml = curl_exec($ch);
		curl_close($ch);
		
		echo $xml;
	
	}
	
	function HttpPost($url, $DataToPost){
		if (!function_exists('curl_init')){
			echo "<RESPONSE>";
			echo "<Status>65553</Status>";
			echo "<Error>Please contact your web administrator to enable php_curl.dll in php.ini file</Error>";
			echo "</RESPONSE>";
			return;
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $DataToPost);
		$xml = curl_exec($ch);
		curl_close($ch);
		
		echo $xml;
	}
?>