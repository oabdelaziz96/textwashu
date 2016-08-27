<?php
require('../Sensitive/database.php'); //Database Access
//ini_set('display_errors', '1'); //display errors for debugging

//Get variables from sign in page 
$studNum = $_POST['sN'];

//Check phone number
if( !preg_match('/^[0-9]{10}$/', $studNum) ){
        $alertMessage = "Invalid phone number. Please enter your 10 digit phone number without any dashes or spaces.";
		echo $alertMessage;
        exit;
}

//Connect to participationDB
$mysqli = setMysqlDatabase('participationDB');

//Check if student has a class with web access
$stmt = $mysqli->query("select id from studwebaccess where stud_num = '$studNum'");
if(!$stmt){
        $alertMessage = "An unexpected error (101) occurred";
        echo $alertMessage;
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
                $alertMessage = "An unexpected error (102) occurred";
                echo $alertMessage;
                exit;
        }
        $stmt->bind_param('ds', $resetCode, $studNum);
        $stmt->execute();
        $stmt->close();
        
        //Use the first number in the studwebaccess table with the studNum
        $stmt = $mysqli->prepare("select class_num from studwebaccess where stud_num = '$studNum' LIMIT 1");
        if(!$stmt){
                $alertMessage = "An unexpected error (103) occurred";
                echo $alertMessage;
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
                $alertMessage = "An unexpected error (104) occurred";
                echo $alertMessage;
                exit;
        } 
        $stmt->execute();
        $stmt->bind_result($twilio_sid, $twilio_auth);
        $stmt->fetch();
        $stmt->close();
        $mysqli->close();
        
        
        //Send text message with reset code and reset link
        $smsMessgae = "Your Text WashU web reset code is $resetCode. You can also reset your password at http://student.textwashu.com/setPassword.php?sN=$studNum&rC=$resetCode&t=reset";
        $smsMessgae = detectAndShortenURLs($smsMessgae);
        sendSMS($studNum, $smsMessgae, $classNum, $twilio_sid, $twilio_auth);
        
        //Redirect to set password page
        $alertMessage = "Check your text messages for a web reset code so you can create a new password.";
        echo '<script>alert("'.$alertMessage.'");window.location.href = "setPassword.php?sN='.$studNum.'&t=reset";</script>';
        exit;
        
} else { //If student doesn't have web access, then tell them
        $alertMessage = "You are not currently enrolled in any classes with web access.";
        echo $alertMessage;
        exit;
}

?>