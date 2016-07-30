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

//Connect to DB
$mysqli = new mysqli('localhost', 'participationUsr', 'WashU330', "$number");

if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}

$query = 'select id, original_message, modified_message, phone_number, response, source, timestamp from hub';

$stmt = $mysqli->prepare($query);
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => "MySQL Failed"
	));
	exit;
}
 
$stmt->execute();

$stmt->bind_result($id, $orgMsg, $modMsg, $pNum, $response, $source, $time);


$array = array();
$cur = 0;

while($stmt->fetch()) {
	$phoneLink = '<a href="viewContacts?search='.urlencode($pNum).'">'.htmlspecialchars($pNum).'</a>';
	$array[$cur] = array($id, htmlspecialchars($orgMsg), htmlspecialchars($modMsg), $phoneLink, htmlspecialchars($response), $source, $time);
	$cur = $cur + 1;
}


//Confirmation
 
	echo json_encode(array(
		"success" => true,
		"dataArray" => $array
	));
	exit;
 
?>