<?php
require('../Sensitive/database.php'); //Database Access
session_start();
ini_set('display_errors', '1'); //display errors for debugging

if (!isset($_SESSION['number'])) { //Redirect if not logged in
		echo "Log in to enter this area";
		header("refresh: 2; url=login.html");
		exit;
}

$twilioNumber = $_SESSION['number'];
		
//Get message from previous page 
$message = $_POST['arg1'];

//Replace new line html with new line symbol
$message = str_replace("\r\n", "*nL*", $message);

//Filter arg1
if( !preg_match('/^.{1,1000}$/', $message) ){ //Argument 1 has invalid regex
        $alertMessage =  "Invalid message. Message should be between 1 and 1000 characters.";
        echo $alertMessage;
        exit;
}

$message = str_replace("*nL*", "\n", $message);

//Connect to this paticipation database
$mysqli = setMysqlDatabase("participationDB");

//Get twilio account information
$sql = "select twilio_account_sid, twilio_auth_token from accounts where twilio_phone_number = '$twilioNumber'";
$stmt = $mysqli->prepare($sql);
if(!$stmt){
        $alertMessage = "An unexpected error (101) occurred";
        echo $alertMessage;
        exit;
} 
$stmt->execute();
$stmt->bind_result($twilio_sid, $twilio_auth);
$stmt->fetch();
$stmt->close();
$mysqli->close();


//Load helper resources
require('../Processing/HelperFunctions.php'); //Get access to helper functions
require('../HelperFiles/twilio-php/Services/Twilio.php'); //Twilio Helper Library

//Connect to this account's database
$mysqli = setMysqlDatabase($twilioNumber);

//Get data from contacts table
$query = 'select phone_number from contacts';

$stmt = $mysqli->prepare($query);
if(!$stmt){
	echo json_encode(array(
		"success" => false,
		"message" => "MySQL Failed"
	));
	exit;
}
 
$stmt->execute();

$stmt->bind_result($pNum);

$numArray = array();

while($stmt->fetch()) {

        array_push($numArray, $pNum);

}

$stmt->close();

for ($i = 0; $i < count($numArray); $i++) {
    //Merge smart fields
    $newMsg = mergeSmartFields($message, $numArray[$i], $mysqli);
    
    //NEED TO ADD URL SHORTENER IF PREFERENCE IS ENABLED
    $newMsg = detectAndShortenURLs($newMsg);
    
    //Send text
    sendSMS($numArray[$i], $newMsg, $twilioNumber, $twilio_sid, $twilio_auth);
}

$mysqli->close();

//Confirmation and session start
echo "Successfully sent all messages";
header("refresh: 2; url=mainMenu.php");
exit;
 
?>