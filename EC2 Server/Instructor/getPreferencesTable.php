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

$query = 'select name, description, status, arg1, arg2, arg1_desc, arg2_desc, number from preferences where number <= 11 OR number = 13 OR number = 14;';

$stmt = $mysqli->prepare($query);
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => "MySQL Failed"
	));
	exit;
}
 
$stmt->execute();

$stmt->bind_result($name, $desc, $status, $arg1, $arg2, $arg1_desc, $arg2_desc, $num);


$array = array();
$cur = 0;

while($stmt->fetch()) {
	$arg1withNewLines = str_replace("*nL*", "\n", stripslashes($arg1));
    $arg2withNewLines = str_replace("*nL*", "\n", stripslashes($arg2));
	
	
	$nameLink = '<a href="editPreferenceForm.php?name='.urlencode($name).'&prefDesc='.urlencode($desc).'&status='.urlencode($status).'&arg1='.urlencode($arg1withNewLines).'&arg2='.urlencode($arg2withNewLines);
	$nameLink.= '&arg1Desc='.urlencode(htmlspecialchars($arg1_desc)).'&arg2Desc='.urlencode(htmlspecialchars($arg2_desc)).'&number='.urlencode($num).'">'.$name.'</a>';
	
	$array[$cur] = array($nameLink, htmlspecialchars($status), $arg1withNewLines, $arg2withNewLines);
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