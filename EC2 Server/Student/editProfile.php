<head>
<title>Student Profile</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="form.css">
</head>

<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
//Destroy any existing session
session_start();
session_destroy();

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
$stmt->bind_result($first_name, $last_name, $email, $wustl_key, $id_number, $password);
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

//Format Number
$formatedNumber = "(".substr($studNum,0,3).") ".substr($studNum,3,3)."-".substr($studNum,6,4);

//Initialize Page Structure
echo '<div class="container"><div class="row"><div class="col-sm-6 col-md-4 col-md-offset-4">';
echo '<h1 class="text-center login-title">'.$courseName." Profile for $formatedNumber".'</h1>';
echo '<div class="account-wall"><img class="profile-img" src="logo.png" alt="">';
echo '<form action="updateProfile.php" method="POST" class="form-signin">';


if ($enableWeb) {
		
		//Check if student already has password
		if (is_null($password) || ($password == "")) {
				//If student doesn't have a password, then create field
				echo '<input type="password" id="password" name="password" class="form-control" placeholder="Password" required><br>';
		} else {
				//If student has password, then hide field
				echo '<input type="hidden" id="password" name="password" class="form-control" required>';
		}
				
} else {
		echo '<input type="hidden" id="password" name="password" class="form-control" required>';
}

if ($enableName) {
		echo '<input type="text" id="first_name" name="first_name" value="'.$first_name.'" class="form-control" placeholder="First Name" required><br>';
		echo '<input type="text" id="last_name" name="last_name" value="'.$last_name.'" class="form-control" placeholder="Last Name" required><br>';
} else { //make it a hidden field
		echo '<input type="hidden" id="first_name" name="first_name" value="'.$first_name.'" class="form-control" required>';
		echo '<input type="hidden" id="last_name" name="last_name" value="'.$last_name.'" class="form-control" required>';
}

if ($enableEmail) {
		echo '<input type="text" id="email" name="email" value="'.$email.'" class="form-control" placeholder="Email" required><br>';
} else { //make it a hidden field
		echo '<input type="hidden" id="email" name="email" value="'.$email.'" class="form-control" required>';
}

if ($enableWuKey) {
		echo '<input type="text" id="wustl_key" name="wustl_key" value="'.$wustl_key.'" class="form-control" placeholder="WUSTL Key" required><br>';
} else { //make it a hidden field
		echo '<input type="hidden" id="wustl_key" name="wustl_key" value="'.$wustl_key.'" class="form-control" required>';
}

if ($enableID) {
		echo '<input type="number" id="id_number" name="id_number" value="'.$id_number.'" class="form-control" placeholder="Student ID Number" required><br>';
} else { //make it a hidden field
		echo '<input type="hidden" id="id_number" name="id_number" value="'.$id_number.'" class="form-control" required>';
}

echo '<button class="btn btn-lg btn-primary btn-block" type="submit">Submit</button>'; //Form submit button
echo '</form></div></div></div></div>'; //Structure Close

//Start session and exit
session_start();
$_SESSION['classNum'] = $classNum;
$_SESSION['className'] = $courseName;
$_SESSION['studNum'] = $studNum;
$_SESSION['authCode'] = $authCode;
exit;

?>



