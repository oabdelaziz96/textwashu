         var chart;
         var chartdata = [0, 0, 0, 0, 0];
         var labels = ["A", "B", "C", "D", "E"];
         var userResponse = "A, B, C, D, E";
         var curPhoneNumbers = []; //Array of phone numbers that have participated in current poll
         
         function newChart() {
            chart = new Highcharts.Chart({
            chart: {
            renderTo: 'chart',
            type: 'bar'
            },
             
            title: {
            text: ''
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
         }
         
         function setTitle(pNum) {     
            $("#title").text("Text " + pNum + " to Vote");
            $('#buttonHolder').append("<input id='clear' type='button' value='Clear' style='float: right;'>");
            $('#buttonHolder').append("<input id='settings' type='button' value='Settings' style='float: right;'>");
            $('#buttonHolder').append("<input id='sendToOther' type='button' value='Texts' style='float: left;'>");
            $('#buttonHolder').append("<input id='sendToMenu' type='button' value='Main Menu' style='float: left;'>");
            $('#hrHolder').prepend("<hr>");
            newChart();
            
            $("#clear").click(function(){
               
               chartdata = [];
               
               for (var i = 0; i < labels.length; i++) {
                  chartdata.push(0);
               }
               
               chart.series[0].setData(chartdata);
               
               //Clear participants
               curPhoneNumbers = [];
               
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
               chart.series[0].setData(chartdata);
               
               //Set new xAxis labels using
               chart.xAxis[0].setCategories(labels);
               
               //Clear participants
               curPhoneNumbers = [];
               
            });
            
            $("#sendToOther").click(function(){
               window.open('http://live.textwashu.com/texts', '_blank');
            });
            
            $("#sendToMenu").click(function(){
               window.open('http://instructor.textwashu.com/mainMenu.php', '_self');
            });
            
         }
         
         function processSocketMessage(data) {
            var incomingNumber = data['number']; //Phone number of person voting
            
            //Check if phone number already participated
            if (curPhoneNumbers.indexOf(incomingNumber) == -1) { //phone number hasn't participated
            
               curPhoneNumbers.push(incomingNumber);
               
               var newVoteMes = data['message'].substring(0,1).toUpperCase(); //Get first character from message
               var index = labels.indexOf(newVoteMes);
               
               if (index !== -1) {
                  var votes = chart.series[0].data[index].y;
                  chart.series[0].data[index].update(votes+1);
               }
            }
         }