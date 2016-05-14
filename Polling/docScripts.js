function onOpen(e) {
   SpreadsheetApp.getUi()
       .createMenu('Clear Responses')
       .addItem('For Polls', 'removePolls')
       .addItem('For Text Feed', 'removeTexts')
       .addItem('For Both', 'removeBoth')
       .addToUi();
 }

function removePolls() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var messagesSheet = ss.getSheetByName("TextsByQ");
  var numMessages  = messagesSheet.getLastRow()+1;
  messagesSheet.deleteRows(2, numMessages);
}

function removeTexts() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var messagesSheet = ss.getSheetByName("Texts");
  var numMessages  = messagesSheet.getLastRow()+1;
  messagesSheet.deleteRows(2, numMessages);
}

function removeBoth() {
  removePolls();
  removeTexts();
}