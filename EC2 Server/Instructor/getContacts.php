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

$query = 'select phone_number, first_name, last_name, email, wustl_key, id_number from contacts';

$stmt = $mysqli->prepare($query);
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => "MySQL Failed"
	));
	exit;
}
 
$stmt->execute();

$stmt->bind_result($pNum, $fName, $lName, $email, $wuKey, $idNum);


$array = array();
$cur = 0;

while($stmt->fetch()) {
	$phoneLink = $phoneLink = '<a href="viewAllMessages.php?search='.urlencode($pNum).'">'.htmlspecialchars($pNum).'</a>';
	$array[$cur] = array($phoneLink, htmlspecialchars($fName), htmlspecialchars($lName), htmlspecialchars($email), htmlspecialchars($wuKey), htmlspecialchars($idNum));
	$cur = $cur + 1;
}


//Confirmation
 
	echo json_encode(array(
		"success" => true,
		"dataArray" => $array
	));
	exit;
 
?>