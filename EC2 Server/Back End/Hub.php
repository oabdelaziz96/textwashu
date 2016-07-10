<?php
require('twilio-php/Services/Twilio.php'); //Twilio Helper Library
require('HelperFunctions.php'); //Own Helper Library
require('Database.php'); //Initialize Database Access

//Sample Data --------- FOR TESTING ONLY
$weAreTesting = false;
if ($weAreTesting) {
    $_REQUEST['Body'] = "C";
    $_REQUEST['From'] = "+11234567890";
    $_REQUEST['NumMedia'] = 0;
}
//End of sample Data -- FOR TESTING ONLY

//Start of text message data retrieval
$type = "Twilio";
$body = $_REQUEST['Body'];
$number = substr($_REQUEST['From'], 2); //10 digit phone number
$mediaExists = $_REQUEST['NumMedia'] > 0;
if ($mediaExists) $body .= ("Picture URL: ".$_REQUEST['MediaUrl0']);
//End of text message data retrieval

//Initialize variables to hold response data
$responseText = "";

//Start of preference retrieval
$allPreferences = getPreferences($mysqli);
$autoTagPref = $allPreferences[0];
$autoReplyPref = $allPreferences[1];
$noTagPref = $allPreferences[2];
$arcTagPref = $allPreferences[3];
$invTagPref = $allPreferences[4];
$removeTagPref = $allPreferences[5];
$autoForwardPref = $allPreferences[6];
$checkRegexPref = $allPreferences[7];
$firstTimePref = $allPreferences[8];
$shortenURLPref = $allPreferences[9];
$hashTextPref = $allPreferences[10];
$sessionInfo = $allPreferences[11];
//End of preference retrieval

//Send Text Message Data to Node Polling Server w/o hashtags
sendToNode(removeTags($body), $number);

//Forward Text Message if applicable
if ($autoForwardPref[1] == "On") {
    sendSMS($autoForwardPref[2], "Text from $number: $body");
}

//Check to see if phone number is already in contacts table
$contactExists = contactExists($number, $mysqli);

//If phone number isn't already in contacts table, then add it
//  And if the pref is enabled, reply with a first time message
if (!$contactExists) {
    addNumberToContacts($number, $mysqli);
    
    if ($firstTimePref[1] == "On") { //Send first time message if applicable
        $responseText = addMessage($responseText, $firstTimePref[2]);
        sendMMS($number, $firstTimePref[3]);
    }
}

//Auto reply if applicable
if ($autoReplyPref[1] == "On") {
    $responseText = addMessage($responseText, $autoReplyPref[2]);
    sendMMS($number, $autoReplyPref[3]);
}

//Auto tag if applicable
if ($autoTagPref[1] == "On") {
    $body .= " ".$autoTagPref[2];
}

//Hash message if applicable
if ($hashTextPref[1] == "On") {
    $body = "#".$body;
}

//Start of filtering code
$tagCheck = preg_match_all('/#[^ ]+/', $body, $messageTags);

if ($tagCheck) { //message has a hashtag
    
    $allHashtags = getHashtags($mysqli);
    
    //For each hashtag contained in the message
    for ($curTagNum = 0; $curTagNum < count($messageTags[0]); $curTagNum++) {
        
        $mesHashtagExists = false; //tracker to see if the hashtag ends up matching something
        
        //Compare against each hashtag contained in the database
        for ($i = 0; $i < count($allHashtags); $i++) {
            //Collect hashtag data
            $mesTagName = $messageTags[0][$curTagNum]; //Current hashtag being tested from text message
            $dbTagName = $allHashtags[$i][0]; //Name of hashtag being compared from database
            $dbTagStatus = $allHashtags[$i][1]; //Status of hashtag being compared from database
            $dbTagReply = $allHashtags[$i][2]; //Reply of hashtag being compared from database
            
            if ($mesTagName == $dbTagName) { //If the message and DB hashtags match
                
                switch($dbTagStatus) {
                    //If hashtag is active
                    case "Active":
                        
                        //Check if instructor would like to remove hashtags
                        if ($removeTagPref[1] == "On") { //Yes, remove hashtag
                            
                            //Remove hashtags from message
                            $messageWithoutTags = removeTags($body);
                            
                            //Then add message to specific hash table
                            addMessageToActiveTable($dbTagName, $number, $messageWithoutTags, $mysqli);
                            
                        } else { //No, don't remove hashtag
                            
                            //Add message to specific hash table
                            addMessageToActiveTable($dbTagName, $number, $body, $mysqli);
                            
                        }
                        
                        //Respond with hashtag reply
                        $responseText = addMessage($responseText, $dbTagReply);

                        break;
                    
                    //If hashtag is for a session
                    case "Session":
                    
                        //Check if the phone number is already in the table
                        $numberExistsInSession = numberExistsInSession($number, $dbTagName, $mysqli);
                        
                        if ($numberExistsInSession) {
                            
                            $responseText = addMessage($responseText, 'This hashtag subscribes you to the session, but in this case you are already subscribed. Please only send your one character response to the current question.');
                            
                        } else {
                            
                            //Add number to session table
                            addPhoneNumberToSessionTable($dbTagName, $number, $mysqli);
                            
                            //Respond with hashtag reply
                            $responseText = addMessage($responseText, $dbTagReply);
                            
                        }
                        
                        break;
                    
                    //If hashtag is archived
                    case "Archived":
                        
                        //Send archive reply if applicable
                        if ($arcTagPref[1] == "On") {
                            $responseText = addMessage($responseText, $arcTagPref[2]);
                            sendMMS($number, $arcTagPref[3]);
                        }
                        break;
                
                }
                
                $mesHashtagExists = true;
                
            }   
        }
        
        //If a message contains a hashtag that is not found in the database,
        //  then reply if the invalid tag preference is on
        if (!$mesHashtagExists && $invTagPref[1] == "On") {
            $responseText = addMessage($responseText, $invTagPref[2]);
            sendMMS($number, $invTagPref[3]);
        }
        
        
    }
    
} else { //message does not have a hashtag
    
    //Check if there is an active session
    $sessionIsActive = $sessionInfo[1] == "On";
    
    //Check if response is one character
    $responseIsOneChar = strlen($body) == 1;
    
    if ($sessionIsActive && $responseIsOneChar) {
        
        //Check if the phone number is subscribed to session
        $numberExistsInSession = numberExistsInSession($number, $sessionInfo[2], $mysqli);
        
        if ($numberExistsInSession) {
            
            //Add response to session table
            addMessageToSessionTable($number, $body, $sessionInfo, $mysqli);
            
            //Update boolean to reflect the fact that the message is part of a hashtag
            $tagCheck = true;
            
        }   
    } 
}
//End of filtering code

//Reply if "reply to untagged" is on and message doesn't have a hashtag
if (!$tagCheck && $noTagPref[1] == "On") {
    $responseText = addMessage($responseText, $noTagPref[2]);
    sendMMS($number, $noTagPref[3]);
}

//Check regular expression and reply if applicable
if ($checkRegexPref[1] == "On") {
    
    if (!preg_match($checkRegexPref[2], $body)) {
        $responseText = addMessage($responseText, $checkRegexPref[3]);
    }
}

//Merge smart fields if applicable
$responseText = mergeSmartFields($responseText, $number, $mysqli);

//Shorten URLs in response message if applicable
if ($shortenURLPref[1] == "On") {
    $responseText = detectAndShortenURLs($responseText);
}

addMessageToHub($number, $body, $type, $responseText, $mysqli); //NEED TO CHANGE TO ORIGINAL/MODIFIED MESSAGE

outputTwilioResponse($responseText); //Responds to Twilio Request in Proper Format

?>