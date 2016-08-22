var chart;
var chartdata = [0, 0, 0, 0, 0];
var labels = ["A", "B", "C", "D", "E"];
var userResponse = "A, B, C, D, E";
var curPhoneNumbers = []; //Array of phone numbers that have participated in current poll

function setTitle(pNum) {     
   $("#title").text("Text " + pNum + " to Participate");
   $('#quizInfo').prepend("<hr>");
   
   quizReady("#yay", "Q1", "0/0 responses received");//Just to Test
}

function checkForSession() {
    //Ask server to check if sesssion already exists
    
      //if session exists then
         
         //Load quiz where it was left off (hashtag and curQuestion but nothing in previous question chart)
         
         //Prompt user if they would like to continue quiz session from last time or end and start a new one
         
      //If session doesn't exist then
      
         //Ask for hashtag and prefill reply when someone joins session
         
            //If hashtag exists, then ask for another choice
            
            //If hashtag doesn't exist, then
            
               //Change session preferences to add the new session that is now active
               
               //Create session table with one question
               
               //Load quiz
}

function quizReady(hashtag, curQuestion, curResponses) {
   //Announce hashtag
   $("#hashtagMessage").text("Text " + hashtag + " first to join session");
   
   //Add header buttons
   $('#buttonHolder').append("<input id='settings' type='button' value='Settings' style='float: right;'>");
   $('#buttonHolder').append("<input id='endSession' type='button' value='End Quiz Session' style='float: left;'>");
   
   //Quiz Info Section
   $('#quizInfo').append("<input id='nextQ' type='button' value='Next Question' style='float: right;'>");
   $('#quizInfo').append("<input id='prevQ' type='button' value='Previous Question' style='float: left;'>");
   $('#quizInfo').append('<H1 align="center" id="curQ">' + curQuestion + '</H1>');
   $('#quizInfo').append('<H3 align="center" id="curResponses">' + curResponses + '</H3>');
   
   //Setup charts
      //Live Chart
      liveChart = new Highcharts.Chart({
         chart: {
         renderTo: "liveChart",
         type: 'bar'
         },
          
         title: {
         text: "Current Question" //FUTURE: ADD WHICH QUESTION in () FOR CLARIFICATION
         },
         
         xAxis: {
            categories: labels,
            labels: {
               style: {
                  fontWeight: 'bold',
                  fontSize:'20px'
               }
            }
         },
         
         yAxis: {
            allowDecimals: false,
            labels: {
                style: {
                  fontWeight: 'bold',
                  fontSize:'15px'
               }
            },
            title: {
                text: ''
            }
        },
         
         series: [{name: 'Votes', data: chartdata}]   
          
         });
      
      //Prev Question Chart
      prevChart = new Highcharts.Chart({
         chart: {
         renderTo: "prevChart",
         type: 'bar'
         },
          
         title: {
         text: "Previous Question" //FUTURE: ADD WHICH QUESTION in () FOR CLARIFICATION
         },
         
         xAxis: {
            categories: labels,
            labels: {
               style: {
                  fontWeight: 'bold',
                  fontSize:'20px'
               }
            }
         },
         
         yAxis: {
            allowDecimals: false,
            labels: {
                style: {
                  fontWeight: 'bold',
                  fontSize:'15px'
               }
            },
            title: {
                text: ''
            }
        },
         
         series: [{name: 'Votes', data: chartdata}]   
          
         });
   
   $("#settings").click(function(){
      
      //Get user response
      newUserResponse = prompt("Enter comma seperated voting options", userResponse);
      
      if (newUserResponse === null || newUserResponse == "") return;//If user presses cancel, then ignore
      
      //User response to upper case
      newUserResponse = newUserResponse.toUpperCase();
      
      //Check user response has at least two options and that values are comma seperated
      var regex = new RegExp("^(?:[A-z0-9], )+[A-z0-9]$");
      if (regex.test(newUserResponse)) {
         userResponse = newUserResponse;
      } else { //if test doesn't pass
         alert("Invalid input. Example of valid input: a, b, c -or- 1, 2, 3, 4");
         return;
      }
      
      //Overwrite labels array based on user entered values
      labels = [];
      for (var i = 0; i < userResponse.length; i += 3) {
         labels.push(userResponse.charAt(i));
      }
      
      //Reset chartdata with all 0s
      chartdata = [];
      for (var i = 0; i < labels.length; i++) {
         chartdata.push(0);
      }
      
      //Set data using that data
      liveChart.series[0].setData(chartdata);
      
      //Set new xAxis labels using
      liveChart.xAxis[0].setCategories(labels);
      
      //Clear participants
      curPhoneNumbers = [];
      
   });
   
   $("#nextQ").click(function(){
      
      //Make sever call to switch to next question
      
         //Checks to see if this is going to be a new question or if we just went back then went forward
         
         //Based on that chooses to create a new column or nah and fills curPhoneNumbers accordingly
      
      //When that call goes through...
      
      //Change question number displayed
      
      //Copy data from live chart to prev chart
      chartdata = [];
      for (var i = 0; i < labels.length; i++) {
         votesFromLive = liveChart.series[0].data[i].y+1; //NEED TO FIX AND NOT ADD 1
         chartdata.push(votesFromLive);
      }
      prevChart.series[0].setData(chartdata);
      prevChart.xAxis[0].setCategories(labels);
      for (var i = 0; i < labels.length; i++) { //REMOVE WORKAROUND WHEN FIXED
         votesFromLive = prevChart.series[0].data[i].y-1; //REMOVE WORKAROUND WHEN FIXED
         prevChart.series[0].data[i].update(votesFromLive); //REMOVE WORKAROUND WHEN FIXED
      } //REMOVE WORKAROUND WHEN FIXED
      
      //Clear participants
      curPhoneNumbers = [];
      
      //Clear live chart
      chartdata = [];     
      for (var i = 0; i < labels.length; i++) {
         chartdata.push(0);
      }
      liveChart.series[0].setData(chartdata);
      
   });
   
   $("#prevQ").click(function(){
      
      //Regex check to see if we can go to previous question (aka if we are on Q1, then nah)
      
      //Make sever call to switch to previous question and get data from previous question and the one before that if applicable
      
      //When that call goes through...
      
         //Change question number displayed
         
         //Update two charts as applicable
         
         //Update curPhoneNumbers if applicable
         
         //Get labels based on what people sent in
      
   });
   
   $("#endSession").click(function(){
      
      //Call to server to:
      
         //Get session info from preferences
         
         //If session is actually running, then
         
            //Clear active session from preferences
         
            //Concatenate all question columns in to message field
            
            //Send confirmation with what the quiz result was ("Confirmation of recorded responses for #yay (3 Qs): A, B, C");
            
            //Archive table
      
   });
     
}



function processSocketMessage(data) {
   var incomingNumber = data['number']; //Phone number of person voting
   
   //Check if phone number already participated
   if (curPhoneNumbers.indexOf(incomingNumber) == -1) { //phone number hasn't participated
      
      var newVoteMes = data['message'].substring(0,1).toUpperCase(); //Get first character from message
      var index = labels.indexOf(newVoteMes);
      
      if (index !== -1) {
         var votes = liveChart.series[0].data[index].y;
         liveChart.series[0].data[index].update(votes+1);
         curPhoneNumbers.push(incomingNumber);
      }
      
   } else { //phone number has participated
      //Remove previous vote and add new one
   }
}