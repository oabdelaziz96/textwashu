<?php

require('HelperFunctions.php');
require('Database.php');

$message = "Hello [first_name] the link is www.google.com/[phone_number]";
echo $message;
echo mergeSmartFields($message, "6306246627", $mysqli);

?>