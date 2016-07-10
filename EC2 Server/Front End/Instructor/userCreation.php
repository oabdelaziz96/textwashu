<?php
require_once('twilio-php/Services/Twilio.php'); //Twilio Helper Library

//Get variables from previous page 
$firstName = $_POST['first_name'];
$lastName = $_POST['last_name'];
$username = $_POST['username'];
$pwd = $_POST['password'];
$email = $_POST['email'];
$twilioNumber = $_POST['twilio_phone_number'];
$twilioSID = $_POST['twilio_account_sid'];
$twilioAuth = $_POST['twilio_auth_token'];


// Filter variables
if( !preg_match('/^[A-Za-z ]{1,30}$/', $firstName) ){
        $alertMessage =  "Invalid first name. First name should consist of letters and spaces only (no spaces/numbers), and must be between 1-30 characters.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^[A-Za-z ]{1,30}$/', $lastName) ){
        $alertMessage =  "Invalid last name. Last name should consist of letters and spaces only (no spaces/numbers), and must be between 1-30 characters.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^[A-Za-z0-9_\-]{4,30}$/', $username) ){
        $alertMessage = "Invalid username. Username should can only have letters, numbers, underscores, and dashes, and must be between 4-30 characters";
		echo $alertMessage;
        exit;
}

if( !preg_match('/^(?=.{4,50}$)[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email) ){
        $alertMessage = "Invalid email address. Email address must be valid and between 4-50 characters";
		echo $alertMessage;
        exit;
}

if( !preg_match('/^[0-9]{10}$/', $twilioNumber) ){
        $alertMessage = "Invalid Twilio number. Twilio phone number should be exactly 10-digits long and not include any non-digit characters.";
		echo $alertMessage;
        exit;
}

//Encrypt password
$pwd = crypt($pwd,'$1$WQvMDFgI$5.mVOS7V2Q/aB78Mxl13Q1');

//Connect to participationDB
$mysqli = new mysqli('localhost', 'participationUsr', 'WashU330', 'participationDB');
 
if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}

//Check if username already exists
$stmt = $mysqli->prepare("select username from accounts where username = '$username'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($output);
$stmt->fetch();
$usernameAlreadyExists = !is_null($output);
$stmt->close();
if($usernameAlreadyExists) {
   echo "Username already taken";
	header("refresh: 2; url=createAccount.html");
	exit; 
}
	
//Check if twilio phone number is already in the database
$stmt = $mysqli->prepare("select twilio_phone_number from accounts where twilio_phone_number = '$twilioNumber'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($output);
$stmt->fetch();
$twilioNumberAlreadyExists = !is_null($output);
$stmt->close();
if($twilioNumberAlreadyExists) {
   echo "Twilio phone number already registered";
	header("refresh: 2; url=createAccount.html");
	exit; 
}

//Get sid for twilio phone number
$twilioServicesClient = new Services_Twilio($twilioSID, $twilioAuth);

try {

    foreach ($twilioServicesClient->account->incoming_phone_numbers->getIterator(0, 50, array(
            "PhoneNumber" => "+1$twilioNumber"
        )) as $number
    ) {
        $twilioNumberSID = $number->sid;
    }

    if (is_null($twilioNumberSID)) {
            echo "Twilio Phone Number is not registered to the given Twilio account";
            exit;
    }

} catch (Services_Twilio_RestException $e) {
    echo 'Invalid Twilio Account SID and/or Auth Token. Visit <a target="_blank" href="https://www.twilio.com/console">www.twilio.com/console</a> to obtain your Account SID (starts with "AC") and your Auth Token';
    exit;
}

//Set SMS webhook to Twilio Hub server
$twilioClientNumber = $twilioServicesClient->account->incoming_phone_numbers->get($twilioNumberSID);
$smsURL = "http://ec2-52-91-18-209.compute-1.amazonaws.com/~oabdelaziz/participation/Hub.php";
$twilioClientNumber->update(array(
        "SmsUrl" => $smsURL
    )); //Known bug -> If number has a TwiML assigned to it, it will not be overridden


//Insert user info into account table
$stmt = $mysqli->prepare("insert into accounts (first_name, last_name, username, email, password, twilio_phone_number, twilio_account_sid, twilio_auth_token) values (?, ?, ?, ?, ?, ?, ?, ?)");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('ssssssss', $firstName, $lastName, $username, $pwd, $email, $twilioNumber, $twilioSID, $twilioAuth);
$stmt->execute();
$stmt->close();
$mysqli->close();

//Connect to mysql
$mysqli = new mysqli('localhost', 'participationUsr', 'WashU330');

if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}

//Create database with twilio phone number as name
$createDBsql = "CREATE DATABASE `$twilioNumber`";

if ($mysqli->query($createDBsql) !== TRUE) {
    echo "Error creating database";
	exit;
}

$mysqli->close();

//Connect to this account's database
$mysqli = new mysqli('localhost', 'participationUsr', 'WashU330', "$twilioNumber");

if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}

//Create contacts table
$createTableSQL = 'CREATE TABLE `contacts` (
  `phone_number` char(10) NOT NULL,
  `web_password` varchar(50) DEFAULT NULL,
  `first_name` varchar(30) DEFAULT NULL,
  `last_name` varchar(30) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `wustl_key` varchar(20) DEFAULT NULL,
  `id_number` char(6) DEFAULT NULL,
  PRIMARY KEY (`phone_number`)
) ENGINE=InnoDB';

if ($mysqli->query($createTableSQL) !== TRUE) {
    echo "Error creating contacts database table";
	exit;
}

//Create hub table
$createTableSQL = 'CREATE TABLE `hub` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `original_message` tinytext NOT NULL,
  `modified_message` tinytext NOT NULL,
  `phone_number` char(10) NOT NULL,
  `response` tinytext,
  `source` enum("Twilio","Web") NOT NULL DEFAULT "Twilio",
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `phone_number` (`phone_number`),
  FOREIGN KEY (`phone_number`) REFERENCES `contacts` (`phone_number`)
) ENGINE=InnoDB';

if ($mysqli->query($createTableSQL) !== TRUE) {
    echo "Error creating hub database table";
	exit;
}

//Create hashtags table
$createTableSQL = 'CREATE TABLE `hashtags` (
  `id` varchar(20) NOT NULL,
  `status` enum("Active","Session","Archived") NOT NULL DEFAULT "Archived",
  `response` tinytext,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB';

if ($mysqli->query($createTableSQL) !== TRUE) {
    echo "Error creating hashtags database table";
	exit;
}

//Create preferences table
$createTableSQL = 'CREATE TABLE `preferences` (
  `number` tinyint(3) unsigned NOT NULL,
  `name` varchar(35) NOT NULL,
  `description` tinytext,
  `status` enum("On","Off") NOT NULL DEFAULT "Off",
  `arg1` tinytext,
  `arg1_desc` tinytext,
  `arg2` tinytext,
  `arg2_desc` tinytext,
  PRIMARY KEY (`number`)
) ENGINE=InnoDB';

if ($mysqli->query($createTableSQL) !== TRUE) {
    echo "Error creating preferences database table";
	exit;
}

//Insert default preference rows
$insertIntoPrefSQL = "INSERT INTO `preferences` (`number`, `name`, `description`, `status`, `arg1`, `arg1_desc`, `arg2`, `arg2_desc`) VALUES
(1, 'Tag Incoming Texts', 'Automatically inserts a hashtag at the end of every incoming text', 'Off', NULL, 'Hashtag to insert after a user''s message', NULL, '2nd argument not accepted'),
(2, 'Auto Reply', 'Automatically replies to every incoming text message', 'On', 'Thank you for your message!', 'The text that you would like to respond with', NULL, 'The URL of any media you would like to send (such as a picture)'),
(3, 'Reply to Untagged Texts', 'Automatically replies with message for all incoming texts without a hashtag', 'On', 'You can only get credit when you have a hashtag at the end of your message', 'The text that you would like to respond with', NULL, 'The URL of any media you would like to send (such as a picture)'),
(4, 'Reply to Archived Tag', 'Automatically replies with message for any incoming texts that references an archived hashtag', 'Off', NULL, 'The text that you would like to respond with', NULL, 'The URL of any media you would like to send (such as a picture)'),
(5, 'Reply to Invalid Hashtag', 'Automatically replies to any incoming text message that has an invalid (not being filtered or archived) hashtag', 'Off', NULL, 'The text that you would like to respond with', NULL, 'The URL of any media you would like to send (such as a picture)'),
(6, 'Remove Tags after Processing', 'All hashtags are removed from incoming texts after they are appropriately filtered', 'Off', NULL, '1st argument not accepted', NULL, '2nd argument not accepted'),
(7, 'Auto Forward Texts', 'Automatically forwards all incoming text messages to the provided phone number', 'Off', NULL, 'Phone number to forward messages to', NULL, '2nd argument not accepted'),
(8, 'Check Regular Expression', 'Checks all incoming texts against the provided regular expression and sends a given message to messages that don''t match', 'Off', NULL, 'Regular Expression', NULL, 'Response for messages that don''t match the regular expression'),
(9, 'First Time Message', 'Sends a special message to people who text in for the first time (ex. Welcome message)', 'Off', NULL, 'The text that you would like to respond with', NULL, 'The URL of any media you would like to send (such as a picture)'),
(10, 'Shorten URLs', 'Uses google URL shortener to shorten all URLs in the text message to a URL in the form of http://goo.gl/xxxxxxx', 'On', NULL, '1st argument not accepted', NULL, '2nd argument not accepted'),
(11, 'Hashtag Text Message', 'This will insert a hashtag (#) to the beginning of all incoming texts. This enables you to filter for specific responses and act accordingly', 'Off', NULL, '1st argument not accepted', NULL, '2nd argument not accepted'),
(12, 'Is Session Active?', 'Not a preference but this table is a good place to store this', 'Off', NULL, 'The hashtag corresponding to the session', NULL, 'The question # that is currently active')";

if ($mysqli->query($insertIntoPrefSQL) !== TRUE) {
    echo "Error inserting default preferences";
	exit;
}

$mysqli->close();


//Confirmation and session start
session_start();
$_SESSION['twilioNumber'] = $twilioNumber;
echo "Successfully Created New User and Logged In";
//header("refresh: 2; url=mainmenu.php");
exit;
 
?>