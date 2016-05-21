function generateEvent(number, message) {
    var encodedMessage = '[{"parameter":{"ApiVersion":"2010-04-01","SmsSid":"SMe57d9a5de4a61a01da88f1d77a4c95fe","SmsStatus":"received","SmsMessageSid":"SMe57d9a5de4a61a01da88f1d77a4c95fe","NumSegments":"1","ToState":"MO","From":"+1'+number+'","MessageSid":"SMe57d9a5de4a61a01da88f1d77a4c95fe","AccountSid":"AC994903a8a48275a31f0a5c8e9dd50824","ToCity":"SAINT LOUIS","FromCountry":"US","ToZip":"63104","FromCity":"ROSELLE","To":"+13142549679","FromZip":"60157","ToCountry":"US","Body":"'+message+'","NumMedia":"0","FromState":"IL"},"contextPath":"","contentLength":-1,"queryString":"ToCountry=US&ToState=MO&SmsMessageSid=SMe57d9a5de4a61a01da88f1d77a4c95fe&NumMedia=0&ToCity=SAINT+LOUIS&FromZip=60157&SmsSid=SMe57d9a5de4a61a01da88f1d77a4c95fe&FromState=IL&SmsStatus=received&FromCity=ROSELLE&Body=THIS+IS+THE+MESSAGE&FromCountry=US&To=%2B13142549679&ToZip=63104&NumSegments=1&MessageSid=SMe57d9a5de4a61a01da88f1d77a4c95fe&AccountSid=AC994903a8a48275a31f0a5c8e9dd50824&From=%2B16306246627&ApiVersion=2010-04-01","parameters":{"ApiVersion":["2010-04-01"],"SmsSid":["SMe57d9a5de4a61a01da88f1d77a4c95fe"],"SmsStatus":["received"],"SmsMessageSid":["SMe57d9a5de4a61a01da88f1d77a4c95fe"],"NumSegments":["1"],"ToState":["MO"],"From":["+1'+number+'"],"MessageSid":["SMe57d9a5de4a61a01da88f1d77a4c95fe"],"AccountSid":["AC994903a8a48275a31f0a5c8e9dd50824"],"ToCity":["SAINT LOUIS"],"FromCountry":["US"],"ToZip":["63104"],"FromCity":["ROSELLE"],"To":["+13142549679"],"FromZip":["60157"],"ToCountry":["US"],"Body":["'+message+'"],"NumMedia":["0"],"FromState":["IL"]}}]';
    var array = JSON.parse(encodedMessage);
    var event = array[0];
    return event;
}

function runTest() {
  var numTrials = 1;
  
  for (var i = 0; i < numTrials; i++) {
    var event = generateEvent("6306246627", "Hello World");
    doGet(event);
  }
}