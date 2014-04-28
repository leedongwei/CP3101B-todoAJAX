<?php 
	/* ------------------------------------------------
	edittask.php: 
		increment taskdone field for a specific task

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
	
	$get_taskdone = pg_prepare($_SESSION['dbconn'], "get_taskdone", 'SELECT TASKDONE FROM USERTASK WHERE TID = $1');
	$get_taskdone = pg_fetch_row(pg_execute($_SESSION['dbconn'], "get_taskdone", array($_REQUEST['taskID'])));
	$get_taskdone[0] = $get_taskdone[0] + 1;
	$edit_task = pg_prepare($_SESSION['dbconn'], "update_task", 'UPDATE USERTASK SET TASKDONE = $1 WHERE TID = $2');
	$edit_task = pg_execute($_SESSION['dbconn'], "update_task", array($get_taskdone[0], $_REQUEST['taskID']));

	// ------------------------------------------------
	// Send reply 
	// ------------------------------------------------
	leave:
	print json_encode($reply);
?>