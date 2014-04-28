<?php 
	/* ------------------------------------------------
	forgetpass.php: 
		searches database to see if username exists
		$reply['name'] == name of the user on success

	Parameters: 
		username - required, non-empty string, the users user name

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
	// Check parameters
	// ------------------------------------------------
	if(empty($_REQUEST['username'])){
		$reply['status']='no data';
	}
	
	if( $reply['status'] != 'ok'){ 
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

	$forget_pass = pg_prepare($_SESSION['dbconn'], "forget_pass", 'SELECT NAME, PASSWORD FROM APPUSER WHERE USERNAME = $1');
	$forget_pass = pg_execute($_SESSION['dbconn'], "forget_pass", array($_REQUEST['username']));
	
	$row = pg_fetch_row($forget_pass);
	
	if (count($row) == 2) {
		$reply['status']='ok';
		$reply['name']=$row[0];
	} else {
		$reply['status']='not found';
	}
	

	// ------------------------------------------------
	// Send reply 
	// ------------------------------------------------
	leave: 
	print json_encode($reply);
?>