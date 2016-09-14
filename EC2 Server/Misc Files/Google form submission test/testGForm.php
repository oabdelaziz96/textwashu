<?php

    //$dataToNode = array("entry.575875228" => "Omar");                                                                    
    //$data_string_to_node = json_encode($dataToNode);
    //$nodeServerURL = 'https://docs.google.com/forms/d/e/1FAIpQLSeQOdw3s1cgsxG4MPOs0fnotkH6eKNOixCAftmfnGNnsdiVKA/formResponse';
    //$ch = curl_init($nodeServerURL);
    //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string_to_node);
    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
    //    'Content-Type: application/json',                                                                                
    //    'Content-Length: ' . strlen($data_string_to_node))                                                                       
    //);
    //curl_exec($ch);
    //curl_close($ch);
    
    $url = 'https://docs.google.com/forms/d/e/1FAIpQLSeQOdw3s1cgsxG4MPOs0fnotkH6eKNOixCAftmfnGNnsdiVKA/formResponse';
    $fields = array(
        'entry.575875228' => urlencode(($_POST['wustl_key']),
        'entry.575873228' => urlencode($_POST['first_name']),
        'entry.5752345228' => urlencode($_POST['title']),
        'entry.5758752234' => urlencode($_POST['institution'])
    );
    
    //url-ify the data for the POST
    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
    rtrim($fields_string, '&');
    
    //open connection
    $ch = curl_init();
    
    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    //execute post
    $result = curl_exec($ch);
    
    //close connection
    curl_close($ch);
    
    echo "Success";


?>