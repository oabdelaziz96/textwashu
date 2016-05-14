function handleResponse(e) {
  var lock = LockService.getPublicLock();
  lock.waitLock(30000);  // wait 30 seconds before conceding defeat.
   
  try {
    var doc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("hubKey"));
    var sheet = doc.getSheetByName("Texts");
    
    var configSheet = doc.getSheetByName("Config");
    var prefArray = configSheet.getRange(10, 2, 6, 3).getValues();
    var autoTag = prefArray[0][0] == "Yes"; //Not implemented
    var autoReply = prefArray[1][0] == "Yes";
    var untagNotify = prefArray[2][0] == "Yes";
    var removeTag = prefArray[3][0] == "Yes"; //Not implemented
    var autoForward = prefArray[4][0] == "Yes"; //Not implemented
    var checkRegex = prefArray[5][0] == "Yes";
    var responseText = "";

    var nextRow = sheet.getLastRow()+1; // get next row

    var timeStamp = new Date();
    var message = e.parameter["Body"];
    var number = e.parameter["From"].substring(2);
    
    
    //Start of MMS Code ---------------------------------
    
    var mediaExists = e.parameter["NumMedia"] > 0; //check if there is a picture
    
    if (mediaExists) {
      
      var mediaURL = e.parameter["MediaUrl0"];
      message += ("Picture URL: " + mediaURL);
      //sheet.insertImage(mediaURL, 3, nextRow);
      
    }
    
    //End of MMS Code -----------------------------------
    
    var row = [timeStamp, number, message];
    sheet.getRange(nextRow, 1, 1, row.length).setValues([row]);
    
    //Start of Filtering Code ----------------------------------------------
    var allSheets = doc.getSheets();
    var tags = [];
    
    for (i=0; i < allSheets.length; i++) { //Getting all sheet names
      var curSheet = allSheets[i].getSheetName();
      tags[i] = curSheet;
    }
    
    var badSheets = ["Texts", "Contacts", "Config"];
  
    for (i=0; i < badSheets.length; i++) { //Removing Texts, Contacts, and Config sheets from my Arrays
      var badIndexNum = tags.indexOf(badSheets[i]);
      allSheets.splice(badIndexNum, 1);
      tags.splice(badIndexNum, 1);
    }
    
    var tagCheck = !(message.search("#[^ ]+") == -1);
    var filtersExist = tags.length != 0;
    
    if (tagCheck && filtersExist) {
      var messageTag = message.match("(?=#)[^ ]*");
      var indexNum = tags.indexOf(messageTag[0].toLowerCase());
          
      if (indexNum >= 0) {
        var nextTagRow = allSheets[indexNum].getLastRow() + 1;
        allSheets[indexNum].getRange(nextTagRow, 1, 1, 3).setValues([row]);
      }
    }
    
    //End of filtering code ----------------------------------------------
    //Start of contact code ----------------------------------------------
    var contactSheet = doc.getSheetByName("Contacts");
    var lastContact = contactSheet.getLastRow()+1;
    var allContacts = contactSheet.getRange(2, 1, lastContact).getValues().toString();
    var isNew = allContacts.search(number) == -1;
    
    if (isNew) {
     contactSheet.getRange(lastContact, 1).setValue(number); 
    }
    
    //End of contact code ----------------------------------------------
    
    SpreadsheetApp.flush();
    
    //Respond to message
    if (autoReply) {
      responseText+= prefArray[1][1];
    }
    
    if (!tagCheck && untagNotify) {
      responseText+= prefArray[2][1];
    }
    
    if (checkRegex) {
      var testRegex = prefArray[5][1];
      var regexMessage  = prefArray[5][2];
      
      var regCheckFail = message.search(testRegex) == -1;
      
      if (regCheckFail) {
        responseText+= regexMessage;
      }
    }
    
    return xmlHelper(responseText);
    
  } catch (e) {
    handleResponse(e);
    
  } finally { //release lock
    lock.releaseLock();
  }
  
}