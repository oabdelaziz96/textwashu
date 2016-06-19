/*
 * Must import scripts library to use and call it "GenScripts"
 */
function onOpen(e) {
   SpreadsheetApp.getUi()
       .createMenu('Clear Responses')
       .addItem('For Polls', 'GenScripts.removePollingPolls')
       .addItem('For Text Feed', 'GenScripts.removePollingTexts')
       .addItem('For Both', 'GenScripts.removePollingBoth')
       .addToUi();
 }

function removePollingPolls() {
  var ss = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("pollingKey"));
  var messagesSheet = ss.getSheetByName("TextsByQ");
  var numMessages  = messagesSheet.getLastRow()+1;
  messagesSheet.deleteRows(2, numMessages);
  messagesSheet.insertRows(2, numMessages);
  SCRIPT_PROP.setProperty("curPollNums", "");
}

function removePollingTexts() {
  var ss = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("pollingKey"));
  var messagesSheet = ss.getSheetByName("Texts");
  var numMessages  = messagesSheet.getLastRow()+1;
  messagesSheet.deleteRows(2, numMessages);
}

function removePollingBoth() {
  removePollingPolls();
  removePollingTexts();
}