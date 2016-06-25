var mysql = require("mysql");

module.exports = {

  test: function(data) {
    
    // First you need to create a connection to the db
    var con = mysql.createConnection({
      host: "localhost",
      user: "twilio",
      password: "washu123",
      database: "twilio"
    });
    
    con.connect(function(err){
      if(err){
        console.log('Error connecting to Db');
        return;
      }
      console.log('Connection established');
    });
    
    con.query('SELECT * FROM contacts',function(err,rows){
    if(err) throw err;
  
    console.log('Data received from Db:\n');
    console.log(rows);
    });
    
    con.end(function(err) {
      // The connection is terminated gracefully
      // Ensures all previously enqueued queries are still
      // before sending a COM_QUIT packet to the MySQL server.
    });
    
    console.log("test passed!");
      
  },
  
  processSubmission: function(data) {
    
    var responseText = "";
    
    
    
    
    var doc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("hubKey"));
    var sheet = doc.getSheetByName("Texts");
    
    var configSheet = doc.getSheetByName("Config");
    var prefArray = configSheet.getRange(10, 2, 11, 3).getValues();
    var autoTag = prefArray[0][0] == "Yes";
    var autoReply = prefArray[1][0] == "Yes";
    var untagNotify = prefArray[2][0] == "Yes";
    var removeTag = prefArray[3][0] == "Yes";
    var autoForward = prefArray[4][0] == "Yes";
    var checkRegex = prefArray[5][0] == "Yes";
    var firstTimeReply = prefArray[6][0] == "Yes";
    var shortenURLs = prefArray[7][0] == "Yes";
    var invalTagNotify = prefArray[8][0] == "Yes";
    var noteReply = prefArray[9][0] == "Yes";
    var hashText = prefArray[10][0] == "Yes";
  
    var nextRow = sheet.getLastRow()+1; // get next row
  
    var timeStamp = new Date();
    var message = e.parameter["Body"].substring(13);//e.parameter["Body"]; -------- CHANGED FOR STRESS TESTING
    var number = e.parameter["Body"].substring(0, 10);//e.parameter["From"].substring(2); -------- CHANGED FOR STRESS TESTING
    
    //Forward text message if option is enabled
    if (autoForward) sendSMS(prefArray[4][1], "Text from " + number + ": " + message);
    
    //Start of MMS Code ---------------------------------
    
    var mediaExists = e.parameter["NumMedia"] > 0; //check if there is a picture
    
    if (mediaExists) {
      
      var mediaURL = e.parameter["MediaUrl0"];
      message += ("Picture URL: " + URLShortener(mediaURL));
      //sheet.insertImage(mediaURL, 3, nextRow);
      
    }
    
    //End of MMS Code -----------------------------------
    
    var row = [timeStamp, number, message];
    sheet.getRange(nextRow, 1, 1, row.length).setValues([row]);
    
    //Start of contact code ----------------------------------------------
    var contactSheet = doc.getSheetByName("Contacts");
    var lastContact = contactSheet.getLastRow()+1;
    var allContacts = contactSheet.getRange(2, 1, lastContact).getValues().toString();
    var isNew = allContacts.search(number) == -1;
    
    if (isNew) {
      contactSheet.getRange(lastContact, 1).setValue(number);
     
      if (firstTimeReply) responseText = addMessage(responseText, prefArray[6][1]);
    }
    //End of contact code ----------------------------------------------
    
    if (autoReply) responseText = addMessage(responseText, prefArray[1][1]);
    
    //Start of Filtering Code ----------------------------------------------
  
    if (autoTag) message+= (" "+prefArray[0][1]);
    if (hashText) message = "#" + message;
    
    var tagCheck = !(message.search("#[^ ]+") == -1);
    
    if (tagCheck) {
      var allSheets = doc.getSheets();
      var tagSheets = [];
      var tags = [];
      
      for (i=0, index = 0; i < allSheets.length; i++) { //Make array of tag sheets
        var curSheet = allSheets[i];
        var curTag = curSheet.getSheetName();
        var validTagSheet = !(curTag.search("^#[^ ]+$") == -1);
        
        if (validTagSheet) {
            tagSheets[index] = curSheet;
            tags[index] = curTag.toLowerCase();
            index++;
        }
      }
      
      var filtersExist = tags.length != 0;
      
      if (filtersExist) {
        var messageTag = message.match(/(?=#)[^ ]*/g);
        
        for (var i = 0; i < messageTag.length; i++) {
          var indexNum = tags.indexOf(messageTag[i].toLowerCase());
              
          if (indexNum >= 0) { //Add text to tagged sheet if one exists
            var sheetWithTag = tagSheets[indexNum];
            var nextTagRow = sheetWithTag.getLastRow() + 1;
            
            if (removeTag) {
              message = removeTags(message);
            }
            
            row = [timeStamp, number, message];
            sheetWithTag.getRange(nextTagRow, 1, 1, 3).setValues([row]);
            
            var tagResponse = sheetWithTag.getRange("G1").getValue();
            if (tagResponse !== "") responseText = addMessage(responseText, tagResponse);
            
          } else { //Text message did not have a valid tag
            if (invalTagNotify) responseText = addMessage(responseText, prefArray[8][1]);
          }
        }
      }
    }
    //End of filtering code ----------------------------------------------
    
    SpreadsheetApp.flush();
    
    //Respond to message
    
    if (!tagCheck && untagNotify) responseText = addMessage(responseText, prefArray[2][1]);
    
    if (checkRegex) {
      var testRegex = prefArray[5][1];
      var regexMessage  = prefArray[5][2];
      var regCheckFail = message.search(testRegex) == -1;
      if (regCheckFail) responseText = addMessage(responseText, regexMessage);
    }
    
    if (responseText !== "") {
    
      //Replace [] in response text with values from contact sheet
      responseText = mergeInfo(number, responseText);
      
      //Shorten URLs if option is enabled
      if (shortenURLs) responseText = detectAndShortenURLs(responseText);
      
      //Log reply if option is enabled
      if (noteReply) sheet.getRange(nextRow, 3).setNote("Replied with: " + responseText);
      
      sendSMS("6306246627", "RF " + number + ": " + responseText);//sendSMS(number, responseText); -------- CHANGED FOR STRESS TESTING
    
    }
  }
  
};