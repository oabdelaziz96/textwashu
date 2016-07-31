<?php

    $dataToNode = array("body" => "hello", "number" => "6306246627", "twilioNumber" => "3142548045");                                                                    
    $data_string_to_node = json_encode($dataToNode);
    $nodeServerURL = 'http://localhost:6543/logout';
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

?>