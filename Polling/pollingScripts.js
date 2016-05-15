function pollResponse(e) {
  var lock = LockService.getPublicLock();
  lock.waitLock(5000);  // wait 5 seconds before conceding defeat.
   
  try {
    var doc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("pollingKey"));
    var qSheet = doc.getSheetByName("TextsByQ");
    var mesSheet = doc.getSheetByName("Texts");
    var message = e.parameter["Body"];
    
    //Bind message to messages sheet
    var noTagMessage = removeTags(message);
    mesSheet.insertRows(2, 2);
    mesSheet.getRange(2, 1, 2, 1).setValues([[noTagMessage],["------"]]);
    
    //Verify unique respondent for poll part
    var curPollNums = SCRIPT_PROP.getProperty("curPollNums");
    var number = e.parameter["From"].substring(2);
    var uniqueRespondent = curPollNums.search(number) == -1;
    
    if (uniqueRespondent) {
      //Parsing answers
      var answerArray = message.toLowerCase().match(/[a-z]/g);
  
      var studQ1 = answerArray[0];
      var studQ2 = answerArray[1];
      var studQ3 = answerArray[2];
  
      //Binding message to questions sheet
      var myMessage = [message, studQ1, studQ2, studQ3];
      qSheet.getRange(qSheet.getLastRow()+1, 1, 1, myMessage.length).setValues([myMessage]);
      
      //Add number to respondent array
      curPollNums+= (number + ",");
      SCRIPT_PROP.setProperty("curPollNums", curPollNums);
    }
    
    SpreadsheetApp.flush();
    
  } catch (e) {
    pollResponse(e);
    
  } finally { //release lock
    lock.releaseLock();
  }
}