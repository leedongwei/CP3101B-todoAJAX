$(document).ready( function() {
	hideAllView();
	$('#navbar-login').show();
	$('#view-login').show();
});

function hideAllView() {
	// navbar
	$('#navbar-login').hide();
	$('#navbar-tasks').hide();
	$('#open-tasks').css("background-color", "none");
	$('#open-settings').css("background-color", "none");
	
	// views
	$('#view-login').hide();
	$('#view-forgetpass').hide();
	$('#view-signup').hide();
	$('#view-settings').hide();
	$('#view-tasks').hide();
}

/**
 * Buttons to display different views
 */
$(function(){
	$('#open-login').on('click', function() {
		hideAllView();
		$('#navbar-login').show();
		$('#view-login').show();
	});
	
	$('#open-view-fp').on('click', function() {
		hideAllView();
		$('#navbar-login').show();
		$('#view-forgetpass').show();
	});
	
	$('#open-signup').on('click', function() {
		hideAllView();
		$('#navbar-login').show();
		$('#view-signup').show();
		$("#signupCPassword").focusout( function() {
			if (checkpassword($("#signupPassword").val(), $("#signupCPassword").val())) {
				$("#signupPasswordCheck").html("Passwords match!");
			} else {
				$("#signupPasswordCheck").html("Passwords do not match");
			}
		});
	});
	
	$('#open-settings').on('click', function() {
		hideAllView();
		$('#navbar-tasks').show();
		$('#view-settings').show();
		$('#open-settings').css("background-color", "#ace");
		
		// POPULATE SETTINGS-FORM FIELDS
		$.getJSON("service/settings.php", { 
			type: "get"
		}, 
		function(data){
			if (data['status']=='ok') {
				$("#setUserNameHolder").html(data['info'][0]);
				$("#setName").val(data['info'][1]);
				$("#setEmail").val(data['info'][2]);
				$('input[name=setPic][value="' + data['info'][3] + '"]').prop('checked', true);
			} else {
				$('#setUserNameHolder').html("ERROR RETRIEVING INFO");
			}
		});
		// END POPULATE SETTINGS-FORM FIELDS
		
		// To reset result after error
		$("#setInfo-Result").html("");

		$("#setCPass").focusout( function() {
			if (checkpassword($("#setNewPass").val(), $("#setCPass").val())) {
				$("#setNewPassCheck").html("Passwords match!");
			} else {
				$("#setNewPassCheck").html("Passwords do not match");
			}
		});
	});
	
	$('#open-tasks').on('click', function() {
		hideAllView();
		$('#navbar-tasks').show();
		$('#view-tasks').show();
		$('#open-tasks').css("background-color", "#ace");
		
		// load welcomeMsg and tasks
		viewtask();
	});
	
	
});

/**
 * Buttons to activate functions
 */
$(function(){
	$('#loginButton').on('click', function() {
		login();
	});
	
	$('#logoutButton').on('click', function() {
		logout();
	});
	
	$('#forgetpassButton').on('click', function() {
		forgetpass();
	});
	
	$('#signupButton').on('click', function() {
		$allFieldsValid = true;
		if ($("#signupPassword").val() != $("#signupCPassword").val()) {
			$("#signup-Result").html("Passwords do no match");
			$allFieldsValid = false;
		} 
		if (!validateEmail($("#signupEmail").val())) {
			$("#signupEmailCheck").html("Invalid email address");
			$allFieldsValid = false;
		}
		if ($allFieldsValid) {
			signup();
		} else {
			$("#signup-Result").html("Error! Please enter the correct information!");
		}
	});
	
	$('#setNewPassButton').on('click', function() {
		$allFieldsValid = true;
		if ($("#setPass").val() == '' ||
				$("#setNewPass").val() == '' ||
				$("#setCPass").val() == '') {
			$("#setNewPass-Result").html("Please fill in all fields");
			$allFieldsValid = false;
		}
		if ($("#setNewPass").val() != $("#setCPass").val()) {
			$("#setNewPass-Result").html("Passwords do no match");
			$allFieldsValid = false;
		} 
		if ($allFieldsValid) {
			changepass();
		}
	});
	
	$('#setInfoButton').on('click', function() {
		$allFieldsValid = true;
		if ($("#setName").val() == '' ||
				$("#setEmail").val() == '' ||
				$('input[name=setPic]:checked', '#newSettingForm').val() == '') {
			$("#setInfo-Result").html("Please fill in all fields");
			$allFieldsValid = false;
		}
		if (!validateEmail($("#setEmail").val())) {
			$("#setInfoEmailCheck").html("Invalid email address");
			$allFieldsValid = false;
		}
		if ($allFieldsValid) {
			changeinfo();
		}
	});
	
	$('#addtaskButton').on('click', function() {
		$allFieldsValid = true;
		if ($('#addtaskname').val() == '') {
			$allFieldsValid = false;
			$('#addtask-Result').html("Insert task name");
		} else if (!(Math.floor($("#addtasktime").val()) == $("#addtasktime").val() && $.isNumeric($("#addtasktime").val()))) {
			$allFieldsValid = false;
			$('#addtask-Result').html("Only int for duration");
		}
		
		if ($allFieldsValid) {
			addtask();
		}
	});
	
});	
function edittaskTrigger($tID) {
	edittask($tID);
}
function deletetaskTrigger($tID) {
	deletetask($tID);
}
	
/**
 * App functions
 */
function login() {
	$.getJSON("service/login.php", { username: $("#username").val(), password: $("#password").val() }, 
		function(data){
			if (data['status']=='ok') {
				$('#open-tasks').trigger('click');
				$("#loginForm-Result").html("<br> ");
			} else {
				$("#loginForm-Result").html("Login Error ");
			}
	});
}

function logout() {
	$.getJSON("service/logout.php", { }, 
		function(data){
			if (data['status']=='logout') {
				location.reload(true);
			}
	});
}

function forgetpass() {
	$.getJSON("service/forgetpass.php", { username: $("#forgetUsername").val() }, 
		function(data){
			if (data['status']=='ok') {
				$("#forgetpassForm-Result").html("<p>Hey " + data['name'] + ", I can't send emails so this function doesn't work :(</p>");
			} else {
				$("#forgetpassForm-Result").html("<p>Your account does not exists</p>");
			}
	});
}

function signup() {
	$.getJSON("service/signup.php", { 
			username: $("#signupUsername").val(),
			password: $("#signupPassword").val(),
			pic: $('input[name=signupPic]:checked', '#signupForm').val(),
			name: $("#signupName").val(),
			email: $("#signupEmail").val(),
		}, 
		function(data){
			if (data['status']=='ok') {
				$("#signup-Result").html('');
				$.getJSON("service/login.php", { username: $("#signupUsername").val(), password: $("#signupPassword").val() });
				$('#open-tasks').trigger('click')
			} else if (data['status']=='username taken') {
				$("#signup-Result").html("The username has been taken");
			} else if (data['status']=='missing data') {
				$("#signup-Result").html("Please fill in missing data!");
			} else {
				$("#signup-Result").html("Account creation error :(");
			}
	});
}

function changepass() {
	$.getJSON("service/settings.php", { 
		type: "password",
		oldpass: $("#setPass").val(),
		newpass: $("#setCPass").val(),
	}, 
	function(data){
		if (data['status']=='ok') {
			$("#setNewPass-Result").html("Password changed!");
			$("#setPass").val() == '';
			$("#setNewPass").val() == '';
			$("#setCPass").val() == '';
		} else if (data['status']=='oldpass') {
			$("#setNewPass-Result").html("Wrong password");
		} else {
			$("#setNewPass-Result").html("Error");
		}
	});
}

function changeinfo() {
	$.getJSON("service/settings.php", { 
		type: "norm",
		name: $("#setName").val(),
		email: $("#setEmail").val(),
		pic: $('input[name=setPic]:checked', '#newSettingForm').val(),
	}, 
	function(data){
		if (data['status']=='ok') {
			$("#setInfo-Result").html("Updated!");
		} else {
			$("#setInfo-Result").html("Error");
		}
	});
}

function viewtask() {
	$.getJSON("service/viewtask.php", {	}, 
	function(data){
		if (data['status']=='ok') {
			// display welcome image
			$welcomePic = "img/" + data['info'][1] + ".jpg";
			$("#welcomePic").attr("src", $welcomePic);
			$("#welcomeMsg").html(data['info'][0]);
			
			// reset div field
			$("#taskMain").html('');
			
			// generate and display tasks
			$.each(data['taskinfo'], function (key, value) {
				$tID = value[0];
				$tname = value[1];
				$ttime = value[2];
				$tdone = value[3];
			
				$HTMLcode = 
				'<div class="taskWidget"><form name="editTask" action="" method="post"><h2 class="taskTitle">' + $tname + '</h2>' +
				'<input type="hidden" name="tID" value="' + $tID + '" /> <button class="deletetaskButton pull-right" type="button" name="deletetaskButton" value="' + $tID + '" style="margin-right:200px;" onclick="deletetaskTrigger(' + $tID +')">Delete</button>'; 
				
				if ($ttime == $tdone) {
					$HTMLcode += "<p>You're done with this!</p>";
				} else {
					$HTMLcode += '	<p>Completed: ' + ($ttime * 0.5) + ' hours, Left: ' + (($ttime - $tdone) * 0.5) + ' hours</p>';
				}
				
				$HTMLcode += '<div class="taskBoxes">';
				// prints out boxes for already completed chunks
				for ($j = 0; $j < $tdone; $j++) {
					$HTMLcode += '<div class="taskBox taskCom"> </div>';
				}
				
				// checks if there are more boxes to print
				$ttime -= $tdone;
				if ($ttime > 0) {
					// print taskClick box
					$HTMLcode += '<div class="taskBox taskClick"> <input type="button" name="✓" value="✓" style="cursor: pointer; cursor: hand; background-color: transparent; text-decoration: none; border:none; color:blue; vertical-align:top;" onclick="edittaskTrigger(' + $tID + ')"/></div>';
					
					// minus the taskClick box
					$ttime -= 1;
					for ($j = 0; $j < $ttime; $j++) {
						if ($ttime > 0) {
							$HTMLcode += '<div class="taskBox taskNorm"> </div>';
						}
					}
				}
				
				$HTMLcode += '</div></form></div>';

				//console.log($HTMLcode);
				$("#taskMain").append($HTMLcode);
			});
			
		} else {
			$("#welcomeMsg").html("Error");
		}
	});
}

function addtask() {
	console.log($("#addtaskname").val()+ "   " + $("#addtasktime").val());
	$.getJSON("service/addtask.php", { 
		taskname: $("#addtaskname").val(),
		tasktime: $("#addtasktime").val()
	}, 
	function(data){
		if (data['status']=='ok') {
			$("#addtask-Result").html("Task added!");
			$("#addtaskname").val('');
			$("#addtasktime").val('');
			$('#open-tasks').trigger('click');
			$("#addtask-Result").delay(2000).fadeOut(1500 ,
					function(){
						$("#addtask-Result").html("<br><br>"); 
						$("#addtask-Result").fadeIn(1);
					});
		} else {
			$("#addtask-Result").html("Error");
		}
	});
}

function edittask($tID) {
	$.getJSON("service/edittask.php", {
		taskID: $tID
	}, 
	function(data){
		if (data['status']=='ok') {
			$('#open-tasks').trigger('click');
			
			$stats = parseInt($('#stats-tracker').val())+1;
			$('#stats-tracker').val($stats);
			$('#stats').html($stats/2);
		} else {
			//
		}
	});
}

function deletetask($tID) {
	$.getJSON("service/deletetask.php", {
		taskID: $tID
	}, 
	function(data){
		if (data['status']=='ok') {
			$('#open-tasks').trigger('click');
		} else {
			//
		}
	});
}

/**
 * Miscellaneous functions that are repeatedly used
 */
function checkpassword($pass1, $pass2) {
	if ($pass1 == $pass2) {
		return true;
	} else { 
		return false;
	}
}

function validateEmail($email) {
	var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
	if (!emailReg.test( $email )) {
		return false;
	} else {
		return true;
	}
}

