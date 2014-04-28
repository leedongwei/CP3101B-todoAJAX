<?php 
	/* ------------------------------------------------
	logout.php: 
		logout script to destroy the session

	Parameters: 
		none

	Returns: 
		{ status: "logout" } on success
	
	------------------------------------------------ */
	
	
	session_save_path("sessions");
	session_start(); 
	header('Content-Type: application/json');
	
	// ------------------------------------------------
	// Default reply
	// ------------------------------------------------
	$reply=array();
	$reply['status']='logout';

	// ------------------------------------------------
	// Perform operation
	// ------------------------------------------------
	$_SESSION=array();	
	session_unset();
	session_destroy();
	
	// ------------------------------------------------
	// Send reply 
	// ------------------------------------------------
	print json_encode($reply);
?>