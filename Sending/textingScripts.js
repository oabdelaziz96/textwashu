function sendSms(to, body) {
  var number = "+1"+to;
  
  var messages_url = "https://api.twilio.com/2010-04-01/Accounts/AC61c94f6c2b81968a1cf39053629fa3ca/Messages.json";

  var payload = {
    "To": number,
    "Body" : body,
    "From" : "+13142793219"
  };

  var options = {
    "method" : "post",
    "payload" : payload
  };

  options.headers = { 
    "Authorization" : "Basic " + Utilities.base64Encode("AC61c94f6c2b81968a1cf39053629fa3ca:d92455e23a3b893288561b88f1b03b31")
  };

  UrlFetchApp.fetch(messages_url, options);
}

function sendAll() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName("Messages");
  var startRow = 2; 
  var numRows = sheet.getLastRow() - 1; 
  var dataRange = sheet.getRange(startRow, 1, numRows, 2) 
  var data = dataRange.getValues();

  for (i in data) {
    var row = data[i];
    try {
      response_data = sendSms(row[0], row[1]);
      status = "Sent!";
    } catch(err) {
      Logger.log(err);
      status = "error";
    }
    sheet.getRange(startRow + Number(i), 3).setValue(status);
  }
}

function sendTexts() {
  sendAll();
}

function onOpen(e) {
   SpreadsheetApp.getUi()
       .createMenu('Texting Options')
       .addItem('Send Texts', 'sendTexts')
       .addItem('Load Contacts', 'loadAll')
       .addItem('Load Filter', 'loadFilter')
       .addItem('Clear List', 'removeCurrent')
       .addToUi();
 }

function loadAll() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var contactsSheet = ss.getSheetByName("Contacts");
  var numContacts = contactsSheet.getLastRow()-1;
  var messagesSheet = ss.getSheetByName("Messages");
  var contactsArray = contactsSheet.getRange(2, 1, numContacts, 1).getValues();
  var numbers = messagesSheet.getRange(2, 1, numContacts, 1).setValues(contactsArray);
}

/*
Complete but need to add the data validation to the new cells just created
*/

function removeCurrent() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var messagesSheet = ss.getSheetByName("Messages");
  var numMessages  = messagesSheet.getLastRow();
  messagesSheet.deleteRows(2, numMessages);
  messagesSheet.insertRows(2, numMessages);
  //var dataValRule = messagesSheet.getRange(500, 1).getDataValidation();
  //messagesSheet.getRange(2, 1, numMessages).setDataValidation(dataValRule);
}

function loadFilter() {
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
      var ss = SpreadsheetApp.getActiveSpreadsheet();
      var contactsSheet = ss.getSheetByName("Contacts");
      var messagesSheet = ss.getSheetByName("Messages");
      var impRng = contactsSheet.getRange("A1").getFormula();
      var sheetID = impRng.slice(14,58);
      
      //Getting info from hub
      var ss = SpreadsheetApp.openById(sheetID);
      var hashSheet = ss.getSheetByName(tag);
      var quantity = hashSheet.getLastRow()-1;
      var numbersArray = hashSheet.getRange(2, 2, quantity, 1).getValues();
      
      //filter input
      var helpArray = []
      var filteredArray = [[]];
      var numFiltered = 0;
      for (i=0; i < numbersArray.length; i++) {
        var cur = numbersArray[i][0];
        
        var valid = (helpArray.indexOf(cur) == -1);
        
        Logger.log(cur+" "+valid);
        
        if (valid) {
          
          helpArray[numFiltered] = cur;
          filteredArray[numFiltered] = [cur];
          numFiltered++;
          
        }
        
      }
      
      //Send data back to messages sheet
      var numbersBack = messagesSheet.getRange(2, 1, filteredArray.length, 1).setValues(filteredArray);
      
      
    } else {
      
      ui.alert("Invalid Tag");
    
    }
  } 
  
}