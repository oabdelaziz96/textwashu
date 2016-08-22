<head>
<title>Student Profile</title>
</head>

<?php
//ini_set('display_errors', '1'); //display errors for debugging
$classNum = $_GET['cN'];
$studNum = $_GET['sN'];
$authCode = $_GET['aC'];

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

//Connect to participationDB
$mysqli = setMysqlDatabase('participationDB');

//Check if class phone number is in the database
$stmt = $mysqli->prepare("select course_name from accounts where twilio_phone_number = '$classNum'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($courseName);
$stmt->fetch();
$courseDoesntExist = is_null($courseName);
$stmt->close();
if($courseDoesntExist) {
		$alertMessage = "Unauthorized Request.";
		echo $alertMessage;
        exit;
}

//Check if we already have user info from another class
$stmt = $mysqli->prepare("select first_name, last_name, email, wustl_key, id_number, web_password from contacts where phone_number = '$studNum'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email, $wustl_key, $id_number);
$stmt->fetch();
$stmt->close();
$mysqli->close();

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
$unAuthorizedRequest = is_null($output);
$stmt->close();
if($unAuthorizedRequest) {
		$alertMessage = "Unauthorized Request. You can only fill out your profle once.";
		echo $alertMessage;
        exit;
}

//Then check what fields the professor wants
$stmt = $mysqli->prepare('select arg1 from preferences where number = "15"');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($contactPrefArray);
$stmt->fetch();
$stmt->close();
$mysqli->close();

//Result of what professor wants
$contactPrefArray = json_decode($contactPrefArray, true);
$enableWeb = $contactPrefArray["enableWeb"];
$enableName = $contactPrefArray["enableName"];
$enableEmail = $contactPrefArray["enableEmail"];
$enableWuKey = $contactPrefArray["enableWuKey"];
$enableID = $contactPrefArray["enableID"];

//Then display fields accordingly
echo "<h1>$courseName</h1><h3>Profile for $studNum:</h3><br>"; //Course name header and phone number
echo '<form action="updateProfile.php" method="POST" id="usrForm">'; //form initializtion

if ($enableWeb) {
		echo 'Web Password<br><input type="password" id="password" name="password"><br><br>';
} else {
		echo '<input type="hidden" id="password" name="password">';
}

if ($enableName) {
		echo 'First Name<br><input type="text" id="first_name" name="first_name" value="'.$first_name.'"><br><br>';
		echo 'Last Name<br><input type="text" id="last_name" name="last_name" value="'.$last_name.'"><br><br>';
} else { //make it a hidden field
		echo '<input type="hidden" id="first_name" name="first_name" value="'.$first_name.'">';
		echo '<input type="hidden" id="last_name" name="last_name" value="'.$last_name.'">';
}

if ($enableEmail) {
		echo 'Email<br><input type="text" id="email" name="email" value="'.$email.'"><br><br>';
} else { //make it a hidden field
		echo '<input type="hidden" id="email" name="email" value="'.$email.'">';
}

if ($enableWuKey) {
		echo 'WUSTL Key<br><input type="text" id="wustl_key" name="wustl_key" value="'.$wustl_key.'"><br><br>';
} else { //make it a hidden field
		echo '<input type="hidden" id="wustl_key" name="wustl_key" value="'.$wustl_key.'">';
}

if ($enableID) {
		echo 'Student ID Number<br><input type="text" id="id_number" name="id_number" value="'.$id_number.'"><br><br>';
} else { //make it a hidden field
		echo '<input type="hidden" id="id_number" name="id_number" value="'.$id_number.'">';
}

echo '<br><button type="submit">Submit</button>'; //Form submit button


//Start session and exit
session_start();
$_SESSION['classNum'] = $classNum;
$_SESSION['studNum'] = $studNum;
$_SESSION['authCode'] = $authCode;
exit;

?>

