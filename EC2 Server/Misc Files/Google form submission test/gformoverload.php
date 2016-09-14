<?php
    
    $url = 'https://docs.google.com/forms/d/e/1FAIpQLSfn4qBMkHj29XHqn79b6Jor_ownWIygrrNh5A4-rL2FgR2jhw/formResponse';
    
    for ($i = 0; $i < 250; $i++) {
    
        $fields = array(
            'entry.678049845' => urlencode(5), //How do u feel
            'entry.1586295882' => urlencode(5), //wants considered
            'entry.2021140509' => urlencode("No"), //food options restored
            'entry.1464132389' => urlencode("Crepes") //Which one back
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
        
    }
    
    echo "Success 250";


?>