<?php
require_once('../../TextWashU/HelperFiles/twilio-php/Services/Twilio.php'); //Twilio Helper Library
require('../Sensitive/database.php'); //Database Access
//ini_set('display_errors', '1'); //display errors for debugging

//Get variables from previous page 
$firstName = $_POST['first_name'];
$lastName = $_POST['last_name'];
$courseName = $_POST['course_name'];
$username = $_POST['username'];
$pwd = $_POST['password'];
$email = $_POST['email'];
$twilioNumber = $_POST['twilio_phone_number'];
$twilioSID = $_POST['twilio_account_sid'];
$twilioAuth = $_POST['twilio_auth_token'];


// Filter variables
if( !preg_match('/^[A-Za-z ]{1,30}$/', $firstName) ){
        $alertMessage =  "Invalid first name. First name should consist of letters and spaces only (no numbers), and must be between 1-30 characters.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^[A-Za-z ]{1,30}$/', $lastName) ){
        $alertMessage =  "Invalid last name. Last name should consist of letters and spaces only (no numbers), and must be between 1-30 characters.";
        echo $alertMessage;
        exit;
}

if( !preg_match('/^[A-Za-z 0-9]{1,30}$/', $courseName) ){
        $alertMessage =  "Invalid course name. Course name should consist of letters, numbers, and spaces only, and must be between 1-30 characters.";
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
$mysqli = setMysqlDatabase('participationDB');

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
		$alertMessage = "Username already taken";
		echo $alertMessage;
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
		$alertMessage = "Twilio phone number already registered";
		echo $alertMessage;
        exit;
}

//Check if course name is already in the database
$stmt = $mysqli->prepare("select course_name from accounts where course_name = '$courseName'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($output);
$stmt->fetch();
$courseNameAlreadyExists = !is_null($output);
$stmt->close();
if($courseNameAlreadyExists) {
		$alertMessage = "Course name already registered. Please pick a different course name.";
		echo $alertMessage;
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
$smsURL = "http://processing.textwashu.com/TwilioHub.php";
$voiceURL = "http://processing.textwashu.com/TwilioCalls.xml";
$twilioClientNumber->update(array(
        "SmsUrl" => $smsURL,
		"VoiceUrl" => $voiceURL
    )); //Known bug -> If number has a TwiML assigned to it, it will not be overridden


//Insert user info into account table
$stmt = $mysqli->prepare("insert into accounts (first_name, last_name, course_name, username, email, password, twilio_phone_number, twilio_account_sid, twilio_auth_token, nodeCode) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$autoGenNodeCode = substr(uniqid(),0,10);
$stmt->bind_param('ssssssssss', $firstName, $lastName, $courseName, $username, $email, $pwd, $twilioNumber, $twilioSID, $twilioAuth, $autoGenNodeCode);
$stmt->execute();
$stmt->close();
$mysqli->close();

//Connect to mysql
$mysqli = connectToMysql();

//Create database with twilio phone number as name
$createDBsql = "CREATE DATABASE `$twilioNumber`";

if ($mysqli->query($createDBsql) !== TRUE) {
    echo "Error creating database";
	exit;
}

$mysqli->close();

//Connect to this account's database
$mysqli = setMysqlDatabase($twilioNumber);

//Create contacts table
$createTableSQL = 'CREATE TABLE `contacts` (
  `phone_number` char(10) NOT NULL,
  `first_name` varchar(30) DEFAULT NULL,
  `last_name` varchar(30) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `wustl_key` varchar(20) DEFAULT NULL,
  `id_number` char(6) DEFAULT NULL,
  `auth_code` char(10) NOT NULL,
  `profile_url` tinytext NOT NULL,
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
  `arg1_active` enum("On","Off"),
  `arg1_regex` tinytext,
  `arg1_regex_msg` tinytext,
  `arg2` tinytext,
  `arg2_desc` tinytext,
  `arg2_active` enum("On","Off"),
  `arg2_regex` tinytext,
  `arg2_regex_msg` tinytext,
  PRIMARY KEY (`number`)
) ENGINE=InnoDB';

if ($mysqli->query($createTableSQL) !== TRUE) {
    echo "Error creating preferences database table";
	exit;
}

//Define JSON Encoded Contact Preference Array
$contactPrefJSON = '{"enableWeb":true,"enableName":true,"enableEmail":true,"enableWuKey":true,"enableID":true}';

//Insert default preference rows
$insertIntoPrefSQL = "INSERT INTO `preferences` (`number`, `name`, `description`, `status`, `arg1`, `arg1_desc`, `arg1_active`, `arg1_regex`, `arg1_regex_msg`, `arg2`, `arg2_desc`, `arg2_active`, `arg2_regex`, `arg2_regex_msg`) VALUES
(1, 'Tag Incoming Texts', 'Automatically inserts a hashtag at the end of every incoming text', 'Off', NULL, 'Hashtag to insert after a user''s message', 'On', '^#[a-zA-Z0-9]{1,19}$', 'Invalid hashtag. Hashtag should consist of letters and numbers only (no spaces or special characters), and must be between 1-19 characters.', NULL, '2nd argument not accepted', 'Off', '^$', '2nd argument not accepted'),
(2, 'Auto Reply', 'Automatically replies to every incoming text message', 'On', 'Thank you for your message!', 'The text that you would like to respond with', 'On', '^.{0,255}$', 'Message must be under 255 characters', NULL, 'The URL of any media you would like to send (such as a picture)', 'On', 'URLREGEX', 'Invalid URL'),
(3, 'Reply to Untagged Texts', 'Automatically replies with message for all incoming texts without a hashtag', 'On', 'You can only get credit when you have a hashtag at the end of your message', 'The text that you would like to respond with', 'On', '^.{0,255}$', 'Message must be under 255 characters', NULL, 'The URL of any media you would like to send (such as a picture)', 'On', 'URLREGEX', 'Invalid URL'),
(4, 'Reply to Archived Tag', 'Automatically replies with message for any incoming texts that references an archived hashtag', 'Off', NULL, 'The text that you would like to respond with', 'On', '^.{0,255}$', 'Message must be under 255 characters', NULL, 'The URL of any media you would like to send (such as a picture)', 'On', 'URLREGEX', 'Invalid URL'),
(5, 'Reply to Invalid Hashtag', 'Automatically replies to any incoming text message that has an invalid (not being filtered or archived) hashtag', 'Off', NULL, 'The text that you would like to respond with', 'On', '^.{0,255}$', 'Message must be under 255 characters', NULL, 'The URL of any media you would like to send (such as a picture)', 'On', 'URLREGEX', 'Invalid URL'),
(6, 'Remove Tags after Processing', 'All hashtags are removed from incoming texts after they are appropriately filtered', 'Off', NULL, '1st argument not accepted', 'Off', '^$', '1st argument not accepted', NULL, '2nd argument not accepted', 'Off', '^$', '2nd argument not accepted'),
(7, 'Auto Forward Texts', 'Automatically forwards all incoming text messages to the provided phone number', 'Off', NULL, 'Phone number to forward messages to', 'On', '^[0-9]{10}$', 'Phone number should be exactly 10-digits long and not include any non-digit characters.', NULL, '2nd argument not accepted', 'Off', '^$', '2nd argument not accepted'),
(8, 'Check Regular Expression', 'Checks all incoming texts against the provided regular expression and sends a given message to messages that don''t match', 'Off', NULL, 'Regular Expression', 'On', '^.{0,255}$', 'Message must be under 255 characters', NULL, 'Response for messages that don''t match the regular expression', 'On', '^.{0,255}$', 'Message must be under 255 characters'),
(9, 'First Time Message', 'Sends a special message to people who text in for the first time (ex. Welcome message)', 'On', 'Welcome to $courseName! Please take a moment to fill out your profile: [profile_url]', 'The text that you would like to respond with', 'On', '^.{0,255}$', 'Message must be under 255 characters', NULL, 'The URL of any media you would like to send (such as a picture)', 'On', 'URLREGEX', 'Invalid URL'),
(10, 'Shorten URLs', 'Uses google URL shortener to shorten all URLs in the text message to a URL in the form of http://goo.gl/xxxxxxx', 'On', NULL, '1st argument not accepted', 'Off', '^$', '1st argument not accepted', NULL, '2nd argument not accepted', 'Off', '^$', '2nd argument not accepted'),
(11, 'Hashtag Text Message', 'This will insert a hashtag (#) to the beginning of all incoming texts. This enables you to filter for specific responses and act accordingly', 'Off', NULL, '1st argument not accepted', 'Off', '^$', '1st argument not accepted', NULL, '2nd argument not accepted', 'Off', '^$', '2nd argument not accepted'),
(12, 'Is Session Active?', 'Not a preference but this table is a good place to store this', 'Off', NULL, 'The hashtag corresponding to the session', NULL, NULL, NULL, NULL, 'The question # that is currently active', NULL, NULL, NULL),
(13, 'Remind to fill profile?', 'Replies with a message reminding users that haven''t filled out their profile to do so, every time they send in a message.', 'On', 'Looks like you still haven''t filled out your profile... please do so now so that we know who you are: [profile_url]', 'Message that you would like to send', 'On', '^.{0,255}$', 'Message must be under 255 characters', '', '2nd argument not accepted', 'Off', '^$', '2nd argument not accepted'),
(14, 'Reply After Filling Out Profile', 'Send a text or picture to a user to confirm that they have successfully filled out their contact profile', 'On', 'Thanks [first_name]! Your profile is now complete', 'The text that you would like to respond with', 'On', '^.{0,255}$', 'Message must be under 255 characters', NULL, 'The URL of any media you would like to send (such as a picture)', 'On', 'URLREGEX', 'Invalid URL'),
(15, 'Contact Field Preferences', 'Not viewable as a regular preference', 'Off', '$contactPrefJSON', 'JSON encoded array of contact preferences', NULL, NULL, NULL, NULL, '2nd argument not accepted', NULL, NULL, NULL)";


if ($mysqli->query($insertIntoPrefSQL) !== TRUE) {
    echo "Error inserting default preferences";
	exit;
}


//Update preferences with URL Regex
$newUrlRegex = $mysqli->real_escape_string("^(?=.{1,255}$)(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})$|^$");
$stmt = $mysqli->prepare('update preferences set arg2_regex=? where arg2_regex="URLREGEX"');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('s', $newUrlRegex);
$stmt->execute();
$stmt->close();
$mysqli->close();


//Confirmation and session start
session_start();
$_SESSION['name'] = $firstName;
$_SESSION['number'] = $twilioNumber;
echo "You're all set $firstName! Taking you to the main menu...";
header("refresh: 2; url=mainMenu.php");
exit;
 
?>