<?php
require('../HelperFiles/twilio-php/Services/Twilio.php'); //Twilio Helper Library
require('HelperFunctions.php'); //Own Helper Library
require('../Sensitive/database.php'); //Database Access
//ini_set('display_errors', '1'); //display errors for debugging

//Sample Data --------- FOR TESTING ONLY
$weAreTesting = false;
if ($weAreTesting) {
    $_REQUEST['Body'] = "C";
    $_REQUEST['From'] = "+11234567890";
    $_REQUEST['To'] = "+16302503186";
    $_REQUEST['AccountSid'] = "ACdc1251a1762be46d5b9e5021d2954f57";
    $_REQUEST['NumMedia'] = 0;
}
//End of sample Data -- FOR TESTING ONLY

//Start of text message data retrieval
$type = "Twilio";
$body = $_REQUEST['Body'];
$fromNumber = substr($_REQUEST['From'], 2); //10 digit phone number of person who sent message
$twilioNumber = substr($_REQUEST['To'], 2); //10 digit Twilio phone number that message was sent to
$twilioAccountSID = $_REQUEST['AccountSid'];
$mediaExists = $_REQUEST['NumMedia'] > 0;
if ($mediaExists) $body = "Text: ".$body." --- Media URL: www.".shortenURL($_REQUEST['MediaUrl0']);
//End of text message data retrieval

//Authenticate Request
$mysqli = setMysqlDatabase('participationDB');
$authorizedReq = authenticateRequest($mysqli, $fromNumber, $twilioNumber, $twilioAccountSID);

if ($authorizedReq[0]) { //request is authorized
    $twilioAuthToken = $authorizedReq[1];
    $mysqli->close(); //close previous db connection
    $mysqli = setMysqlDatabase($twilioNumber);
    
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
    $remindProfilePref = $allPreferences[12];
    //End of preference retrieval
    
    //Send Text Message Data to Node Polling Server w/o hashtags
    sendToNode(removeTags($body), $fromNumber, $twilioNumber);
    
    //Forward Text Message if applicable
    if ($autoForwardPref[1] == "On") {
        sendSMS($autoForwardPref[2], "Text from $fromNumber: $body", $twilioNumber, $twilioAccountSID, $twilioAuthToken);
    }
    
    //Check to see if phone number is already in contacts table
    $contactExists = contactExists($fromNumber, $mysqli);
    
    //If phone number isn't already in contacts table, then add it
    //  And if the pref is enabled, reply with a first time message
    if (!$contactExists[0]) {
        addNumberToContacts($twilioNumber, $fromNumber, $mysqli);
        
        if ($firstTimePref[1] == "On") { //Send first time message if applicable
            $responseText = addMessage($responseText, $firstTimePref[2]);
            sendMMS($fromNumber, $firstTimePref[3], $twilioNumber, $twilioAccountSID, $twilioAuthToken);
        }
    }
    
    //Auto reply if applicable and not a new contact
    if ($autoReplyPref[1] == "On" && $contactExists[0]) {
        $responseText = addMessage($responseText, $autoReplyPref[2]);
        sendMMS($fromNumber, $autoReplyPref[3], $twilioNumber, $twilioAccountSID, $twilioAuthToken);
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
                $mesTagName = strtolower($messageTags[0][$curTagNum]); //Current hashtag being tested from text message.. Lower case though
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
                                addMessageToActiveTable($dbTagName, $fromNumber, $messageWithoutTags, $mysqli);
                                
                            } else { //No, don't remove hashtag
                                
                                //Add message to specific hash table
                                addMessageToActiveTable($dbTagName, $fromNumber, $body, $mysqli);
                                
                            }
                            
                            //Respond with hashtag reply
                            $responseText = addMessage($responseText, $dbTagReply);
    
                            break;
                        
                        //If hashtag is for a session
                        case "Session":
                        
                            //Check if the phone number is already in the table
                            $numberExistsInSession = numberExistsInSession($fromNumber, $dbTagName, $mysqli);
                            
                            if ($numberExistsInSession) {
                                
                                $responseText = addMessage($responseText, 'This hashtag subscribes you to the session, but in this case you are already subscribed. Please only send your one character response to the current question.');
                                
                            } else {
                                
                                //Add number to session table
                                addPhoneNumberToSessionTable($dbTagName, $fromNumber, $mysqli);
                                
                                //Respond with hashtag reply
                                $responseText = addMessage($responseText, $dbTagReply);
                                
                            }
                            
                            break;
                        
                        //If hashtag is archived
                        case "Archived":
                            
                            //Send archive reply if applicable
                            if ($arcTagPref[1] == "On") {
                                $responseText = addMessage($responseText, $arcTagPref[2]);
                                sendMMS($fromNumber, $arcTagPref[3], $twilioNumber, $twilioAccountSID, $twilioAuthToken);
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
                sendMMS($fromNumber, $invTagPref[3], $twilioNumber, $twilioAccountSID, $twilioAuthToken);
            }
            
            
        }
        
    } else { //message does not have a hashtag
        
        //Check if there is an active session
        $sessionIsActive = $sessionInfo[1] == "On";
        
        //Check if response is one character
        $responseIsOneChar = strlen($body) == 1;
        
        if ($sessionIsActive && $responseIsOneChar) {
            
            //Check if the phone number is subscribed to session
            $numberExistsInSession = numberExistsInSession($fromNumber, $sessionInfo[2], $mysqli);
            
            if ($numberExistsInSession) {
                
                //Add response to session table
                addMessageToSessionTable($fromNumber, $body, $sessionInfo, $mysqli);
                
                //Update boolean to reflect the fact that the message is part of a hashtag
                $tagCheck = true;
                
            }   
        } 
    }
    //End of filtering code
    
    //Reply if "reply to untagged" is on and message doesn't have a hashtag
    if (!$tagCheck && $noTagPref[1] == "On") {
        $responseText = addMessage($responseText, $noTagPref[2]);
        sendMMS($fromNumber, $noTagPref[3], $twilioNumber, $twilioAccountSID, $twilioAuthToken);
    }
    
    //Check regular expression and reply if applicable
    if ($checkRegexPref[1] == "On") {
        
        if (!preg_match('/'.$checkRegexPref[2].'/', $body)) {
            $responseText = addMessage($responseText, $checkRegexPref[3]);
        }
    }
    
    //Remind user to fill out profile if applicable
    if ($remindProfilePref[1] == "On" && $contactExists[0]) {
        
        //Check if profile has been filled out
        if ($contactExists[1] == "n") { //first letter of auth_code is "n"
            $responseText = addMessage($responseText, $remindProfilePref[2]); 
        }
    }
    
    //Merge smart fields if applicable
    $responseText = mergeSmartFields($responseText, $fromNumber, $mysqli);
    
    //Shorten URLs in response message if applicable
    if ($shortenURLPref[1] == "On") {
        $responseText = detectAndShortenURLs($responseText);
    }
    
    addMessageToHub($fromNumber, $_REQUEST['Body'], $body, $type, $responseText, $mysqli);
    
    outputTwilioResponse($responseText); //Responds to Twilio Request in Proper Format
    
} else { //request is not authorized
    
    outputTwilioResponse("It appears like this is an unauthorized request, please contact the system administrator for assistance.");
    
}

?>