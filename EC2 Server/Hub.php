<?php
require('twilio-php/Services/Twilio.php'); //Twilio Helper Library
require('HelperFunctions.php');
require('Database.php');

//Sample Data --------- FOR TESTING ONLY
$_REQUEST['Body'] = "Hello";
$_REQUEST['From'] = "+16306216627";
$_REQUEST['NumMedia'] = 0;
//End of sample Data -- FOR TESTING ONLY

//Start of retrieving text message data
$type = "Twilio";
$body = $_REQUEST['Body'];
$number = substr($_REQUEST['From'], 2);
$mediaExists = $_REQUEST['NumMedia'] > 0;
if ($mediaExists) $body .= ("Picture URL: ".$_REQUEST['MediaUrl0']);
//End of retrieving text message data

$responseText = ""; //Initialize variable to hold response data

//-------GET PREFERENCES



//sendToNode($body, $number); //Send Text Message Data to Node Polling Server

$contactExists = contactExists($number, $mysqli); //Check to see if phone number is already in contacts table

if (!$contactExists) {
    addNumberToContacts($number, $mysqli); //Add phone number to contacts table if it isn't already there
}

outputTwilioResponse($responseText); //Responds to Twilio Request in Proper Format

?>