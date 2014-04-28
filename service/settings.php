<?php 
	/* ------------------------------------------------
	settings.php: 
		changes to user details in the database

	Parameters: 
		type 		- required, string value "get" or "password" or "norm", 
					  to determine type of operation
						"get" : retrieve data to populate settings form
						"password" : change password only
						"norm" : change account details only
						
		oldpass		- for "password" operation, non-empty string, the users old password
		newpass		- for "password" operation, non-empty string, the users new password
		
		name		- for "norm" operation, non-empty string, the user's name
		email		- for "norm" operation, non-empty string, the user's email
		pic			- for "norm" operation, non-empty integer, the user's profile pic

	Returns: 
		{ status: "ok" } on success
		{ status: "<error messages>" } on failure
	
	------------------------------------------------ */
	
	
	session_save_path("sessions");
	session_start(); 
	header('Content-Type: application/json');
	
	// ------------------------------------------------
	// Default reply
	// ------------------------------------------------
	$reply=array();
	$reply['status']='ok';
	
	// ------------------------------------------------
	// Santise user input
	// ------------------------------------------------
	function santiseInput($data) {
		$search = array(
			'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
			'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
			'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
			'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
		);
	 
		$data = preg_replace($search, '', $data);
		return $data;
	}
	
	$_REQUEST['name'] = santiseInput($_REQUEST['name']);

	// ------------------------------------------------
	// Check parameters
	// ------------------------------------------------
	if ($_REQUEST['type'] == "password") {
		if (empty($_REQUEST['oldpass']) || empty($_REQUEST['newpass'])){
			$reply['status']='unfilled data';
		}
	} else if ($_REQUEST['type'] == "norm") {
		if (empty($_REQUEST['name']) || empty($_REQUEST['email']) || empty($_REQUEST['pic'])) {
			$reply['status']='unfilled data';
		}
	} else if($_REQUEST['type'] == "get") {
		// no parameter require checking
	} else {
		$reply['status']='invalid type';
	}
	
	/*
	if(empty($_REQUEST['username']) || empty($_REQUEST['password'])){
		$reply['status']='no data';
	}*/

	if($reply['status']!='ok'){
		goto leave;
	}

	// ------------------------------------------------
	// Perform operation 
	// ------------------------------------------------
	include ('config.inc');
	
	$_SESSION['dbconn'] = pg_connect("host=127.0.0.1 port=5432 dbname=$db_name user=$db_user password=$db_password");
	if (!$_SESSION['dbconn']) {
		$reply['status']='DB conn';
		goto leave;
	}
	
	// ------------------------------------------------
	// Change password
	// ------------------------------------------------
	if ($_REQUEST['type'] == "password") {
		// retrieve hashed password from database
		$get_oldpass = pg_prepare($_SESSION['dbconn'], "get_oldpass", 'SELECT PASSWORD FROM APPUSER WHERE USERNAME = $1');
		$get_oldpass = pg_execute($_SESSION['dbconn'], "get_oldpass", array($_SESSION["username"]));
		if (!$get_oldpass) {
			$reply['status']=$_SESSION["username"] . ' error-1';
			goto leave;
		}
		$row = pg_fetch_row($get_oldpass);	
		
		// hash user-entered password
		$oldpass = hash("sha256", "cp3101b-a0085419".$_REQUEST['oldpass'], false);
		
		// compare database and user-entered password
		if ($row[0] == $oldpass) {
			$_REQUEST['newPassword'] = hash("sha256", "cp3101b-a0085419".$_REQUEST['newpass'], false);
			$change_pass = pg_prepare($_SESSION['dbconn'], "change_pass", 'UPDATE APPUSER SET PASSWORD = $1 WHERE USERNAME = $2');
			$change_pass = pg_execute($_SESSION['dbconn'], "change_pass", array($_REQUEST['newPassword'], $_SESSION["username"]));
			
			if (!$change_pass) {
				$reply['status']='error';
			} else {
				$reply['status']='ok';
			}
		} else {
			$reply['status']='error';
		}		
		
	} 
	// ------------------------------------------------
	// Change other info
	// ------------------------------------------------
	else if ($_REQUEST['type'] == "norm") {
		$change_info = pg_prepare($_SESSION['dbconn'], "change_info", 'UPDATE APPUSER SET NAME = $1, EMAIL = $2, PIC = $3 WHERE USERNAME = $4');
		$change_info = pg_execute($_SESSION['dbconn'], "change_info", array($_REQUEST['name'], $_REQUEST['email'], $_REQUEST['pic'], $_SESSION["username"]));
		
		if (!$change_info) {
			$reply['status']='error';
			goto leave;
		} else {
			$reply['status']='ok';
		}

	} 
	// ------------------------------------------------
	// Retrieve info
	// ------------------------------------------------
	else if($_REQUEST['type'] == "get") {
		$get_info = pg_prepare($_SESSION['dbconn'], "get_info", 'SELECT USERNAME, NAME, EMAIL, PIC FROM APPUSER WHERE USERNAME = $1');
		$get_info = pg_execute($_SESSION['dbconn'], "get_info", array($_SESSION["username"]));
		
		if (!$get_info) {
			$reply['status']='error';
			goto leave;
		} else {
			$reply['status']='ok';
		}
		
		$row = pg_fetch_row($get_info);	
		$reply['info'] = $row;
	}
	
	// ------------------------------------------------
	// Send reply 
	// ------------------------------------------------
	leave:
	print json_encode($reply);
?>