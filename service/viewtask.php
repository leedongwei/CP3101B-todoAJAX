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
	// Check parameters
	// ------------------------------------------------
	if(empty($_SESSION['username']) || $_SESSION["is_logged_in"] == "false"){
		$reply['status']='no login';
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
	
	// Get user's name and profile pic to populate welcome message
	$getinfo = pg_prepare($_SESSION['dbconn'], "getinfo", "SELECT NAME, PIC FROM APPUSER WHERE USERNAME = $1");
	$getinfo = pg_execute($_SESSION['dbconn'], "getinfo", array($_SESSION['username']));
	$row = pg_fetch_row($getinfo);
	$reply['info'][0] = $row[0];
	$reply['info'][1] = $row[1];
	
	// Get all of user's tasks
	$getTasks = pg_prepare($_SESSION['dbconn'], "getTask", "SELECT 
				UT.TID, UT.TASKNAME, UT.TASKTIME, UT.TASKDONE
				FROM USERTASK UT
				WHERE UT.USERNAME = $1
				ORDER BY UT.TID");
	$getTasks = pg_execute($_SESSION['dbconn'], "getTask", array($_SESSION['username']));
	
	$i = 0;
	while($row = pg_fetch_row($getTasks)) {
		$reply['taskinfo'][$i][0] = $row[0];
		$reply['taskinfo'][$i][1] = $row[1];
		$reply['taskinfo'][$i][2] = $row[2];
		$reply['taskinfo'][$i][3] = $row[3];
		$i++;
	}
	
	// ------------------------------------------------
	// Send reply 
	// ------------------------------------------------
	leave:
	print json_encode($reply);
?>