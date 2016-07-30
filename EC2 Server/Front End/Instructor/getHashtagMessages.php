<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();

if (isset($_SESSION['number'])) {
	$number = $_SESSION['number'];
} else {
	echo json_encode(array(
		"success" => false,
		"message" => "Not logged in"
	));
	exit;
}

//Get variable from previous page 
$id = strtolower($_POST['hashtag']); //Lowercase hashtag off the bat

// Filter variables
if( !preg_match('/^#[a-zA-Z0-9]{1,19}$/', $id) ){
        $alertMessage =  "Invalid hashtag. Hashtag should consist of letters and numbers only (no spaces or special characters), and must be between 1-19 characters.";
        echo json_encode(array(
		"success" => false,
		"message" => $alertMessage
		));
        exit;
}

//Connect to DB
$mysqli = new mysqli('localhost', 'participationUsr', 'WashU330', "$number");

if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}

//Check to see if hashtag exists
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
        echo json_encode(array(
		"success" => false,
		"message" => $alertMessage
		));
        exit;
}

//Get messages from hashtag table
$query = "select message, phone_number, timestamp from `$id`";

$stmt = $mysqli->prepare($query);
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => "MySQL Failed"
	));
	exit;
}
 
$stmt->execute();

$stmt->bind_result($message, $phone_number, $timestamp);


$array = array();
$cur = 0;

while($stmt->fetch()) {
	$phoneLink = '<a href="viewContacts.php?search='.urlencode($phone_number).'">'.htmlspecialchars($phone_number).'</a>';
	$msgLink = '<a href="viewAllMessages.php?search='.urlencode($message." ".$phone_number." ".$timestamp).'">'.htmlspecialchars($message).'</a>';
	$array[$cur] = array($msgLink, $phoneLink, htmlspecialchars($timestamp));
	$cur = $cur + 1;
}


//Confirmation
 
	echo json_encode(array(
		"success" => true,
		"dataArray" => $array
	));
	exit;
 
?>