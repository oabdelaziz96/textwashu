function onOpen(e) {
   SpreadsheetApp.getUi()
       .createMenu('Filters')
       .addItem('Add Filter', 'newFilter')
       .addItem('Delete This Filter', 'deleteFilter')
       .addItem('Archive This Filter', 'archiveFilter')
       .addToUi();
 }


function newFilter() {
  var ui = SpreadsheetApp.getUi();

  var result = ui.prompt(
    'Please enter the new hashtag you would like:',
    'For example, to start tracking #yay, please write: #yay',
      ui.ButtonSet.OK_CANCEL);

  // Process the user's response.
  var button = result.getSelectedButton();
  var newTag = result.getResponseText();
  var matchCheck = !(newTag.search("(^#[^ ]*$)") == -1);
  
  if (button == ui.Button.OK) {
    if (matchCheck) {
      var ss = SpreadsheetApp.getActiveSpreadsheet();
      ss.insertSheet(newTag, ss.getNumSheets());
      var newSheet = ss.getSheetByName(newTag);
      var topRow = [["Time","Phone Number","Message","","","Hashtag AutoReply:"]];
      newSheet.getRange(1, 1, 1, topRow[0].length).setValues(topRow);
      newSheet.autoResizeColumn(6);
      newSheet.getRange("F1").setNote("Message in G1 will be the automatic reply to any message with this sheet's hashtag");
      newSheet.setFrozenRows(1);
      newSheet.getRange(1, 1, 1, 3).setFontWeight("bold");
      newSheet.getRange(1, 1, 1, 3).setHorizontalAlignment("center");
      newSheet.getRange("A2").setValue("                               ");
      newSheet.autoResizeColumn(1);
      newSheet.getRange("A2").setValue("");
      newSheet.getRange(2, 1, 1000).setNumberFormat("M/d/yyyy H:mm:ss");
      newSheet.getRange(2, 1, 1000, 3).setHorizontalAlignment("center");
    } else {
      
      ui.alert("Invalid Tag");
    
    }
  } 
}

function deleteFilter() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = SpreadsheetApp.getActiveSheet();
  var valid = sheet.getName().search("(^#[^ ]*$)") !== -1;
  var ui = SpreadsheetApp.getUi();
  
  if (valid) { // A filter sheet is selected
    var response = ui.alert("Are you sure you want to delete this filter and not archive it?","This action is not reversible", ui.ButtonSet.YES_NO);
    
    if (response == ui.Button.YES) {
      ss.deleteSheet(sheet);
    }
    
  } else { // A filter sheet is not selected
    ui.alert("The sheet currently selected is not a filter. Please select a filter (or hashtag) sheet and try again");
  } 
}

function archiveFilter() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = SpreadsheetApp.getActiveSheet();
  var hashtag = sheet.getName();
  var valid = hashtag.search("(^#[^ ]*$)") !== -1;
  var ui = SpreadsheetApp.getUi();
  
  if (valid) { // A filter sheet is selected
    var archiveSpreadsheet = SpreadsheetApp.openById(GenScripts.SCRIPT_PROP.getProperty("archiveKey"));
    sheet.copyTo(archiveSpreadsheet);
    var newSheet = archiveSpreadsheet.getSheetByName("Copy of "+hashtag);
    newSheet.setName(hashtag);
    ss.deleteSheet(sheet);
  } else { // A filter sheet is not selected
    ui.alert("The sheet currently selected is not a filter. Please select a filter (or hashtag) sheet and try again");
  } 
}