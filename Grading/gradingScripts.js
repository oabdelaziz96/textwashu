function onOpen(e) {
   SpreadsheetApp.getUi()
       .createMenu('Grader')
       .addItem('New Grader', 'newGrader')
       .addItem('Send to Gradebook', 'sendToGrades')
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
      //First make sure a sheet exists
      var ss2 = SpreadsheetApp.openById(GenScripts.SCRIPT_PROP.getProperty("hubKey"));
      var hashSheet = ss2.getSheetByName(newTag);
      if (hashSheet == null) {
        ss2 = SpreadsheetApp.openById(GenScripts.SCRIPT_PROP.getProperty("archiveKey"));
        hashSheet = ss2.getSheetByName(newTag);
      }
      if (hashSheet !== null) {
         
         //Then make the sheet and layout
         var ss = SpreadsheetApp.getActiveSpreadsheet();
         ss.insertSheet(newTag, 0);
         var newSheet = ss.getSheetByName(newTag);
         var topRow = [["Title","","","Time Received","Phone Number","Response","Grade"]];
         var leftCol = [["Date"],["Type of Credit"],["# of Questions"],["Key"],["# of Responses"],["Average"]];
         newSheet.getRange(1, 1, 1, topRow[0].length).setValues(topRow);
         newSheet.getRange(2, 1, leftCol.length, 1).setValues(leftCol);
         newSheet.setFrozenRows(1);
         newSheet.getRange(1, 4, 1, 4).setFontWeight("bold");
         newSheet.getRange(1, 4, 1000, 4).setHorizontalAlignment("center");
         newSheet.getRange("D2").setValue("                               ");
         newSheet.autoResizeColumn(4);
         newSheet.getRange("D2").setValue("");
         newSheet.getRange(2, 4, 1000).setNumberFormat("M/d/yyyy H:mm:ss");
         
         //Then get the data
         var quantity = hashSheet.getLastRow()-1;
         var dataArray = hashSheet.getRange(2, 1, quantity, 3).getValues();
         newSheet.getRange(2, 4, quantity, 3).setValues(dataArray);
   
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
           
           var curGrade = grades[i]/numQs;
           gradeAvg += curGrade;
           grades[i] = [curGrade];
           
           } else {
           
           grades[i] = [0];
             
           }
           
         }
          
           newSheet.getRange(2, 7, quantity).setValues(grades);
           gradeAvg = gradeAvg / quantity;
           var type = "Credit";
           
         } else {
           
           newSheet.getRange(2, 7, quantity).setValue(1);
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
         ui.alert("The requested sheet was not found. Please verify the name and try again");
      }
    } else {
         ui.alert("Your input was not in the correct format");
    }
  } 
}

function sendToGrades() {
   var ss = SpreadsheetApp.getActiveSpreadsheet();
   var gradedTagSheet = ss.getActiveSheet();
   var gradebook = ss.getSheetByName("Gradebook");
   var hashtag = gradedTagSheet.getName();
   var valid = hashtag.search("(^#[^ ]*$)") !== -1;
  
   if (valid) { // A filter sheet is selected
   
      //First get the info from graded tag sheet
      var gradedStartRow = 2;
      var gradedStartCol = 5;
      var gradedNumRows = gradedTagSheet.getLastRow()-1;
      var gradedNumCols = 3;
      var gradedRange = gradedTagSheet.getRange(gradedStartRow, gradedStartCol, gradedNumRows, gradedNumCols).getValues();
      
      //Then get the range from the gradebook
      var phoneNums = gradebook.getRange(2, 1, gradebook.getLastRow()-1).getValues();
      phoneNums = GenScripts.ArrConvert(phoneNums);
      var resultCol = gradebook.getLastColumn()+1;
      
      //Set the column name
      var gradeTitle = gradedTagSheet.getRange("B1").getValue();
      if (gradeTitle == "") gradeTitle = gradedTagSheet.getSheetName();
      gradebook.getRange(1, resultCol).setValue(gradeTitle);
      
      //Now copy info over
      for (var i = 0; i < gradedRange.length; i++) {
         var curNum = gradedRange[i][0] + "";
         var curGrade = gradedRange[i][2];
         var curIndex = phoneNums.indexOf(curNum);
         
         if (curIndex >= 0) {
            var resultCell = gradebook.getRange(curIndex+2, resultCol);
            
            if (resultCell.getValue() === "") {
               //result cell is empty
               resultCell.setValue(curGrade);
            } else {
               //result cell already had a value
               resultCell.setNote("Multiple attempts recorded");
               gradedTagSheet.getRange(gradedStartRow + i, gradedStartCol).setBackground("#FF6133");
            }
            
         } else {
            //phone number not found
            gradedTagSheet.getRange(gradedStartRow + i, gradedStartCol).setNote("Phone number not found in grade book");
         }
      }
      
      var activeRange = gradebook.getRange(1, resultCol);
      gradebook.setActiveSelection(activeRange);
      
   } else { // A filter sheet is not selected
      var ui = SpreadsheetApp.getUi();
      ui.alert("The sheet currently selected is not a filter. Please select a filter (or hashtag) sheet and try again");
   }
}