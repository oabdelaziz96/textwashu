<?php

//Sends body and phone number of incoming messages to Node.js Server
function sendToNode($body, $number) {
    $dataToNode = array("body" => $body, "number" => $number);                                                                    
    $data_string_to_node = json_encode($dataToNode);
    $nodeServerURL = 'http://localhost:6543';
    $ch = curl_init($nodeServerURL);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string_to_node);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($data_string_to_node))                                                                       
    );
    curl_exec($ch);
    curl_close($ch);
}

//Gets preferences and returns them in an array
function getPreferences($mysqli) {
    $query = 'select name, status, arg1, arg2 from preferences ORDER BY number ASC';
    $stmt = $mysqli->prepare($query);
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->execute();
    $stmt->bind_result($name, $status, $arg1, $arg2);
    $arr = array();
    while($stmt->fetch()) {
        $arr[] = array($name, $status, $arg1, $arg2);
    }
    $stmt->close();
    return $arr;
}

//Checks to see if contact already exists in contact table and returns a boolean
function contactExists($number, $mysqli) {
    $stmt = $mysqli->prepare("select phone_number from contacts where phone_number = '$number'");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    } 
    $stmt->execute();
    $stmt->bind_result($output);
    $stmt->fetch();
    $result = !is_null($output);
    $stmt->close();
    return $result;
}

//Adds Phone Number into Contacts Table
function addNumberToContacts($number, $mysqli) {
    $stmt = $mysqli->prepare("insert into contacts (phone_number) values (?)");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('s', $number);
    $stmt->execute();
    $stmt->close();
}

//Concatenates response messages appropriately
function addMessage($responseText, $addedMessage) {
  
  $invalid = is_null($addedMessage) || ($addedMessage == "");

  if (!$invalid) {

    if ($responseText == "") {
      $responseText = $addedMessage;
    } else {
      $responseText .= "\n---\n".$addedMessage;
    }
  }
  return $responseText;
}

//Gets hashtags and returns them in an array
function getHashtags($mysqli) {
    $query = 'select id, status, response from hashtags';
    $stmt = $mysqli->prepare($query);
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->execute();
    $stmt->bind_result($id, $status, $response);
    $arr = array();
    while($stmt->fetch()) {
        $arr[] = array($id, $status, $response);
    }
    $stmt->close();
    return $arr;
}

//Adds message to active hashtag table
function addMessageToActiveTable($hashtag, $number, $body, $mysqli) {
    $stmt = $mysqli->prepare('insert into `'.$hashtag.'` (phone_number, message) values (?, ?)');
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('ss', $number, $body);
    $stmt->execute();
    $stmt->close();
}

//Adds phone number to session hashtag table
function addPhoneNumberToSessionTable($hashtag, $number, $mysqli) {
    $stmt = $mysqli->prepare('insert into `'.$hashtag.'` (phone_number) values (?)');
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('s', $number);
    $stmt->execute();
    $stmt->close();
}

//Adds message to session hashtag table
function addMessageToSessionTable($number, $body, $sessionInfo, $mysqli) {
    $tableName = $sessionInfo[2];
    $questionNumber = $sessionInfo[3];
    $answer = strtolower($body);
    $stmt = $mysqli->prepare('UPDATE `'.$tableName.'` SET '.$questionNumber.' = "'.$answer.'" WHERE phone_number = '.$number);
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->execute();
    $stmt->close();
}

//Gets all contact details for given phone number
function getContactInfo($number, $mysqli) {
    $result = $mysqli->query("select * from contacts where phone_number = '$number'");
    if(!$result){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $row = $result->fetch_array(MYSQLI_ASSOC);
    return $row;
}

//Detects and replaces smart fields with values from contacts table
function mergeSmartFields($responseText, $number, $mysqli) {
    //Detect smart fields
    $smartFieldsExist = preg_match_all('/\[([^\]]+)\]/', $responseText, $smartFields);
    
    //If smart fields exist
    if ($smartFieldsExist) {
        
        //Get contact information
        $contactArray = getContactInfo($number, $mysqli);
        $contactKeys = array_keys($contactArray);
    
        //For each smart field in the response
        for($i = 0; $i < count($smartFields[1]); $i++) {
            
            $curField = $smartFields[1][$i];
            
            //Check if smart field is valid
            if (in_array($curField, $contactKeys)) {
                
                //Get contact detail
                $contactDetail = $contactArray[$curField];
                
                //Check if contact detail is null or blank
                if (is_null($contactDetail) || ($contactDetail == "")) {
                    
                    //Check to see if there is a space before and replace space smart field with ""
                    $responseText = preg_replace('/ \['.$curField.'\]/', '', $responseText, -1, $numReplaced);
                    
                    if ($numReplaced == 0) {
                        
                        $responseText = preg_replace('/\['.$curField.'\]/', '', $responseText);
                    
                    }
                    
                } else {
                    
                    //Replace smart field with contact detail
                    $responseText = preg_replace('/\['.$curField.'\]/', $contactDetail, $responseText);
                    
                }
            } //If smart field is not valid, don't do anything
        }
    } 
    return $responseText;
}

//Takes a regular URL and shortens it to the format https://goo.gl/XXXXXXX
function shortenURL($longURL) {
    $APIKey = 'AIzaSyDIv0q20lQ2vvuj6VhaX4f53pghSQ4Lc5U';
    $dataArray= array("longUrl" => $longURL);                                                                    
    $data_string_to_google = json_encode($dataArray);
    $googleServerURL = 'https://www.googleapis.com/urlshortener/v1/url?key='.$APIKey;
    $ch = curl_init($googleServerURL);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string_to_google);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($data_string_to_google))                                                                       
    );
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $response["id"];
}

//Detects and shortens URLs
function detectAndShortenURLs($responseText) {
  $urlRegex = '/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/';
  $urlExists = preg_match_all($urlRegex, $responseText, $URLs);
  
  if ($urlExists) {
    for ($i = 0; $i < count($URLs[0]); $i++) {
      $longURL = $URLs[0][$i];
      $shortURL = shortenURL($longURL);
      
      $responseText = str_replace($longURL, $shortURL, $responseText);
    }
  }
  return $responseText;
}

//Adds message to hub table
function addMessageToHub($number, $body, $type, $responseText, $mysqli) {
    $stmt = $mysqli->prepare("insert into hub (phone_number, message, source, response) values (?, ?, ?, ?)");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('ssss', $number, $body, $type, $responseText);
    $stmt->execute();
    $stmt->close();
}

//Removes hashtags from message
function removeTags($message) {
  $regex = '/#[^ ]+/';
  $message = preg_replace($regex, "", $message);
  $message = trim($message);
  return $message;
}

//Checks to see if phone number already exists in session table and returns a boolean
function numberExistsInSession($number, $hashtag, $mysqli) {
    $stmt = $mysqli->prepare("select phone_number from `".$hashtag."` where phone_number = '$number'");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    } 
    $stmt->execute();
    $stmt->bind_result($output);
    $stmt->fetch();
    $result = !is_null($output);
    $stmt->close();
    return $result;
}

//Sends text message to phone number
function sendSMS($phoneNumber, $message) {
    if (!(is_null($message) || ($message == ""))) {
        $AccountSid = "ACdc1251a1762be46d5b9e5021d2954f57";
        $AuthToken = "b620065bd7bd2ee1465a22ba5d0dd4ca";
        $twilioNumber = "+13142548045";
        $client = new Services_Twilio($AccountSid, $AuthToken);
        $sms = $client->account->messages->sendMessage($twilioNumber, $phoneNumber, $message);
    }
}

//Sends multimedia message to phone number
function sendMMS($phoneNumber, $mediaURL) {
    if (!(is_null($mediaURL) || ($mediaURL == ""))) {
        $AccountSid = "ACdc1251a1762be46d5b9e5021d2954f57";
        $AuthToken = "b620065bd7bd2ee1465a22ba5d0dd4ca";
        $twilioNumber = "+13142548045";
        $client = new Services_Twilio($AccountSid, $AuthToken);
        $sms = $client->account->messages->sendMessage($twilioNumber, $phoneNumber,"", $mediaURL);
    }
}

//Outputs Twilio Response
function outputTwilioResponse($responseText) {
    $response = new Services_Twilio_Twiml();
    if ($responseText !== "") $response->message($responseText);
    print $response;
}

?>