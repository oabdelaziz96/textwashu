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
	$editLink = '<a href="editHashtagForm.php?hashtag='.urlencode($id).'&status='.urlencode($status).'&response='.urlencode($response).'">Edit</a>';
	$deleteLink = '<a href="deleteHashtag.php?hashtag='.urlencode($id).'" class="confirmation">Delete</a>';
	$archiveLink = '<a href="editHashtag.php?hashtag='.urlencode($id).'&reply='.urlencode($response).'">Archive</a>';
	$idLink = '<a href="viewHashtagMessages.php?hashtag='.urlencode($id).'">'.htmlspecialchars($id).'</a>';
	
	if ($status == "Active") {
		$smartLink = $archiveLink;
	} else {
		$smartLink = $deleteLink;
	}
	
	$array[$cur] = array($idLink, htmlspecialchars($status), htmlspecialchars($response), $editLink." | ".$smartLink);
	$cur = $cur + 1;
}


//Confirmation
 
	echo json_encode(array(
		"success" => true,
		"dataArray" => $array
	));
	exit;
 
?>