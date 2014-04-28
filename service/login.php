<?php
	/* ------------------------------------------------
	login.php: 
		login to the application if valid credentials 
		are supplied, otherwise the user is logged out.

		$_SESSION['is_logged_in']=="true" on success
		$_SESSION['is_logged_in']=="false" on failure

	Parameters: 
		username - required, non-empty string, the users user name
		password - required, non-empty string, the users password

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
	// Default resulting state
	// ------------------------------------------------
	$_SESSION["is_logged_in"]="false";

	// ------------------------------------------------
	// Check parameters
	// ------------------------------------------------
	if(empty($_REQUEST['username']) || empty($_REQUEST['password'])){
		$reply['status']='no data';
	}

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
	
	// HASH FUNCTION USED: SHA256 algorithm
	// SALT USED: cp3101b-a0085419
	$user = strtolower($_REQUEST['username']);
	$pass = hash("sha256", "cp3101b-a0085419".$_REQUEST['password'], false);
	
	$check_cred = pg_prepare($_SESSION['dbconn'], "loginquery", 'SELECT * FROM APPUSER WHERE USERNAME = $1 AND PASSWORD = $2');
	$check_cred = pg_execute($_SESSION['dbconn'], "loginquery", array($user, $pass));
	if (!$check_cred) {
		$reply['status'] = "DB error";
	} else {
		$row = pg_fetch_row($check_cred);
		if ($row[0] == $user && $row[1] == $pass) {
			$reply['status'] = "ok";
			$_SESSION["username"]=$user;
			$_SESSION["is_logged_in"]="true";
			//$reply['name']=$_SESSION["username"];
		} else {
			$reply['status'] = "error";
		}
	}
	
	
	// ------------------------------------------------
	// Send reply 
	// ------------------------------------------------
	leave:
	print json_encode($reply);
?>
