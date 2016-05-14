function getAllTexts() {
  var messages_url = "https://api.twilio.com/2010-04-01/Accounts/AC61c94f6c2b81968a1cf39053629fa3ca/Messages.json";

  var options = {
    "method" : "get",
    "pagesize" : "100"
  };

  options.headers = { 
    "Authorization" : "Basic " + Utilities.base64Encode("AC61c94f6c2b81968a1cf39053629fa3ca:d92455e23a3b893288561b88f1b03b31")
  };

  var response = UrlFetchApp.fetch(messages_url, options);
  var data = JSON.parse(response.getContentText());
  
  var doc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("key"));
  doc.insertSheet("AllTexts");
  var sheet = doc.getSheetByName("AllTexts");
  var nextRow = sheet.getLastRow()+1;
  
  for (i = 0; i < data.messages.length; i++) {
    var body = data.messages[i].body;
    var from = data.messages[i].from;
    var timestamp = data.messages[i].date_created;
    var row = [timestamp, from, body];
    
    sheet.getRange(nextRow, 1, 1, row.length).setValues([row]);
    nextRow++;
    
  }
}