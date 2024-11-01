<?php
	function SitepalWizard_getDataFromDB(){
		global $wpdb;
		$table_name = $wpdb->prefix.'sitepalwizard';
		$existing_table = $wpdb->get_var("show tables like '$table_name'");
		
		if (empty($existing_table)){
			return NULL;
		}
		else{
			// There should always be one pair of username and password.
			$sql = "SELECT * FROM $table_name LIMIT 0, 1";
			$safe_sql = $wpdb->escape($sql);
			$result = $wpdb->get_results($safe_sql);
			return $result;
		}
	}


	function SitepalWizard_InstallDB(){
		global $wpdb;
		$table_name = $wpdb->prefix.'sitepalwizard';
		$existing_table = $wpdb->get_var("show tables like '$table_name'");
		
		if (empty($existing_table)){ 
			$sql = "CREATE TABLE ".$table_name." (user_index INT NOT NULL AUTO_INCREMENT, username text not null, password text not null, accountid text not null, INDEX(user_index));";
			$safe_sql  = $wpdb->escape($sql);
			$result = $wpdb->query($safe_sql);
			//if (!$result)
		}
	}

	function SitepalWizard_saveDataIntoDB($username, $password, $accountid){
		$result =  SitepalWizard_getDataFromDB();
		if ($result == NULL){
			SitepalWizard_insertDataIntoDB($username, $password, $accountid);
			return false;
		}else{
			SitepalWizard_updateDataIntoDB($username, $password, $accountid);
			return true;
		}
	}
	
	function SitepalWizard_updateDataIntoDB($username, $password, $accountid){
		global $wpdb;
		$table_name = $wpdb->prefix.'sitepalwizard';
		$existing_table = $wpdb->get_var("show tables like '$table_name'");
		if (empty($existing_table)){
			return NULL;
		}
		else{
			if ($username=='' || $password=='')
				$sql = "UPDATE $table_name SET accountid='$accountid' ";
			else
				$sql = "UPDATE $table_name SET username='$username', password='$password', accountid='$accountid' ";
			//echo $sql;
			//$safe_sql = $wpdb->escape($sql);
			$result = $wpdb->query($sql);
			return $result;
		}
	}
	
	function SitepalWizard_insertDataIntoDB($username, $password, $accountid){
		global $wpdb;
		$table_name = $wpdb->prefix.'sitepalwizard';
		$existing_table = $wpdb->get_var("show tables like '$table_name'");
		if (empty($existing_table)){
			return NULL;
		}
		else{
			$sql = "INSERT INTO $table_name ( user_index , username , password , accountid )VALUES (NULL , '$username', '$password', '$accountid')";
			//$safe_sql = $wpdb->escape($sql);
			$result = $wpdb->query($sql);
			return $result;
		}
	
	}
	
	
?>