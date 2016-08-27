<?php
require('../Sensitive/database.php'); //Database Access
//ini_set('display_errors', '1'); //display errors for debugging

//Get variables from sign in page 
$studNum = $_POST['phoneNumber'];
$pwdFromUsr = $_POST['password'];

//Check username
if( !preg_match('/^[0-9]{10}$/', $studNum) ){
        $alertMessage = "Invalid phone number. Please enter your 10 digit phone number without any dashes or spaces.";
		echo $alertMessage;
        exit;
}

//Check password
if( !preg_match('/^.{1,50}$/', $pwdFromUsr) ){
		$alertMessage = "Invalid password. Password must be between 1 and 50 charcaters long.";
		echo $alertMessage;
		exit;
}

//Connect to participationDB
$mysqli = setMysqlDatabase('participationDB');

//Get password in DB if username exists
$sql = "select phone_number, first_name, web_password from contacts where phone_number = '$studNum'";
$stmt = $mysqli->prepare($sql);
if(!$stmt){
		$alertMessage = "An unexpected error (101) occurred";
		echo $alertMessage;
		$mysqli->close();
        exit;
} 
$stmt->execute();
$stmt->bind_result($studNum, $name, $pwdFromDB);
$stmt->fetch();
$stmt->close();

if (is_null($studNum)) { //Phone number not in participationDB contact table
		$alertMessage = "Phone number doesn't exist. Make sure you are enrolled in a class with web access and have completed your profile.";
		echo $alertMessage;
		$mysqli->close();
		exit;
}

if (is_null($pwdFromDB)  || ($pwdFromDB == "")) { //Phone number is in contact table but doesn't have a password
		
		//Check if student has a class with web access
		$stmt = $mysqli->query("select id from studwebaccess where stud_num = '$studNum'");
		if(!$stmt){
				$alertMessage = "An unexpected error (102) occurred";
				echo $alertMessage;
				$mysqli->close();
				exit;
		}
		$numClassesWithWebAccess = $stmt->num_rows;
		$stmt->close();
		
		//If student has web access, then send a one time code to set a password
		if ($numClassesWithWebAccess > 0) {
				
				//Generate 6-digit reset code
				$resetCode = rand(100000, 999999);
				
				//Add reset code to contacts table
				$stmt = $mysqli->prepare('update contacts set reset_code=? where phone_number=?');
				if(!$stmt){
						$alertMessage = "An unexpected error (103) occurred";
						echo $alertMessage;
						$mysqli->close();
						exit;
				}
				$stmt->bind_param('ds', $resetCode, $studNum);
				$stmt->execute();
				$stmt->close();
				
				//Use the first number in the studwebaccess table with the studNum
				$stmt = $mysqli->prepare("select class_num from studwebaccess where stud_num = '$studNum' LIMIT 1");
				if(!$stmt){
						$alertMessage = "An unexpected error (104) occurred";
						echo $alertMessage;
						$mysqli->close();
						exit;
				}
				$stmt->execute();
				$stmt->bind_result($classNum);
				$stmt->fetch();
				$stmt->close();
				
				//Load helper resources
				require('../Processing/HelperFunctions.php'); //Get access to helper functions
				require('../HelperFiles/twilio-php/Services/Twilio.php'); //Twilio Helper Library
				
				//Get twilio account information
				$sql = "select twilio_account_sid, twilio_auth_token from accounts where twilio_phone_number = '$classNum'";
				$stmt = $mysqli->prepare($sql);
				if(!$stmt){
						$alertMessage = "An unexpected error (105) occurred";
						echo $alertMessage;
						$mysqli->close();
						exit;
				} 
				$stmt->execute();
				$stmt->bind_result($twilio_sid, $twilio_auth);
				$stmt->fetch();
				$stmt->close();
				
				
				//Send text message with reset code and reset link
				$smsMessgae = "Your Text WashU web access code is $resetCode. You can also set your password at http://student.textwashu.com/setPassword.php?sN=$studNum&rC=$resetCode";
				$smsMessgae = detectAndShortenURLs($smsMessgae);
				sendSMS($studNum, $smsMessgae, $classNum, $twilio_sid, $twilio_auth);
				
				//Redirect to set password page
				$alertMessage = "It looks like you haven't setup a password yet. Check your text messages for a web access code so you can create a password.";
				echo '<script>alert("'.$alertMessage.'");window.location.href = "setPassword.php?sN='.$studNum.'";</script>';
				$mysqli->close();
				exit;
				
		} else { //If student doesn't have web access, then tell them
				$alertMessage = "You are not currently enrolled in any classes with web access.";
				echo $alertMessage;
				$mysqli->close();
				exit;
		}
}

if($pwdFromDB == crypt($pwdFromUsr,$pwdFromDB)) {//Paswords match
		
		//Check if student has a class with web access
		$stmt = $mysqli->query("select id from studwebaccess where stud_num = '$studNum'");
		if(!$stmt){
				$alertMessage = "An unexpected error (106) occurred";
				echo $alertMessage;
				$mysqli->close();
				exit;
		}
		$numClassesWithWebAccess = $stmt->num_rows;
		$stmt->close();
		
		
		//If student has web access, then start session and send to check classes
		if ($numClassesWithWebAccess > 0) {
				session_start();
				$_SESSION['studName'] = $name;
				$_SESSION['studNumber'] = $studNum;
				header("Location: checkClasses.php");
				$mysqli->close();
				exit;		
		} else {
				$alertMessage = "You are not currently enrolled in any classes with web access.";
				echo $alertMessage;
				$mysqli->close();
				exit;
		}
				
} else {//Passwords don't match
		$alertMessage = "Incorrect password";
		echo $alertMessage;
		$mysqli->close();
		exit;		
}

?>