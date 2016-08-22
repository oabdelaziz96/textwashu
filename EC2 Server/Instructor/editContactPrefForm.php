<head>
<title>Contact Preferences</title>
<H1>Contact Preferences</H1>
</head>

<?php
//ini_set('display_errors', '1'); //display errors for debugging
session_start();

if (!isset($_SESSION['number'])) { //Redirect if not logged in
		echo "Log in to enter this area";
		header("refresh: 2; url=login.html");
		exit;
}

require('../Sensitive/database.php'); //Database Access

//Connect to this class' database
$mysqli = setMysqlDatabase($_SESSION['number']);

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
echo '<form action="editContactPref.php" method="POST" id="usrForm">'; //form initializtion

if ($enableWeb) {
    echo 'Web Access<br><input type="radio" name="enableWeb" id="true" value="On" checked="checked"> On  <input type="radio" name="enableWeb" id="false" value="Off"> Off<br><br>';
} else {
	echo 'Web Access<br><input type="radio" name="enableWeb" id="true" value="On"> On  <input type="radio" name="enableWeb" id="false" value="Off" checked="checked"> Off<br><br>';
}

if ($enableName) {
	echo 'First & Last Name<br><input type="radio" name="enableName" id="true" value="On" checked="checked"> On  <input type="radio" name="enableName" id="false" value="Off"> Off<br><br>';
} else {
    echo 'First & Last Name<br><input type="radio" name="enableName" id="true" value="On"> On  <input type="radio" name="enableName" id="false" value="Off" checked="checked"> Off<br><br>';
}

if ($enableEmail) {
	echo 'Email<br><input type="radio" name="enableEmail" id="true" value="On" checked="checked"> On  <input type="radio" name="enableEmail" id="false" value="Off"> Off<br><br>';
} else {
    echo 'Email<br><input type="radio" name="enableEmail" id="true" value="On"> On  <input type="radio" name="enableEmail" id="false" value="Off" checked="checked"> Off<br><br>';
}

if ($enableWuKey) {
	echo 'WUSTL Key<br><input type="radio" name="enableWuKey" id="true" value="On" checked="checked"> On  <input type="radio" name="enableWuKey" id="false" value="Off"> Off<br><br>';
} else {
    echo 'WUSTL Key<br><input type="radio" name="enableWuKey" id="true" value="On"> On  <input type="radio" name="enableWuKey" id="false" value="Off" checked="checked"> Off<br><br>';
}

if ($enableID) {
	echo 'Student ID #<br><input type="radio" name="enableID" id="true" value="On" checked="checked"> On  <input type="radio" name="enableID" id="false" value="Off"> Off<br><br>';
} else {
    echo 'Student ID #<br><input type="radio" name="enableID" id="true" value="On"> On  <input type="radio" name="enableID" id="false" value="Off" checked="checked"> Off<br><br>';
}

echo '<button type="submit">Update</button><a href="viewContacts.php"><input type="button" value="Cancel"/></a>'; //Form submit button

exit;

?>