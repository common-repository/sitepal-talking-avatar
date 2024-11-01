<?php
	// need to include this php to use wpdb class
	include_once("../../../wp-config.php");
	include_once("SitepalWizardLib.php");
	
	$DBAction = isset($_REQUEST['DBAction'])?$_REQUEST['DBAction']:'none';
	$Username = isset($_REQUEST['username'])?$_REQUEST['username']:'';
	$Password = isset($_REQUEST['password'])?$_REQUEST['password']:'';
	$AccountID = isset($_REQUEST['accountid'])?$_REQUEST['accountid']:'';
	
	$result = NULL;
	
	switch($DBAction){
		case 'update':
			$result = SitepalWizard_saveDataIntoDB($Username, $Password, $AccountID);
			break;
		case 'get':
			$result = SitepalWizard_getDataFromDB();
			break;
		case 'none':
			break;
	}
	
	if ($result == NULL	){
		echo '<Status>0</Status>';
		
	}
	else{
		echo '<Status>1</Status>';
		echo '<Username>'.$result[0]->username.'</Username>';
		echo '<Password>'.$result[0]->password.'</Password>';
		echo '<AccountID>'.$result[0]->accountid.'</AccountID>';
	}
?>