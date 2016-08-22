<head>
<title>Thank you</title>
</head>

<?php
ini_set('display_errors', '1'); //display errors for debugging
session_start();
$classNum = $_SESSION['classNum'];
$studNum = $_SESSION['studNum'];
$authCode = $_SESSION['authCode'];
$password = $_POST['password'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$wustl_key = $_POST['wustl_key'];
$id_number = $_POST['id_number'];

//Filter Variables
if( !preg_match('/^[0-9]{10}$/', $classNum) ){
        $alertMessage = "Unauthorized Request.";
		echo $alertMessage;
        exit;
}

if( !preg_match('/^[0-9]{10}$/', $studNum) ){
        $alertMessage = "Unauthorized Request.";
		echo $alertMessage;
        exit;
}

if( !preg_match('/^[A-z0-9]{10}$/', $authCode) ){
        $alertMessage = "Unauthorized Request.";
		echo $alertMessage;
        exit;
}

//Get access to database functions
require('../Sensitive/database.php');

//Connect to this class' database
$mysqli = setMysqlDatabase($classNum);

//Check if student number and authCode match
$stmt = $mysqli->prepare("select phone_number from contacts where phone_number = '$studNum' and auth_code = '$authCode'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($output);
$stmt->fetch();
$unauthorizedRequest = is_null($output);
$stmt->close();
if($unauthorizedRequest) {
		$alertMessage = "Unauthorized Request. You can only fill out your profle once.";
		echo $alertMessage;
        exit;
}

//Then check what fields the professor wants and get reply preferences
$stmt = $mysqli->prepare('select status, arg1, arg2 from preferences where number = 14 or number = 15');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($profPrefStatus, $profPrefArg1, $profPrefArg2);
$curRound = 14;
while($stmt->fetch()) {
		if ($curRound == 14) { //Pref 14
				$replyPref = array($profPrefStatus, stripslashes($profPrefArg1), stripslashes($profPrefArg2));
				$curRound++;
		} else { //Pref 15
				$contactPrefArray = $profPrefArg1;
		}
}
$stmt->close();

//Result of what professor wants
$contactPrefArray = json_decode($contactPrefArray, true);
$enableWeb = $contactPrefArray["enableWeb"];
$enableName = $contactPrefArray["enableName"];
$enableEmail = $contactPrefArray["enableEmail"];
$enableWuKey = $contactPrefArray["enableWuKey"];
$enableID = $contactPrefArray["enableID"];

//Then check regex accordingly
if( !preg_match('/^.{1,50}$/', $password) ){ //figure out regex
		
		if ($enableWeb) {//we're actually looking for this field
				$alertMessage = "Invalid password. Password must be between 1 and 50 charcaters long.";
				echo $alertMessage;
				exit;
		} else {
				$password = "";
		}
		

} else {
		
		//Encrypt password
		$password = crypt($password,'$1$WQvMDFgI$5.mVOS7V2Q/aB78Mxl23Q1');
		
}

if( !preg_match('/^[A-Za-z ]{1,30}$/', $first_name) ){
		
		if ($enableName) {//we're actually looking for this field
				$alertMessage = "Invalid first name. First name should consist of letters and spaces only (no numbers), and must be between 1-30 characters.";
				echo $alertMessage;
				exit;
		
		} else {
				$first_name = "";
		}
		
}

if( !preg_match('/^[A-Za-z -]{1,30}$/', $last_name) ){

		if ($enableName) {//we're actually looking for this field
				$alertMessage = "Invalid last name. Last name should consist of letters, dashes, and spaces only (no numbers), and must be between 1-30 characters.";
				echo $alertMessage;
				exit;
		
		} else {
				$last_name = "";
		}

}

if( !preg_match('/^(?=.{4,50}$)[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email) ){
		
		if ($enableEmail) {//we're actually looking for this field
				$alertMessage = "Invalid email address. Email address must be valid and between 4-50 characters";
				echo $alertMessage;
				exit;
		
		} else {
				$email = "";
		}		
		
}

if( !preg_match('/^[A-Za-z-0-9_.]{1,20}$/', $wustl_key) ){  //update
		
		if ($enableWuKey) {//we're actually looking for this field
				$alertMessage = "Invalid WUSTL key. WUSTL key must consist only of letters, numbers, dashes, underscores, and periods, and must be less than 20 characters.";
				echo $alertMessage;
				exit;
		
		} else {
				$wustl_key = "";
		}		
		
}

if( !preg_match('/^4[0-9]{5}$/', $id_number) ){ 
		
		if ($enableID) {//we're actually looking for this field
				$alertMessage = "Invalid ID Number. ID number should be exactly 6 digits long and start with a 4";
				echo $alertMessage;
				exit;
		
		} else {
				$id_number = "";
		}		
		
}

//Generate new Auth Code and Profile Link
$authCode = "f".substr(uniqid(),0,9);
$profileURL = "http://student.textwashu.com/editProfile.php?cN=$classNum&sN=$studNum&aC=$authCode";

//Update data in class database
$stmt = $mysqli->prepare('update contacts set web_password=?, first_name=?, last_name=?, email=?, wustl_key=?, id_number=?, auth_code=?, profile_url=? where phone_number=?');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('sssssssss', $password, $first_name, $last_name, $mysqli->real_escape_string($email), $mysqli->real_escape_string($wustl_key), $id_number, $authCode, $profileURL, $studNum);
$stmt->execute();
$stmt->close();
$mysqli->close();

//Update data in participationDB
$mysqli = setMysqlDatabase("participationDB");

//Check if phone number is already there
$stmt = $mysqli->prepare("select phone_number from contacts where phone_number = '$studNum'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($output);
$stmt->fetch();
$contactExists = !is_null($output);
$stmt->close();

if ($contactExists) { //Then update information
		$stmt = $mysqli->prepare('update contacts set first_name=?, last_name=?, email=?, wustl_key=?, id_number=? where phone_number=?');
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('ssssss', $first_name, $last_name, $mysqli->real_escape_string($email), $mysqli->real_escape_string($wustl_key), $id_number, $studNum);
		$stmt->execute();
		$stmt->close();

} else { //Add contact since they don't exist yet
		$stmt = $mysqli->prepare("insert into contacts (phone_number, first_name, last_name, email, wustl_key, id_number) values (?, ?, ?, ?, ?, ?)");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('ssssss', $studNum, $first_name, $last_name, $mysqli->real_escape_string($email), $mysqli->real_escape_string($wustl_key), $id_number);
		$stmt->execute();
		$stmt->close();
		
}

//If reply is requested
if ($replyPref[0] == "On") {
		
		require('../Processing/HelperFunctions.php'); //Get access to helper functions
		require('../HelperFiles/twilio-php/Services/Twilio.php'); //Twilio Helper Library
		
		//Get twilio account information
		$sql = "select twilio_account_sid, twilio_auth_token from accounts where twilio_phone_number = '$classNum'";
		$stmt = $mysqli->prepare($sql);
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		} 
		$stmt->execute();
		$stmt->bind_result($twilio_sid, $twilio_auth);
		$stmt->fetch();
		$stmt->close();
		$mysqli->close();
		
		//Connect to this class' database
		$mysqli = setMysqlDatabase($classNum);
		
		//Send SMS if applicable
		$smsMessgae = mergeSmartFields($replyPref[1], $studNum, $mysqli);
		$mysqli->close();
		sendSMS($studNum, $smsMessgae, $classNum, $twilio_sid, $twilio_auth);
		
		//Send MMS is applicable
		sendMMS($studNum, $replyPref[2], $classNum, $twilio_sid, $twilio_auth);
		
}



//Destroy Session and Exit
session_destroy();
echo "Thank you! You should receive a confirmation text message shortly.";
exit;

?>

