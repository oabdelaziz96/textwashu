<?php
require('twilio-php/Services/Twilio.php'); //Twilio Helper Library
require('HelperFunctions.php'); //Own Helper Library
require('Database.php'); //Initialize Database Access

sendMMS("6306246627", "https://demo.twilio.com/owl.png");

?>