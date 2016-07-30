<title>Deleting Hashtag...</title>
<?php

session_start();

if (!isset($_SESSION['number'])) { //Redirect if not logged in
		echo "Log in to enter this area";
		header("refresh: 2; url=login.html");
		exit;
}

//Get variables from previous page 
$id = strtolower(urldecode($_GET['hashtag'])); //Lowercase hashtag off the bat
$twilioNumber = $_SESSION['number'];


// Filter variables
if( !preg_match('/^#[a-zA-Z0-9]{1,19}$/', $id) ){
        $alertMessage =  "Invalid hashtag. Hashtag should consist of letters and numbers only (no spaces or special characters), and must be between 1-19 characters.";
        echo $alertMessage;
        exit;
}

//Connect to this account's database
$mysqli = new mysqli('localhost', 'participationUsr', 'WashU330', "$twilioNumber");

if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}

//Check to see if hashtag already exists
$stmt = $mysqli->prepare("select id from hashtags where id = '$id'");
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
} 
$stmt->execute();
$stmt->bind_result($output);
$stmt->fetch();
$stmt->close();
if(is_null($output)) {//hashtag doesn't already exist
        $alertMessage =  "This hashtag doesn't exist.";
        echo $alertMessage;
        exit;
}


//Update hashtag info in hashtags table
$stmt = $mysqli->prepare('delete from hashtags where id=?');
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
	exit;
}
$stmt->bind_param('s', $id);
$stmt->execute();
$stmt->close();

//Delete specifc hashtag table
if ($mysqli->query("drop table `$id`") !== TRUE) {
    echo "Error deleting hashtag table";
	exit;
}

$mysqli->close();

//Confirmation and session start
echo "Successfully deleted $id hashtag";
header("refresh: 2; url=manageHashtags.php");
exit;
 
?>