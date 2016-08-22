<?php
require('../Sensitive/database.php'); //Database Access
session_start();

if (!isset($_SESSION['number'])) { //Redirect if not logged in
		echo "Log in to enter this area";
		header("refresh: 2; url=login.html");
		exit;
}

$studNum = $_POST['stud_num'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$wustl_key = $_POST['wustl_key'];
$id_number = $_POST['id_number'];

if( !preg_match('/^[0-9]{10}$/', $studNum) ){
		$alertMessage = "An error occured.";
		echo $alertMessage;
		exit;
}

$twilioNumber = $_SESSION['number'];

//Connect to this account's database
$mysqli = setMysqlDatabase($twilioNumber);


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

//Result of what professor wants
$contactPrefArray = json_decode($contactPrefArray, true);
$enableName = $contactPrefArray["enableName"];
$enableEmail = $contactPrefArray["enableEmail"];
$enableWuKey = $contactPrefArray["enableWuKey"];
$enableID = $contactPrefArray["enableID"];

//Then check regex accordingly
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


//Update data in class database
$stmt = $mysqli->prepare('update contacts set first_name=?, last_name=?, email=?, wustl_key=?, id_number=? where phone_number=?');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('ssssss', $first_name, $last_name, $mysqli->real_escape_string($email), $mysqli->real_escape_string($wustl_key), $id_number, $studNum);
$stmt->execute();
$stmt->close();
$mysqli->close();

//Confirmation and session start
echo "Successfully edited contact information for $studNum";
header("refresh: 1; url=viewContacts.php");
exit;
 
?>