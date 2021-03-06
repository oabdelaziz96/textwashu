<?php

//Authenticates request and returns array containing a boolean and auth token
function authenticateRequest($mysqli, $fromNumber, $twilioNumber, $twilioAccountSID) {
    
    //Check for valid From Number
    if(!preg_match('/^[0-9]{10}$/', $fromNumber) ){
        return array(false);
    }
    
    //Check for valid Twilio Number
    if(!preg_match('/^[0-9]{10}$/', $twilioNumber) ){
        return array(false);
    }
    
    //Check for valid Twilio SID
    if(!preg_match('/^AC[a-zA-z0-9]{32}$/', $twilioAccountSID) ){
        return array(false);
    }
    
    //Check in DB
    $sql = "select twilio_auth_token from accounts where twilio_phone_number = '$twilioNumber' and twilio_account_sid = '$twilioAccountSID'";
    $stmt = $mysqli->prepare($sql);
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    } 
    $stmt->execute();
    $stmt->bind_result($output);
    $stmt->fetch();
    $stmt->close();
    
    if (is_null($output)) {
        return array(false);
    } else {
        return array(true, $output);
    }
    
}

//Sends body and phone number of incoming messages to Node.js Server 
function sendToNode($body, $number, $twilioNumber) {
    $dataToNode = array("body" => $body, "number" => $number, "twilioNumber" => $twilioNumber);                                                                    
    $data_string_to_node = json_encode($dataToNode);
    $nodeServerURL = 'http://localhost:6543/newMessage';
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
    $query = 'select name, status, arg1, arg2 from preferences where number < 14 ORDER BY number ASC';
    $stmt = $mysqli->prepare($query);
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->execute();
    $stmt->bind_result($name, $status, $arg1, $arg2);
    $arr = array();
    while($stmt->fetch()) {
        $arg1withNewLines = str_replace("*nL*", "\n", stripslashes($arg1));
        $arg2withNewLines = str_replace("*nL*", "\n", stripslashes($arg2));
        $arr[] = array($name, $status, $arg1withNewLines, $arg2withNewLines);
    }
    $stmt->close();
    return $arr;
}

//Checks to see if contact already exists in contact table and returns an array with a boolean and the first letter of the auth_code if applicable
function contactExists($number, $mysqli) {
    $stmt = $mysqli->prepare("select auth_code from contacts where phone_number = '$number'");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    } 
    $stmt->execute();
    $stmt->bind_result($output);
    $stmt->fetch();
    $stmt->close();
    
    if (is_null($output)) {
        return array(false);
    } else {
        return array(true, substr($output, 0, 1));
    }

}

//Adds Phone Number into Contacts Table
function addNumberToContacts($classNum, $number, $mysqli) {
    
    //Generate auth_code and profile_url
    $authCode = "n".substr(uniqid(),0,9);
    $profileURL = "http://student.textwashu.com/editProfile.php?cN=$classNum&sN=$number&aC=$authCode";
    
    $stmt = $mysqli->prepare("insert into contacts (phone_number, auth_code, profile_url) values (?, ?, ?)");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('sss', $mysqli->real_escape_string($number), $authCode, $profileURL);
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
        $responseWithNewLines = str_replace("*nL*", "\n", stripslashes($response));
        $arr[] = array($id, $status, $responseWithNewLines);
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
    $stmt->bind_param('ss', $mysqli->real_escape_string($number), $mysqli->real_escape_string($body));
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
    $stmt->bind_param('s', $mysqli->real_escape_string($number));
    $stmt->execute();
    $stmt->close();
}

//Adds message to session hashtag table
function addMessageToSessionTable($number, $body, $sessionInfo, $mysqli) {
    $tableName = $sessionInfo[2];
    $questionNumber = $sessionInfo[3];
    $answer = strtolower($body);
    $stmt = $mysqli->prepare('UPDATE `'.$tableName.'` SET '.$questionNumber.' = "'.$mysqli->real_escape_string($answer).'" WHERE phone_number = '.$mysqli->real_escape_string($number));
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

//Takes a regular URL and shortens it to the format textwashu.com/XXXX
function shortenURL($longURL) {    
    //Connect to participationDB
    $newMysqli = setMysqlDatabase('participationDB');
    
    //Lookup longURL in urlshortner table (to check if URL has been previously shortned)
    $stmt = $newMysqli->prepare("select short_path from urlshortner where binary long_URL = '$longURL'");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $newMysqli->error);
        exit;
    } 
    $stmt->execute();
    $stmt->bind_result($shortPath);
    $stmt->fetch();
    $stmt->close();			
    
    if (is_null($shortPath)) { //If URL hasn't already been shortened
        //Generate new shortPath
        $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        for($i=0; $i<4; $i++) $shortPath .= $charset[(mt_rand(0,(strlen($charset)-1)))];
        
        //Check if shortPath already exists
        $failDuplicateShortPathTest = true;
        
        while ($failDuplicateShortPathTest) {
            $stmt = $newMysqli->prepare("select short_path from urlshortner where binary short_path = '$shortPath'");
            if(!$stmt){
                printf("Query Prep Failed: %s\n", $newMysqli->error);
                exit;
            } 
            $stmt->execute();
            $stmt->bind_result($shortPathAlreadyExists);
            $stmt->fetch();
            $stmt->close();
            
            if (is_null($shortPathAlreadyExists)) { //shortPath doesn't already exist
                $failDuplicateShortPathTest = false; //exit loop
            }
        }
        
        //Insert shortPath into table
        $stmt = $newMysqli->prepare("insert into urlshortner (short_path, long_URL, hits) values (?, ?, ?)");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $newMysqli->error);
			exit;
		}
        $hits = 0;
		$stmt->bind_param('ssi', $shortPath, $newMysqli->real_escape_string($longURL), $hits);
		$stmt->execute();
		$stmt->close();

    } else {} //If URL has been already shortened, then skip this
    
    $newMysqli->close();
    return "textwashu.com/".$shortPath;
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
function addMessageToHub($number, $orgMessage, $modMessage, $type, $responseText, $mysqli) {
    $stmt = $mysqli->prepare("insert into hub (phone_number, original_message, modified_message, source, response) values (?, ?, ?, ?, ?)");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('sssss', $mysqli->real_escape_string($number), $mysqli->real_escape_string($orgMessage), $mysqli->real_escape_string($modMessage), $type, $mysqli->real_escape_string($responseText));
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
function sendSMS($phoneNumber, $message, $twilioNumber, $twilioAccountSid, $twilioAuth) { 
    if (!(is_null($message) || ($message == ""))) {
        $client = new Services_Twilio($twilioAccountSid, $twilioAuth);
        
        try {
            
            $sms = $client->account->messages->sendMessage($twilioNumber, $phoneNumber, $message);
        
        } catch (Services_Twilio_RestException $e) {} //wasn't able to send SMS   
    }
}

//Sends multimedia message to phone number
function sendMMS($phoneNumber, $mediaURL, $twilioNumber, $twilioAccountSid, $twilioAuth) { 
    if (!(is_null($mediaURL) || ($mediaURL == ""))) {//If $mediaURL actually has a valude
        
        $urlRegex = '/^(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})$/';
        $urlExists = preg_match($urlRegex, $mediaURL);
  
        if ($urlExists) {//If $mediaURL is actually a URL
        
            $client = new Services_Twilio($twilioAccountSid, $twilioAuth);
            
            try {
            
                $mms = $client->account->messages->sendMessage($twilioNumber, $phoneNumber,"", $mediaURL);
            
            } catch (Services_Twilio_RestException $e) {} //wasn't able to send MMS
        
        }
    }
}

//Outputs Twilio Response
function outputTwilioResponse($responseText) {
    $response = new Services_Twilio_Twiml();
    if ($responseText !== "") $response->message($responseText);
    print $response;
}

?>