<?php
// Install the library via PEAR or download the .zip file to your project folder.
// This line loads the library
require 'twilio-php/Services/Twilio.php';

$sid = "ACdc1251a1762be46d5b9e5021d2954f57"; // Your Account SID from www.twilio.com/user/account
$token = "b620065bd7bd2ee1465a22ba5d0dd4ca"; // Your Auth Token from www.twilio.com/user/account

$client = new Services_Twilio($sid, $token);
$message = $client->account->messages->sendMessage(
  '3142548045', // From a valid Twilio number
  '6306246627', // Text this number
  "Hello monkey!"
);

print $message->sid;

?>

