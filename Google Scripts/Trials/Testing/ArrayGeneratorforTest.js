/**
 * @OnlyCurrentDoc
 */

function onOpen(e) {
   SpreadsheetApp.getUi()
       .createMenu('Script')
       .addItem('Do it', 'myFunction')
       .addToUi();
 }


//Must have a phone num col in col 1 and text col next to it
function myFunction() {
  var ss = SpreadsheetApp.getActiveSheet();
  var data = ss.getRange(2, 1, ss.getLastRow()-1, 2).getValues();
  var result = '{';
  
  Logger.log(data);
  
  for (var i = 0; i < data.length; i++) {
    var curPN = data[i][0];
    var curMes = data[i][1];
    var curCombo = '"' + curPN + " - " + curMes + '"';
    
    
    if (i == data.length - 1) {
      //Last one
      result+= (curCombo + '}');
    } else {
      result+= (curCombo + ', ');
    }
  }
  
  ss.getRange("E1").setValue(result);
}

