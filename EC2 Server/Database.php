<?php
// Content of database.php
 
$mysqli = new mysqli('localhost', 'twilio', 'washu123', 'twilio');
 
if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}
?>