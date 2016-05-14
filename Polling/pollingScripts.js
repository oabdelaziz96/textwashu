function pollResponse(e) {
  var lock = LockService.getPublicLock();
  lock.waitLock(5000);  // wait 5 seconds before conceding defeat.
   
  try {
    var doc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("pollingKey"));
    var sheet = doc.getSheetByName("TextsByQ");
    var sheet2 = doc.getSheetByName("Texts");

    var nextRow = sheet.getLastRow()+1; // get next row
    sheet2.insertRows(2, 2);
    //var nextRow2 = sheet2.getLastRow()+1;

    var message = e.parameter["Body"];
    
    //Parsing answers Related
          var parsedMessage = message.replace(/,/g,"").replace(/ /g,"");
          var matchCheck = !(parsedMessage.search("[A-Za-z]+(?=#)") == -1);
          
          if (matchCheck) {
            
          var filteredMessage = parsedMessage.match("[A-Za-z]+(?=#)");
          var studQ1 = filteredMessage[0].substring(0, 1).toLowerCase();
          var studQ2 = filteredMessage[0].substring(1, 2).toLowerCase();
          var studQ3 = filteredMessage[0].substring(2, 3).toLowerCase();
            
          
          var noTagMessage = message.match(".+(?=#)");

          //Binding Message to Sheet
          var myMessage = [message, studQ1, studQ2, studQ3];
          sheet.getRange(nextRow, 1, 1, myMessage.length).setValues([myMessage]);
          //sheet2.getRange(nextRow2, 1, 2, 1).setValues([[noTagMessage[0]],["------"]]);
          sheet2.getRange(2, 1, 2, 1).setValues([[noTagMessage[0]],["------"]]);  
            
          } else {

            var studQ1 = parsedMessage.substring(0, 1).toLowerCase();
            var studQ2 = parsedMessage.substring(1, 2).toLowerCase();
            var studQ3 = parsedMessage.substring(2, 3).toLowerCase();
          
          //Binding Message to Sheet
          var myMessage = [message, studQ1, studQ2, studQ3];
          sheet.getRange(nextRow, 1, 1, myMessage.length).setValues([myMessage]);
          //sheet2.getRange(nextRow2, 1, 2, 1).setValues([[message],["------"]]);
          sheet2.getRange(2, 1, 2, 1).setValues([[message],["------"]]);
      
          }
    

    SpreadsheetApp.flush();
    
  } catch (e) {
    handleResponse(e);
    
  } finally { //release lock
    lock.releaseLock();
  }
}