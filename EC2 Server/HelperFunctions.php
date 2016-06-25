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
      $responseText .= "/n --- /n".$addedMessage;
    }
  }
  return $responseText;
}

//Outputs Twilio Response
function outputTwilioResponse($responseText) {
    $response = new Services_Twilio_Twiml();
    if ($responseText !== "") $response->message($responseText);
    print $response;
}

?>