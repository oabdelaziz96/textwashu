<?php //NEEEEEEEEEEED BINARY EVERYWHERE
require('../Sensitive/database.php'); //Database Access

ini_set('display_errors', '1'); //display errors for debugging

$shortPath = $_GET['path'];

if( !preg_match('/^([A-z0-9]{4})$/', $shortPath) ){
    $alertMessage = "Invalid URL";
    echo $alertMessage;
    exit;
}

//Connect to participationDB
$mysqli = setMysqlDatabase('participationDB');

//Get Long URL
$stmt = $mysqli->prepare("select long_URL from urlshortner where binary short_path = '$shortPath'");
if(!$stmt){
    $alertMessage = "An unexpected error occured";
    echo $alertMessage;
    exit;
} 
$stmt->execute();
$stmt->bind_result($longURL);
$stmt->fetch();
$stmt->close();

//If long_URL doesn't exist
if (is_null($longURL)) {
    $alertMessage = "Invalid URL";
    echo $alertMessage;
    exit;
}

//Update Hits
$stmt = $mysqli->prepare("update urlshortner set hits=hits+1 where binary short_path=?");
if(!$stmt){
    $alertMessage = "An unexpected error occured";
    echo $alertMessage;
    exit;
} 
$stmt->bind_param('s', $shortPath);
$stmt->execute();
$stmt->close();
$mysqli->close();

//Fix URLs that start in "www"
if (substr($longURL, 0, 4) == "www.") {
    $longURL = "http://".$longURL;
}

//Redirect
header("Location: $longURL");

?>