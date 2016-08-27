<?php
require('../Sensitive/database.php'); //Database Access
session_start();

if (!isset($_SESSION['number'])) { //
		echo "Log in to enter this area";
		header("refresh: 2; url=login.html");
		exit;
}

//Get variables from previous page 
$id = strtolower($_POST['hashtag']); //Lowercase hashtag off the bat
$status = $_POST['status'];
$response = $_POST['reply'];
$twilioNumber = $_SESSION['number'];

//Replace new line html with new line symbol
$response = str_replace("\r\n", "*nL*", $response);


// Filter variables
if( !preg_match('/^#[a-zA-Z0-9]{1,19}$/', $id) ){
        $alertMessage =  "Invalid hashtag. Hashtag should consist of letters and numbers only (no spaces or special characters), and must be between 1-19 characters.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^(Active|Archived|Session)$/', $status) ){
        $alertMessage =  "Invalid status.";
        echo $alertMessage;
        exit;
}

if(strlen($response) > 255){
        $alertMessage = "Reply too long... Reply must be less than 255 characters";
		echo $alertMessage;
        exit;
}

//Connect to this account's database
$mysqli = setMysqlDatabase($twilioNumber);

//Check to see if hashtag already exists
$stmt = $mysqli->prepare("select id from hashtags where id = '$id'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($output);
$stmt->fetch();
$stmt->close();
if(!is_null($output)) {//hashtag already exists
        $alertMessage =  "Hashtag already being used. Please choose a different hashtag name.";
        echo $alertMessage;
        exit;
}


//Insert hashtag info into hashtags table
$stmt = $mysqli->prepare('insert into hashtags (id, status, response) values (?, ?, ?)');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('sss', $id, $status, $mysqli->real_escape_string($response));
$stmt->execute();
$stmt->close();

//Create approprtiate hashtag table

if ($status == "Session") { //Create session table
		
		$createTableSQL = "CREATE TABLE `$id` (
		`phone_number` char(10) NOT NULL,
		`message` varchar(10),
		`Q1` char(1),
		`Q2` char(1),
		`Q3` char(1),
		`Q4` char(1),
		`Q5` char(1),
		`Q6` char(1),
		`Q7` char(1),
		`Q8` char(1),
		`Q9` char(1),
		`Q10` char(1),
		`time_joined` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`phone_number`),
		FOREIGN KEY (`phone_number`) REFERENCES `contacts` (`phone_number`)
	  ) ENGINE=InnoDB";
		
} else { //Create a regular table
		
		$createTableSQL = "CREATE TABLE `$id` (
		`id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
		`message` tinytext NOT NULL,
		`phone_number` char(10) NOT NULL,
		`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`),
		FOREIGN KEY (`phone_number`) REFERENCES `contacts` (`phone_number`)
	  ) ENGINE=InnoDB";
		
}


if ($mysqli->query($createTableSQL) !== TRUE) {
    echo "Error creating hashtag database table";
	exit;
}

$mysqli->close();

//Confirmation and session start
echo "Successfully created $id hashtag";
header("refresh: 2; url=manageHashtags.php");
exit;
 
?>