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

//Then check what fields the professor wants
$stmt = $mysqli->prepare('select arg1 from preferences where number = "15"');
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => "MySQL Failed"
	));
	exit;
} 
$stmt->execute();
$stmt->bind_result($contactPrefJSON);
$stmt->fetch();
$stmt->close();

//Result of what professor wants
$contactPrefArray = json_decode($contactPrefJSON, true);
$enableName = $contactPrefArray["enableName"];
$enableEmail = $contactPrefArray["enableEmail"];
$enableWuKey = $contactPrefArray["enableWuKey"];
$enableID = $contactPrefArray["enableID"];


//Get data from contacts table
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
	$phoneLink = '<a href="viewAllMessages.php?search='.urlencode($pNum).'">'.htmlspecialchars($pNum).'</a>';
	$editLink = '<a href="editContactForm.php?phone_number='.urlencode($pNum).'">'."Edit".'</a>';
	$deleteLink = '<a href="deleteContact.php?phone_number='.urlencode($pNum).'" class="confirmation">'."Delete".'</a>';
	
	$array[$cur] = array($phoneLink);
	
	if ($enableName) {
		array_push($array[$cur], htmlspecialchars($fName));
		array_push($array[$cur], htmlspecialchars($lName));
	}
	
	if ($enableEmail) {
		array_push($array[$cur], htmlspecialchars($email));
	}
	
	if ($enableWuKey) {
		array_push($array[$cur], htmlspecialchars($wuKey));
	}
	
	if ($enableID) {
		array_push($array[$cur], htmlspecialchars($idNum));
	}
	
	array_push($array[$cur], $editLink." | ".$deleteLink); //Options
	
	$cur = $cur + 1;
}

//while($stmt->fetch()) {
//	$phoneLink = $phoneLink = '<a href="viewAllMessages.php?search='.urlencode($pNum).'">'.htmlspecialchars($pNum).'</a>';
//	$array[$cur] = array($phoneLink, htmlspecialchars($fName), htmlspecialchars($lName), htmlspecialchars($email), htmlspecialchars($wuKey), htmlspecialchars($idNum));
//	$cur = $cur + 1;
//}

$stmt->close();
$mysqli->close();

//Confirmation
 
echo json_encode(array(
	"success" => true,
	"dataArray" => $array,
	"preferences" => $contactPrefArray
));
exit;
 
?>