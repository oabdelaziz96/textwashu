<?php

$data = array("name" => "Robot", "msg" => "Hi guys, I'm a PHP bot !");                                                                    
$data_string = json_encode($data);
echo $data_string;

$ch = curl_init('http://localhost:6543');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
);

echo curl_exec($ch)."\n";
curl_close($ch);
  
    
?>
