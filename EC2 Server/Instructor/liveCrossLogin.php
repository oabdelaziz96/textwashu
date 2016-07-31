<?php
require('../Sensitive/database.php'); //Database Access
session_start();

if (!isset($_SESSION['number'])) { //Redirect if not logged in
		echo "Log in to enter this area";
		header("refresh: 2; url=login.html");
		exit;
}

$location = $_GET['location'];
$twilioNumber = $_SESSION['number'];


// Filter variable
if( !preg_match('/^(texts|poll)$/', $location) ){
        $alertMessage =  "Invalid location.";
        echo $alertMessage;
        exit;
}

//Connect to database
$mysqli = setMysqlDatabase("participationDB");

//Get nodeCode
$stmt = $mysqli->prepare("select nodeCode from accounts where twilio_phone_number = '$twilioNumber'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($output);
$stmt->fetch();
$stmt->close();

//Redirect
header("Location: http://live.textwashu.com/crossLogin?phoneNumber=$twilioNumber&nodeCode=$output&location=$location");
exit;
 
?>