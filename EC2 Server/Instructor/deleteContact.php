<title>Deleting Contact...</title>
<?php

require('../Sensitive/database.php'); //Database Access
session_start();

if (!isset($_SESSION['number'])) { //Redirect if not logged in
		echo "Log in to enter this area";
		header("refresh: 2; url=login.html");
		exit;
}

//Get variables from previous page 
$studNum = $_GET['phone_number'];
$twilioNumber = $_SESSION['number'];


// Filter variables
if( !preg_match('/^[0-9]{10}$/', $studNum) ){
		$alertMessage = "An error occured.";
		echo $alertMessage;
		exit;
}

//Connect to this account's database
$mysqli = setMysqlDatabase($twilioNumber);

//Get all hashtags
$query = 'select id from hashtags';
$stmt = $mysqli->prepare($query);
if(!$stmt){
		printf("Query Prep Failed: %s\n", $mysqli->error);
		exit;
}
$stmt->execute();
$stmt->bind_result($hashtag);
$hashtagArray = [];

while($stmt->fetch()) { //For each hashtag
		array_push($hashtagArray, $hashtag);
}

$stmt->close();

//For each hashtag, delete all messages with phone number
for ($i = 0; $i < count($hashtagArray); $i++) {
		$stmt = $mysqli->prepare('delete from `'.$hashtagArray[$i].'` where phone_number=?');
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $studNum);
		$stmt->execute();
		$stmt->close();
}

//Delete all messages from hub with phone number
$stmt = $mysqli->prepare('delete from hub where phone_number=?');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('s', $studNum);
$stmt->execute();
$stmt->close();

//Delete contact
$stmt = $mysqli->prepare('delete from contacts where phone_number=?');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('s', $studNum);
$stmt->execute();
$stmt->close();
$mysqli->close();

//Confirmation and session start
echo "Successfully deleted $studNum contact information and all associated messgaes";
header("refresh: 2; url=viewContacts.php");
exit;
 
?>