/**
 * @OnlyCurrentDoc
 */

function onOpen(e) {
   SpreadsheetApp.getUi()
       .createMenu('Filters')
       .addItem('Add Filter', 'newFilter')
       .addItem('Delete Filter', 'deleteFilter')
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
      var topRow = [["Time","Phone Number","Message","","","","","NumMessages"]];
      newSheet.getRange(1, 1, 1, topRow[0].length).setValues(topRow);
      newSheet.setFrozenRows(1);
      newSheet.getRange(1, 1, 1, 3).setFontWeight("bold");
      newSheet.getRange(1, 1, 1, 3).setHorizontalAlignment("center");
      newSheet.getRange("I1").setFormula("=COUNTA(C:C)-1");
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
  var ui = SpreadsheetApp.getUi();

    var result = ui.prompt(
    'Please enter the hashtag you would like to delete:',
    'For example, to stop tracking #yay, please write: #yay',
      ui.ButtonSet.OK_CANCEL);

  // Process the user's response.
  var button = result.getSelectedButton();
  var tagName = result.getResponseText();
  var matchCheck = !(tagName.search("(^#[^ ]*$)") == -1);
  
  if (button == ui.Button.OK) {
    if (matchCheck) {
      var ss = SpreadsheetApp.getActiveSpreadsheet();
      ss.deleteSheet(ss.getSheetByName(tagName));
    } else {
      ui.alert("Invalid Tag");
    }    
  } 
}