<?php
require('twilio-php/Services/Twilio.php'); //Twilio Helper Library
require('HelperFunctions.php'); //Own Helper Library
require('Database.php'); //Initialize Database Access

$phoneNumber = "6306246627";
$message = "http://www.unitedrealtygroupwellington.com/wp-content/uploads/2014/06/4th-of-July-wellington-florida.jpg";

sendMMS($phoneNumber, "");

?>