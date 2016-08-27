<?php
require('../HelperFiles/twilio-php/Services/Twilio.php'); //Twilio Helper Library
require('HelperFunctions.php'); //Own Helper Library
require('../Sensitive/database.php'); //Database Access
//ini_set('display_errors', '1'); //display errors for debugging

//Get Message Data from Twilio
    //Type = Twilio
    //Body
    //Media URL (shortened, if applicable)
    //From Number
    //Twilio Number
    //Twilio SID

//Connect to participationDB

//Authenticate Request

    //If request is from Twilio
    
        //Send original message to node
    
        //Connect to class database
    
        //Initialize variable to hold response data
        
        //Retrieve preferences
            //Reply to All Texts (SMS and/or MMS)
            //Reply to First Messages (SMS and/or MMS)
            //Add Keyword to All Incoming Texts (Which keyword?)
            //Reply to Texts without a Keyword (SMS and/or MMS)
            //Reply to Texts with an Archived Keyword (SMS)
            //Forward All Texts (Phone number to forward to?)
            //Check Regular Expression (Regex, Response)
            //Shorten URLs
            //Remind to Fill Profile
            //Session Information -- Not really a preference but here
            //Enable Web Access -- Not needed for Hub
            //Message After Filling Out Profile -- Not needed for Hub
            //Contact Preferences -- Not needed for Hub
            
        
        
            
            
            
    
    
    
    
    
        //Close connection to databases
    
    //If request is not from Twilio
        
        //Close connection to participationDB
        //Output ~unauthorized message








?>