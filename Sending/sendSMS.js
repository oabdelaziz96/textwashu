function sendSMS(to, body) {
  //Include +1 in number sent to twilio
  var number = "+1"+to;
  
  //Replace [] in body with values from contact sheet
  body = mergeInfo(to, body);
  
  //Shorten URLs if option is enabled
  var doc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("hubKey"));
  var configSheet = doc.getSheetByName("Config");
  var shortenURLs = configSheet.getRange("B17").getValue() == "Yes";
  if (shortenURLs) body = detectAndShortenURLs(body);

  var messages_url = "https://api.twilio.com/2010-04-01/Accounts/"+SCRIPT_PROP.getProperty("twilioSID")+"/Messages.json";

  var payload = {
    "To": number,
    "Body" : body,
    "From" : SCRIPT_PROP.getProperty("twilioNumber")
  };

  var options = {
    "method" : "post",
    "payload" : payload
  };

  options.headers = { 
    "Authorization" : "Basic " + Utilities.base64Encode(SCRIPT_PROP.getProperty("twilioSID")+":"+SCRIPT_PROP.getProperty("twilioAuth"))
  };

  UrlFetchApp.fetch(messages_url, options);
  return body;
}