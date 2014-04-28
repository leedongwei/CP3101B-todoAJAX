<?php 
	/* ------------------------------------------------
	signup.php: 
		takes in account information for new users.
		cross-check to see if username is unique. if so
		account is created.

	Parameters: 
		username - required, non-empty string 32chars, the users user name
		password - required, non-empty string 32chars, the users password
		pic		- required, value 0-3, representing 4 different profile pics available
		name	- required, non-empty string 32chars, the users name
		email	- required, non-empty string 32chars, the users email

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
	
	$_REQUEST['username'] = santiseInput($_REQUEST['username']);
	$_REQUEST['name'] = santiseInput($_REQUEST['name']);
	

	// ------------------------------------------------
	// Check parameters
	// ------------------------------------------------
	if(empty($_REQUEST['username']) ||
		empty($_REQUEST['password']) ||
		empty($_REQUEST['pic']) ||
		empty($_REQUEST['name']) ||
		empty($_REQUEST['email'])
		){
		// parameters should have been checked by javascript
		$reply['status']='missing data';
	}

	if($reply['status']!='ok'){
		goto leave;
	}

	
	// ------------------------------------------------
	// Preparing login information for database entry
	// ------------------------------------------------
	// HASH FUNCTION USED: SHA256 algorithm
	// SALT USED: cp3101b-a0085419
	$_REQUEST['username'] = strtolower($_REQUEST['username']);
	$_REQUEST['password'] = hash("sha256", "cp3101b-a0085419".$_REQUEST['password'], false);
	

	// ------------------------------------------------
	// Perform operation 
	// ------------------------------------------------
	include ('config.inc');
	
	$_SESSION['dbconn'] = pg_connect("host=127.0.0.1 port=5432 dbname=$db_name user=$db_user password=$db_password");
	if (!$_SESSION['dbconn']) {
		$reply['status']='DB conn';
		goto leave;
	}

	$new_user = pg_prepare($_SESSION['dbconn'], "new_user", 'INSERT INTO APPUSER (USERNAME, PASSWORD, NAME, EMAIL, PIC) VALUES ($1, $2, $3, $4, $5)');
	$new_user = pg_execute($_SESSION['dbconn'], "new_user", array($_REQUEST['username'], $_REQUEST['password'], $_REQUEST['name'], $_REQUEST['email'], $_REQUEST['pic']));
	if (!$new_user) {
		$reply['status']='username taken';
	} else {
		$reply['status']='ok';
	}

	
	// ------------------------------------------------
	// Send reply 
	// ------------------------------------------------
	leave:
	print json_encode($reply);
?>