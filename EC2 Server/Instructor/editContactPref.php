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
$enableWeb = $_POST["enableWeb"];
$enableName = $_POST["enableName"];
$enableEmail = $_POST["enableEmail"];
$enableWuKey = $_POST["enableWuKey"];
$enableID = $_POST["enableID"];

// Filter variables
if( !preg_match('/^(On|Off)$/', $enableWeb) ){
        $alertMessage =  "An error occured.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^(On|Off)$/', $enableName) ){
        $alertMessage =  "An error occured.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^(On|Off)$/', $enableEmail) ){
        $alertMessage =  "An error occured.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^(On|Off)$/', $enableWuKey) ){
        $alertMessage =  "An error occured.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^(On|Off)$/', $enableID) ){
        $alertMessage =  "An error occured.";
        echo $alertMessage;
        exit;
}

//Connect to this account's database
$mysqli = setMysqlDatabase($twilioNumber);

//Create new preference array
$prefArray = array(
				"enableWeb" => $enableWeb == "On",
				"enableName" => $enableName == "On",
				"enableEmail" => $enableEmail == "On",
				"enableWuKey" => $enableWuKey == "On",
				"enableID" => $enableID == "On"
				);

//JSON Encode the array
$prefArray = json_encode($prefArray);

//Update preference
$stmt = $mysqli->prepare('update preferences set arg1=? where number = "15"');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('s', $prefArray);
$stmt->execute();
$stmt->close();
$mysqli->close();

//Confirmation and session start
echo "Successfully edited contact preferences";
header("refresh: 1; url=viewContacts.php");
exit;
 
?>