<head>
<title>Thank you</title>
</head>

<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
//ini_set('display_errors', '1'); //display errors for debugging
session_start();
$classNum = $_SESSION['classNum'];
$className = $_SESSION['className'];
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
$mysqli_class = setMysqlDatabase($classNum);

//Connect to participationDB
$mysqli_partDB = setMysqlDatabase("participationDB");

//Check if student number and authCode match
$stmt = $mysqli_class->prepare("select phone_number from contacts where phone_number = '$studNum' and auth_code = '$authCode'");
if(!$stmt){
        $alertMessage = "An unexpected error (101) occurred";
		echo $alertMessage;
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
$stmt = $mysqli_class->prepare('select status, arg1, arg2 from preferences where number = 14 or number = 15');
if(!$stmt){
        $alertMessage = "An unexpected error (102) occurred";
		echo $alertMessage;
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
if( !preg_match('/^.{1,50}$/', $password) ){
				
    //Check if student already has password
		
		//Check if we already have user info from another class
		$stmt = $mysqli_partDB->prepare("select web_password from contacts where phone_number = '$studNum'");
		if(!$stmt){
				$alertMessage = "An unexpected error (103) occurred";
				echo $alertMessage;
				exit;
		} 
		$stmt->execute();
		$stmt->bind_result($previous_password);
		$stmt->fetch();
		$stmt->close();		
		
		if (is_null($previous_password) || ($previous_password == "")) {
				//If student doesn't have a password, then check enableWeb
				
				if ($enableWeb) { //Check against regex
						$alertMessage = "Invalid password. Password must be between 1 and 50 charcaters long.";
						echo $alertMessage;
						exit;	
				} else { //Leave passord as blank
						$password = "";	
				}		
				
		} else {
				//If student has password, then copy it
				$password = $previous_password;
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
$stmt = $mysqli_class->prepare('update contacts set first_name=?, last_name=?, email=?, wustl_key=?, id_number=?, auth_code=?, profile_url=? where phone_number=?');
if(!$stmt){
        $alertMessage = "An unexpected error (104) occurred";
		echo $alertMessage;
        exit;
}
$stmt->bind_param('ssssssss', $first_name, $last_name, $mysqli_class->real_escape_string($email), $mysqli_class->real_escape_string($wustl_key), $id_number, $authCode, $profileURL, $studNum);
$stmt->execute();
$stmt->close();

//Check if phone number is already in participationDB
$stmt = $mysqli_partDB->prepare("select phone_number from contacts where phone_number = '$studNum'");
if(!$stmt){
        $alertMessage = "An unexpected error (105) occurred";
		echo $alertMessage;
        exit;
} 
$stmt->execute();
$stmt->bind_result($output);
$stmt->fetch();
$contactExists = !is_null($output);
$stmt->close();

if ($contactExists) { //Then update information
		$stmt = $mysqli_partDB->prepare('update contacts set first_name=?, last_name=?, email=?, wustl_key=?, id_number=?, web_password=? where phone_number=?');
		if(!$stmt){
				$alertMessage = "An unexpected error (106) occurred";
				echo $alertMessage;
				exit;
		}
		$stmt->bind_param('sssssss', $first_name, $last_name, $mysqli_partDB->real_escape_string($email), $mysqli_partDB->real_escape_string($wustl_key), $id_number, $password, $studNum);
		$stmt->execute();
		$stmt->close();

} else { //Add contact since they don't exist yet
		$stmt = $mysqli_partDB->prepare("insert into contacts (phone_number, first_name, last_name, email, wustl_key, id_number, web_password) values (?, ?, ?, ?, ?, ?, ?)");
		if(!$stmt){
				$alertMessage = "An unexpected error (107) occurred";
				echo $alertMessage;
				exit;
		}
		$stmt->bind_param('sssssss', $studNum, $first_name, $last_name, $mysqli_partDB->real_escape_string($email), $mysqli_partDB->real_escape_string($wustl_key), $id_number, $password);
		$stmt->execute();
		$stmt->close();
}

//Add student-class row to studwebaccess table if applicable
if ($enableWeb) {
		//Check if user and class have a row
		$stmt = $mysqli_partDB->prepare("select id from studwebaccess where stud_num = '$studNum' and class_num = '$classNum'");
		if(!$stmt){
				$alertMessage = "An unexpected error (108) occurred";
				echo $alertMessage;
				exit;
		}
		$stmt->execute();
		$stmt->bind_result($output);
		$stmt->fetch();
		$needStudWebAccessRow = is_null($output);
		$stmt->close();
		
		if($needStudWebAccessRow) { //Add row since it doesn't exist
				$stmt = $mysqli_partDB->prepare("insert into studwebaccess (stud_num, class_num, class_name) values (?, ?, ?)");
				if(!$stmt){
						$alertMessage = "An unexpected error (109) occurred";
						echo $alertMessage;
						exit;
				}
				$stmt->bind_param('sss', $studNum, $classNum, $className);
				$stmt->execute();
				$stmt->close();
		}	
}

//If reply is requested
if ($replyPref[0] == "On") {
		
		require('../Processing/HelperFunctions.php'); //Get access to helper functions
		require('../HelperFiles/twilio-php/Services/Twilio.php'); //Twilio Helper Library
		
		//Get twilio account information
		$sql = "select twilio_account_sid, twilio_auth_token from accounts where twilio_phone_number = '$classNum'";
		$stmt = $mysqli_partDB->prepare($sql);
		if(!$stmt){
				$alertMessage = "An unexpected error (110) occurred";
				echo $alertMessage;
				exit;
		} 
		$stmt->execute();
		$stmt->bind_result($twilio_sid, $twilio_auth);
		$stmt->fetch();
		$stmt->close();
		
		
		//Send SMS if applicable
		$smsMessgae = mergeSmartFields($replyPref[1], $studNum, $mysqli_class);
		//NEED TO ADD URL SHORTENER IF PREFERENCE IS ENABLED
		sendSMS($studNum, $smsMessgae, $classNum, $twilio_sid, $twilio_auth);
		
		//Send MMS is applicable
		sendMMS($studNum, $replyPref[2], $classNum, $twilio_sid, $twilio_auth);
		
		//Display thank you message
		echo "Thank you! You should receive a confirmation text message shortly.";
		
} else {
		//Display thank you message
		echo "Thank you for completing your profile!";
}

//Close DB connections
$mysqli_class->close();
$mysqli_partDB->close();

//Destroy Session and Exit
session_destroy();
exit;

?>

