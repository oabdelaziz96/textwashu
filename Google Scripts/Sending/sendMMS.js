function sendMMS(to, mediaURL) {
  //Include +1 in number sent to twilio
  var number = "+1"+to;

  var messages_url = "https://api.twilio.com/2010-04-01/Accounts/"+SCRIPT_PROP.getProperty("twilioSID")+"/Messages.json";

  var payload = {
    "To": number,
    "MediaUrl" : mediaURL,
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