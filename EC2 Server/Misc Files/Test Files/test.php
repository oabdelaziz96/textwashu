<?php

require('../HelperFiles/twilio-php/Services/Twilio.php'); //Twilio Helper Library
require('../Processing/HelperFunctions.php'); //Own Helper Library
require('../Sensitive/database.php'); //Database Access

$mysqli = setMysqlDatabase('2242523209');

$response = $_POST['arg1'];

//echo strlen($response);

$response = str_replace("\r\n", "*nL*", $response);

//echo $response;

$testIn = $mysqli->real_escape_string($response);

$stmt = $mysqli->prepare('update preferences set arg1=? where number=2');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('s', $testIn);
$stmt->execute();
$stmt->close();
$mysqli->close();



//
$testOut = str_replace("*nL*", "\r\n", stripslashes($testIn));

echo urlencode($testOut);

//echo nl2br($testOut);
//
//echo "hello\nhello";



    
?>