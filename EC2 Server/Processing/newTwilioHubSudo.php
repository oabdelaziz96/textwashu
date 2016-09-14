<?php
require('../HelperFiles/twilio-php/Services/Twilio.php'); //Twilio Helper Library
require('HelperFunctions.php'); //Own Helper Library
require('../Sensitive/database.php'); //Database Access
//ini_set('display_errors', '1'); //display errors for debugging

//Get Message Data from Twilio
    //Source = Twilio
    //Body
    //Media URL (shortened, if applicable)
    //From Number
    //Twilio Number
    //Twilio SID

//Connect to participationDB

//Authenticate Request

    //If request is from Twilio
    
        //Send message to node (original message, phone number, class number)
    
        //Connect to class database
    
        //Initialize variable to hold response data
        
        //Retrieve preferences
            //Reply to All Texts (SMS and/or MMS)
            //Reply to First Messages (SMS and/or MMS)
            //Add Keyword to All Incoming Texts (Which keyword?) {When turning on preference here, we should make sure that it is an active table. Remove from here if it gets archived/deleted.}
            //Reply to Texts without a Keyword (SMS and/or MMS)
            //Reply to Texts with an Archived Keyword (SMS)
            //Forward All Texts (Phone number to forward to?)
            //Check Regular Expression (Regex, Response)
            //Shorten URLs
            //Remind to Fill Profile
            //Session Information -- Not really a preference but good location here
            //Enable Web Access -- Not needed for Hub
            //Message After Filling Out Profile -- Not needed for Hub
            //Contact Preferences -- Not needed for Hub
            
        //Forward message (APPLY PREFERENCE)
        
        //Check if contact already exists
            //If new contact
                //Add number to contacts
                //First Message (APPLY PREFERENCE)
            
            //If already registered
                //Remind to Fill Profile (APPLY PREFERENCE)
                
        //Identify type of message
            //One letter (w/ or w/o spaces before/after) //Process as keyword only if matches //Don't show in live questions
            
            
            //One word (w/ or w/o spaces before/after) //Process as keyword
            
            
            
            //Other //Process using hashtags as keywords
            
            
            
            //Process "Add Keyword" (APPLY PREFERENCE) (Don't double reply if keyword was already there as one word or hashtag) 
                
        
                
            
        
            
        
        
            
            
            
    
    
    
    
    
        //Close connection to databases
    
    //If request is not from Twilio
        
        //Close connection to participationDB
        //Output ~unauthorized message








?>