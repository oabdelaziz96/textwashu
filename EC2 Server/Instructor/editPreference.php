<?php
require('../Sensitive/database.php'); //Database Access
session_start();

if (!isset($_SESSION['number'])) { //Redirect if not logged in
		echo "Log in to enter this area";
		header("refresh: 2; url=login.html");
		exit;
}

$twilioNumber = $_SESSION['number'];
		
//Get variables from previous page 
$prefNum = $_POST['prefNum'];
$status = $_POST['status'];
$arg1 = $_POST['arg1'];
$arg2 = $_POST['arg2'];

// Filter primary variables that we can 
if( !preg_match('/^[1-9]$|^[1-9][0134]$/', $prefNum) ){ //Request didn't come in with preference number
        $alertMessage =  "An error occured.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^(On|Off)$/', $status) ){
        $alertMessage =  "Invalid status.";
        echo $alertMessage;
        exit;
}

//Connect to this account's database
$mysqli = setMysqlDatabase($twilioNumber);

if ($status == "On") { //If we're turning a preference on, then check the regex

		//Get regex for arg1 and arg2
		$stmt = $mysqli->prepare("select arg1_regex, arg1_regex_msg, arg2_regex, arg2_regex_msg from preferences where number = '$prefNum'");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		} 
		$stmt->execute();
		$stmt->bind_result($arg1_regex, $arg1_regex_msg, $arg2_regex, $arg2_regex_msg);
		$stmt->fetch();
		$stmt->close();
		
		//Temp solve for URL Regex
		//if ($arg2_regex == "URLREGEX") $arg2_regex = "^(?=.{1,255}$)(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})$|^$";

		$arg1_regex = stripslashes($arg1_regex);
		$arg2_regex = stripslashes($arg2_regex);
		
		
} else { //Otherwise just check that the arguments are less than 255 characters
		
		$arg1_regex = "^.{0,255}$";
		$arg1_regex_msg = "Argument cannot be longer than 255 characters";
		$arg2_regex = "^.{0,255}$";
		$arg2_regex_msg = "Argument cannot be longer than 255 characters";
		
}

//Filter arg1 and arg2
if( !preg_match('/'.$arg1_regex.'/', $arg1) ){ //Argument 1 has invalid regex
        $alertMessage =  $arg1_regex_msg;
        echo $alertMessage;
        exit;
}

if( !preg_match('/'.$arg2_regex.'/', $arg2) ){ //Argument 2 has invalid regex
        $alertMessage =  $arg2_regex_msg;
        echo $alertMessage;
        exit;
}

//Update preference
$stmt = $mysqli->prepare('update preferences set status=?, arg1=?, arg2=? where number=?');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('ssss', $status, $mysqli->real_escape_string($arg1), $mysqli->real_escape_string($arg2), $prefNum);
$stmt->execute();
$stmt->close();

$mysqli->close();

//Confirmation and session start
echo "Successfully edited preference";
header("refresh: 1; url=managePreferences.php");
exit;
 
?>