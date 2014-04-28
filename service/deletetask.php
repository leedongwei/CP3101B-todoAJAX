<?php 
	/* ------------------------------------------------
	deletetask.php: 
		delete a specific task identified by it's task ID

	Parameters: 
		taskID - required, non-empty integer, the task's ID

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
	// Perform operation 
	// ------------------------------------------------
	include ('config.inc');
	
	$_SESSION['dbconn'] = pg_connect("host=127.0.0.1 port=5432 dbname=$db_name user=$db_user password=$db_password");
	if (!$_SESSION['dbconn']) {
		$reply['status']='DB conn';
		goto leave;
	}
	
	$delete_task = pg_prepare($_SESSION['dbconn'], "delete_task", 'DELETE FROM USERTASK WHERE TID = $1');
	$delete_task = pg_execute($_SESSION['dbconn'], "delete_task", array($_REQUEST['taskID']));	
	
	// ------------------------------------------------
	// Send reply 
	// ------------------------------------------------
	leave:
	print json_encode($reply);
?>