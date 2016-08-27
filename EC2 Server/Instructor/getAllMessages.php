<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
require('../Sensitive/database.php'); //Database Access
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
$mysqli = setMysqlDatabase($number);

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
	$orgMsg = stripslashes(str_replace('\n', " ", htmlspecialchars($orgMsg)));
	$modMsg = stripslashes(str_replace('\n', " ", htmlspecialchars($modMsg)));
	$response = stripslashes(str_replace('\n', " ", htmlspecialchars($response)));
	
	$phoneLink = '<a href="viewContacts.php?search='.urlencode($pNum).'">'.htmlspecialchars($pNum).'</a>';
	$array[$cur] = array($id, $orgMsg, $modMsg, $phoneLink, $response, $source, $time);
	$cur = $cur + 1;
}

$stmt->close();
$mysqli->close();


//Confirmation
 
	echo json_encode(array(
		"success" => true,
		"dataArray" => $array
	));
	exit;
 
?>