function sendingSendAllSMS() {
  //Get data
  var ss = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("sendTextsKey"));
  var sheet = ss.getSheetByName("Messages");
  var startRow = 2; 
  var numRows = sheet.getLastRow() - 1; 
  var dataRange = sheet.getRange(startRow, 1, numRows, 2);
  var data = dataRange.getValues();
  
  //Get Short URL Preferences
  var doc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("hubKey"));
  var configSheet = doc.getSheetByName("Config");
  var shortenURLs = configSheet.getRange("B17").getValue() == "Yes";
  
  //Message preview and confirmation
  var to = data[0][0];
  var body = data[0][1];
  body = mergeInfo(to, body);
  if (shortenURLs) body = detectAndShortenURLs(body);
  var ui = SpreadsheetApp.getUi();
  var uiResponse = ui.alert("Ready to Send?",
                            "This is what the first text will look like: "+body,
                            ui.ButtonSet.YES_NO);
  
  if (uiResponse == ui.Button.YES) {
    
    //Actual work
    for (var i = 0; i < data.length; i++) {
      try {
        to = data[0][0];
        body = data[0][1];
        
        //Replace [] in body with values from contact sheet
        body = mergeInfo(to, body);
        
        //Shorten URLs if option is enabled
        if (shortenURLs) body = detectAndShortenURLs(body);
    
        sendSMS(to, body);
        var status = "Sent!";
        var note = "Actual message sent: " + body;
      } catch(err) {
        var status = "error";
        var note = "Error: " + err;
      }
      sheet.getRange(startRow + i, 3).setValue(status).setNote(note);
      sheet.getRange("A1").getValue(); //Just to visually see messages as they are sent
    }
  }
}

function sendingSendAllMMS() {
  var ui = SpreadsheetApp.getUi();

  var result = ui.alert(
    'Are you sure you want to send these messages as multimedia messages?',
    'The message field should only include the media URL',
      ui.ButtonSet.YES_NO);

  // Process the user's response.
  var button = result.getSelectedButton();
  
  if (button == ui.Button.YES) {
    var ss = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("sendTextsKey"));
    var sheet = ss.getSheetByName("Messages");
    var startRow = 2; 
    var numRows = sheet.getLastRow() - 1; 
    var dataRange = sheet.getRange(startRow, 1, numRows, 2);
    var data = dataRange.getValues();
  
    for (var i = 0; i < data.length; i++) {
      try {
        response_data = sendMMS(data[i][0], data[i][1]);
        var status = "Sent!";
      } catch(err) {
        var status = "error";
      }
      sheet.getRange(startRow + i, 3).setValue(status);
      sheet.getRange("A1").getValue(); //Just to visually see messages as they are sent
    }
  }
}

function sendingLoadAll() {
  var ss = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("sendTextsKey"));
  var contactsSheet = ss.getSheetByName("Contacts");
  var numContacts = contactsSheet.getLastRow()-1;
  var messagesSheet = ss.getSheetByName("Messages");
  var contactsArray = contactsSheet.getRange(2, 1, numContacts, 1).getValues();
  var numbers = messagesSheet.getRange(2, 1, numContacts, 1).setValues(contactsArray);
}

function sendingRemoveCurrent() {
  var ss = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("sendTextsKey"));
  var messagesSheet = ss.getSheetByName("Messages");
  var numMessages  = messagesSheet.getLastRow();
  messagesSheet.deleteRows(2, numMessages);
  messagesSheet.insertRows(2, numMessages);
  
  var dataVal = SpreadsheetApp.newDataValidation()
                  .requireFormulaSatisfied("=10=LEN(A2)")
                  .setAllowInvalid(false)
                  .setHelpText("Please enter a 10-digit phone number")
                  .build();
  
  ss.getRange("A2:A1000").setDataValidation(dataVal);
}

function sendingLoadFilter() {
  var ui = SpreadsheetApp.getUi();
  
  var result = ui.prompt(
  'Please enter the hashtag you would like to load:',
  'For example, to load #yay, please write: #yay',
  ui.ButtonSet.OK_CANCEL);
  
  // Process the user's response.
  var button = result.getSelectedButton();
  var tag = result.getResponseText();
  var matchCheck = !(tag.search("(^#[^ ]*$)") == -1);
  
  if (button == ui.Button.OK) {
    if (matchCheck) {
      //Getting hub sheet ID
      var ss = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("sendTextsKey"));
      var contactsSheet = ss.getSheetByName("Contacts");
      var messagesSheet = ss.getSheetByName("Messages");
      
      //Getting info from hub
      var ss = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("hubKey"));
      var hashSheet = ss.getSheetByName(tag);
      if (hashSheet == null) {
        ss = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("archiveKey"));
        hashSheet = ss.getSheetByName(tag);
      }
      
      if (hashSheet !== null) {
        var quantity = hashSheet.getLastRow()-1;
        var numbersArray = hashSheet.getRange(2, 2, quantity, 1).getValues();
        
        //filter input
        var helpArray = []
        var filteredArray = [[]];
        var numFiltered = 0;
        for (i=0; i < numbersArray.length; i++) {
          var cur = numbersArray[i][0];
          var valid = (helpArray.indexOf(cur) == -1);
          
          if (valid) {
            helpArray[numFiltered] = cur;
            filteredArray[numFiltered] = [cur];
            numFiltered++; 
          }
        }
        
        //Send data back to messages sheet
        var numbersBack = messagesSheet.getRange(2, 1, filteredArray.length, 1).setValues(filteredArray);
      } else {
        ui.alert("The requested hashtag was not found. Please verify the name and try again");
      }
    } else {
      ui.alert("Your input was not in the correct format");
    }
  }
}