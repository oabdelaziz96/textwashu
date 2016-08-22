<head>
<title>Edit Contact</title>
</head>

<?php
//ini_set('display_errors', '1'); //display errors for debugging
session_start();

if (!isset($_SESSION['number'])) { //Redirect if not logged in
		echo "Log in to enter this area";
		header("refresh: 2; url=login.html");
		exit;
}

$studNum = $_GET['phone_number'];

if( !preg_match('/^[0-9]{10}$/', $studNum) ){
        $alertMessage = "An error occured.";
		echo $alertMessage;
        exit;
}

//Get access to database functions
require('../Sensitive/database.php');

//Connect to this class' database
$mysqli = setMysqlDatabase($_SESSION['number']);

//Check if we already have user info
$stmt = $mysqli->prepare("select first_name, last_name, email, wustl_key, id_number from contacts where phone_number = '$studNum'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email, $wustl_key, $id_number);
$stmt->fetch();
$stmt->close();

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
$enableName = $contactPrefArray["enableName"];
$enableEmail = $contactPrefArray["enableEmail"];
$enableWuKey = $contactPrefArray["enableWuKey"];
$enableID = $contactPrefArray["enableID"];

//Then display fields accordingly
echo "<h1>Edit Contact for $studNum</h1>"; //Course name header and phone number
echo '<form action="editContact.php" method="POST" id="usrForm">'; //form initializtion

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

echo '<input type="hidden" id="stud_num" name="stud_num" value="'.$studNum.'">';

echo '<br><button type="submit">Update</button><a href="viewContacts.php"><input type="button" value="Cancel"/></a>'; //Form submit button

//Exit
exit;

?>

