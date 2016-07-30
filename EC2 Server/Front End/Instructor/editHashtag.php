<?php

session_start();

if (!isset($_SESSION['number'])) { //Redirect if not logged in
		echo "Log in to enter this area";
		header("refresh: 2; url=login.html");
		exit;
}

if ($_GET['hashtag'] == "") {//Not a GET request
		
		//Get variables from previous page 
		$id = strtolower($_POST['hashtag']); //Lowercase hashtag off the bat
		$status = $_POST['status'];
		$response = $_POST['reply'];
		
} else { //Is a GET request to archive
		
		$id = $_GET['hashtag'];
		$status = "Archived";
		$response = $_GET['reply'];
}


$twilioNumber = $_SESSION['number'];


// Filter variables
if( !preg_match('/^#[a-zA-Z0-9]{1,19}$/', $id) ){
        $alertMessage =  "Invalid hashtag. Hashtag should consist of letters and numbers only (no spaces or special characters), and must be between 1-19 characters.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^(Active|Archived)$/', $status) ){
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
$mysqli = new mysqli('localhost', 'participationUsr', 'WashU330', "$twilioNumber");

if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}

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
if(is_null($output)) {//hashtag doesn't already exist
        $alertMessage =  "This hashtag doesn't exist.";
        echo $alertMessage;
        exit;
}


//Update hashtag info in hashtags table
$stmt = $mysqli->prepare('update hashtags set status=?, response=? where id=?');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('sss', $status, $mysqli->real_escape_string($response), $id);
$stmt->execute();
$stmt->close();

$mysqli->close();

//Confirmation and session start
echo "Successfully edited $id hashtag";
header("refresh: 1; url=manageHashtags.php");
exit;
 
?>