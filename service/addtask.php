<?php 
	/* ------------------------------------------------
	addtask.php: 
		add a new task into the database and link it to the
		unique username

	Parameters: 
		username - required, non-empty string, the users user name
		taskname - required, non-empty string, the name of task
		tasktime - required, non-empty string, the duration of task

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
		//echo ".." . $data . "..";
		return $data;
	}
	
	$_REQUEST['taskname'] = santiseInput($_REQUEST['taskname']);

	// ------------------------------------------------
	// Check parameters
	// ------------------------------------------------
	if(empty($_SESSION["username"]) || !$_SESSION["is_logged_in"]) {
		$reply['status']='no login';
	}
	if(empty($_REQUEST['taskname']) || empty($_REQUEST['tasktime'])){
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
	
	$findTID = pg_prepare($_SESSION['dbconn'], "findTID", "SELECT TID FROM USERTASK ORDER BY TID ASC");
	$findTID = pg_execute($_SESSION['dbconn'], "findTID", array());

	$newTID = 0;
	while($TID = pg_fetch_row($findTID)) {
		//echo $TID[0] . "...";
		$newTID = $TID[0];
	}
	$newTID++;
	
	//echo $newTID . "...";

	$addtask = pg_prepare($_SESSION['dbconn'], "addtask", 'INSERT INTO USERTASK (TID, USERNAME, TASKNAME, TASKTIME, TASKDONE) VALUES ($1, $2, $3, $4, $5)');
	$addtask = pg_execute($_SESSION['dbconn'], "addtask", array($newTID, $_SESSION["username"], $_REQUEST['taskname'], $_REQUEST['tasktime'], '0'));
	
	if (!$addtask) {
		$reply['status'] = "addtask failed";
	}
	
	// ------------------------------------------------
	// Send reply 
	// ------------------------------------------------
	leave:
	print json_encode($reply);
?>