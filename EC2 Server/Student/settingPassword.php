<head>
<title>Setting Password...</title>
</head>

<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
ini_set('display_errors', '1'); //display errors for debugging
$studNum = $_POST['sN'];
$accessCode = $_POST['aC'];
$password = $_POST['password'];

//Filter Variables
if( !preg_match('/^[0-9]{10}$/', $studNum) ){
        $alertMessage = "Unauthorized Request.";
		echo $alertMessage;
        exit;
}

if( !preg_match('/^[0-9]{6}$/', $accessCode) ){
        $alertMessage = "Invalid Access Code";
		echo $alertMessage;
        exit;
}

if( !preg_match('/^.{1,50}$/', $password) ){
				
		$alertMessage = "Invalid password. Password must be between 1 and 50 charcaters long.";
		echo $alertMessage;
		exit;	

} else {
		
		//Encrypt password
		$password = crypt($password,'$1$WQvMDFgI$5.mVOS7V2Q/aB78Mxl23Q1');
		
}

//Get access to database functions
require('../Sensitive/database.php');

//Connect to participationDB
$mysqli = setMysqlDatabase("participationDB");

//Check if student number and accessCode match
$stmt = $mysqli->prepare("select phone_number, first_name from contacts where phone_number = '$studNum' and reset_code = '$accessCode'");
if(!$stmt){
        $alertMessage = "An unexpected error (101) occurred";
		echo $alertMessage;
        exit;
} 
$stmt->execute();
$stmt->bind_result($studNum, $name);
$stmt->fetch();
$unauthorizedRequest = is_null($studNum);
$stmt->close();
if($unauthorizedRequest) {
		$alertMessage = "Phone number and access code do not match. Access code is only good for one password reset.";
		echo $alertMessage;
        exit;
}

//Clear reset code
$resetCode = "";

//Change password
$stmt = $mysqli->prepare('update contacts set reset_code=?, web_password=? where phone_number=?');
if(!$stmt){
		$alertMessage = "An unexpected error (102) occurred";
		echo $alertMessage;
		exit;
}
$stmt->bind_param('sss', $resetCode, $password, $studNum);
$stmt->execute();
$stmt->close();
$mysqli->close();

//Start session and take to checkClasses
session_start();
$_SESSION['studName'] = $name;
$_SESSION['studNumber'] = $studNum;
header("Location: checkClasses.php");
exit;

?>

