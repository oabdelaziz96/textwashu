function onOpen(e) {
   SpreadsheetApp.getUi()
       .createMenu('Grader')
       .addItem('New Grader', 'newGrader')
       .addToUi();
 }

function newGrader() {
  var ui = SpreadsheetApp.getUi();

  var result = ui.prompt(
    'Please enter the new hashtag you would like to grade:',
    'For example, to grade #yay, please write: #yay',
      ui.ButtonSet.OK_CANCEL);

  // Process the user's response.
  var button = result.getSelectedButton();
  var newTag = result.getResponseText();
  var matchCheck = !(newTag.search("(^#[^ ]*$)") == -1);
  
  if (button == ui.Button.OK) {
    if (matchCheck) {
      //First make the sheet and layout
      var ss = SpreadsheetApp.getActiveSpreadsheet();
      ss.insertSheet(newTag, 0);
      var newSheet = ss.getSheetByName(newTag);
      var topRow = [["Title","","","Time Received","Phone Number","Response","ID Number","Grade"]];
      var leftCol = [["Date"],["Type of Credit"],["# of Questions"],["Key"],["# of Responses"],["Average"]];
      newSheet.getRange(1, 1, 1, topRow[0].length).setValues(topRow);
      newSheet.getRange(2, 1, leftCol.length, 1).setValues(leftCol);
      newSheet.setFrozenRows(1);
      newSheet.getRange(1, 4, 1, 5).setFontWeight("bold");
      newSheet.getRange(1, 4, 1000, 5).setHorizontalAlignment("center");
      newSheet.getRange("D2").setValue("                               ");
      newSheet.autoResizeColumn(4);
      newSheet.getRange("D2").setValue("");
      newSheet.getRange(2, 4, 1000).setNumberFormat("M/d/yyyy H:mm:ss");
      
      //Then get the data
      var contactsSheet = ss.getSheetByName("Contacts");
      var ss2 = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("hubKey"));
      var hashSheet = ss2.getSheetByName(newTag);
      var quantity = hashSheet.getLastRow()-1;
      var dataArray = hashSheet.getRange(2, 1, quantity, 3).getValues();
      newSheet.getRange(2, 4, quantity, 3).setValues(dataArray);
      
      //Spit out ID Nubets if available
      var idFormula = '=IFERROR(IF(ISBLANK(VLOOKUP(R[0]C[-2]&"",Contacts!C[-6]:C[-5],2,FALSE)), "N/A",VLOOKUP(R[0]C[-2]&"",Contacts!C[-6]:C[-5],2,FALSE)),"N/A")';
      newSheet.getRange(2, 7, quantity).setFormulaR1C1(idFormula);
      var idNumbers = newSheet.getRange(2, 7, quantity).getValues();
      newSheet.getRange(2, 7, quantity).setValues(idNumbers);
      

      //Prepare for grading
      var answerKeyPrompt = ui.prompt(
        'Please enter the answer key for grading or cancel to give participation credit:',
        'For example, if the answer to Q1 and Q2 is a and b respectively, enter ab',
        ui.ButtonSet.OK_CANCEL);
      
      if (answerKeyPrompt.getSelectedButton() == ui.Button.OK) {
      var key = answerKeyPrompt.getResponseText().toLowerCase();
      var numQs = key.length;
      
      var grades = [];
      var gradeAvg = 0;
      
      //Actual Grading
      for (i=0; i < quantity; i++) {
        var curResponse = dataArray[i][2];
        var curValid = typeof curResponse == "string";
    
        if (curValid) {  
        curResponse = curResponse.toLowerCase();
        var curAnswerArray = curResponse.match(/[a-z]/g);
        var curNumAnswers = Math.min(curAnswerArray.length,numQs);

        grades[i] = 0;
        
        for (k=0; k < curNumAnswers; k++) {
          if (key.charAt(k) == curAnswerArray[k]) {
           grades[i]++; 
          }
        }
        
        grades[i] = [grades[i]/numQs];
        gradeAvg += grades[i]/numQs;
        
        } else {
        
        grades[i] = [0];
          
        }
        
      }
       
        newSheet.getRange(2, 8, quantity).setValues(grades);
        gradeAvg = gradeAvg / quantity;
        var type = "Credit";
        
      } else {
        
        newSheet.getRange(2, 8, quantity).setValue(1);
        var gradeAvg = 1;
        var type = "Participation";
        var key = "N/A";
        var numQs = "N/A";
        
      }
  
      //Finalize sheet
      var leftColAns = [[new Date()],[type],[numQs],[key],[quantity],[gradeAvg]];
      newSheet.getRange(2, 2, leftColAns.length, 1).setValues(leftColAns);
      newSheet.getRange(2, 2, leftColAns.length, 1).setHorizontalAlignment("center");
      newSheet.getRange("B7").setNumberFormat("0%");
      
    } else {
      
      ui.alert("Invalid Tag");
    
    }
  } 
}