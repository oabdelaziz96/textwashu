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

$query = 'select id, status, response from hashtags';

$stmt = $mysqli->prepare($query);
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => "MySQL Failed"
	));
	exit;
}
 
$stmt->execute();

$stmt->bind_result($id, $status, $response);


$array = array();
$cur = 0;

while($stmt->fetch()) {
	$response = str_replace("*nL*", "\n", stripslashes($response));
	
	$editLink = '<a href="editHashtagForm.php?hashtag='.urlencode($id).'&status='.urlencode($status).'&response='.urlencode($response).'">Edit</a>';
	$deleteLink = '<a href="deleteHashtag.php?hashtag='.urlencode($id).'" class="confirmation">Delete</a>';
	$archiveLink = '<a href="editHashtag.php?hashtag='.urlencode($id).'&reply='.urlencode($response).'">Archive</a>';
	$idLink = '<a href="viewHashtagMessages.php?hashtag='.urlencode($id).'">'.htmlspecialchars($id).'</a>';
	
	if ($status == "Active") {
		$smartLink = $archiveLink;
	} else {
		$smartLink = $deleteLink;
	}
	
	$array[$cur] = array($idLink, htmlspecialchars($status), $response, $editLink." | ".$smartLink);
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