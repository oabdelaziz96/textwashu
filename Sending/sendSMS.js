function sendSms(to, body) {
  var number = "+1"+to;
  
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
}