<?php
require('../Sensitive/database.php'); //Database Access

//Get variables from sign in page 
$username = $_POST['username'];
$pwdFromUsr = $_POST['password'];

//Check username
if( !preg_match('/^[A-Za-z0-9_\-]{4,30}$/', $username) ){
        $alertMessage = "Invalid username. Username should can only have letters, numbers, underscores, and dashes, and must be between 4-30 characters";
		echo $alertMessage;
        exit;
}

//Connect to participationDB
$mysqli = setMysqlDatabase('participationDB');

//Get password in DB if username exists
$sql = "select password, first_name, twilio_phone_number from accounts where username = '$username'";
$stmt = $mysqli->prepare($sql);
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($pwdFromDB, $name, $number);
$stmt->fetch();
$stmt->close();

if (is_null($pwdFromDB)) {//Username doesn't exist
	
		$alertMessage = "Username doesn't exist";
		echo $alertMessage;
		exit;
		
} else {//Username exists
		
		if($pwdFromDB == crypt($pwdFromUsr,$pwdFromDB)) {//Paswords match
				
				session_start();
				$_SESSION['name'] = $name;
				$_SESSION['number'] = $number;
				header("Location: mainMenu.php");
				exit;		
				
		} else {//Passwords don't match
				
				$alertMessage = "Incorrect password";
				echo $alertMessage;
				exit;
				
		}
		
} 

?>