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
if( !preg_match('/^[1-9]$|^[1-9][01]$/', $prefNum) ){ //Request didn't come in with preference number
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

////Get regex for arg1 and arg2
//$stmt = $mysqli->prepare("select arg1_php_regex, arg2_php_regex from preferences where number = '$prefNum'");
//if(!$stmt){
//	printf("Query Prep Failed: %s\n", $mysqli->error);
//	exit;
//} 
//$stmt->execute();
//$stmt->bind_result($arg1_regex, $arg2_regex);
//$stmt->fetch();
//$stmt->close();
//
////Filter arg1 and arg2
//if( !preg_match('/'.$arg1_regex.'/', $arg1) ){ //Argument 1 has invalid regex
//        $alertMessage =  "Argument 1 invalid";
//        echo $alertMessage;
//        exit;
//}
//
//if( !preg_match('/'.$arg2_regex.'/', $arg2) ){ //Argument 2 has invalid regex
//        $alertMessage =  "Argument 2 invalid";
//        echo $alertMessage;
//        exit;
//}

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