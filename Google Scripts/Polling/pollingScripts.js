function pollResponse(e) {
  var lock = LockService.getScriptLock();
  var lockAquired = lock.tryLock(30000); // wait 30 seconds before conceding defeat.
  
  if (lockAquired) {
    var doc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("pollingKey"));
    var qSheet = doc.getSheetByName("TextsByQ");
    var mesSheet = doc.getSheetByName("Texts");
    var message = e.parameter["Body"].substring(13);//e.parameter["Body"]; -------- CHANGED FOR STRESS TESTING
    
    //Bind message to messages sheet
    var noTagMessage = removeTags(message);
    mesSheet.insertRows(2, 2);
    mesSheet.getRange(2, 1, 2, 1).setValues([[noTagMessage],["------"]]);
    
    //Verify unique respondent for poll part
    var curPollNums = SCRIPT_PROP.getProperty("curPollNums");
    var number = e.parameter["Body"].substring(0, 10);//e.parameter["From"].substring(2); -------- CHANGED FOR STRESS TESTING
    var uniqueRespondent = curPollNums.search(number) == -1;
    
    if (uniqueRespondent) {
      //Parsing answers
      var answerArray = message.toLowerCase().match(/[a-z]/g);
      
      var studQ1 = "";
      var studQ2 = "";
      var studQ3 = "";
      
      if (answerArray !== null) {
        studQ1 = answerArray[0];
        studQ2 = answerArray[1];
        studQ3 = answerArray[2];
      }
  
      //Binding message to questions sheet
      var myMessage = [message, studQ1, studQ2, studQ3];
      qSheet.getRange(qSheet.getLastRow()+1, 1, 1, myMessage.length).setValues([myMessage]);
      
      //Add number to respondent array
      curPollNums+= (number + ",");
      SCRIPT_PROP.setProperty("curPollNums", curPollNums);
    }
    
    SpreadsheetApp.flush();
    lock.releaseLock();
  } else {
    
    //Just for the purposes of testing
    //MailApp.sendEmail("oabdelaziz@me.com", "From Polling: " + e.parameter["Body"], e.parameter["SmsSid"]);
    
    pollResponse(e);
   
  }
}