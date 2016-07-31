function showPreferencesTable(event){

    var xmlHttp = new XMLHttpRequest(); // Initialize our XMLHttpRequest instance
    xmlHttp.open("POST", "getPreferencesTable.php", true); // Starting a POST request
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); // It's easy to forget this line for POST requests
    xmlHttp.addEventListener("load", function(event){
        var jsonData = JSON.parse(event.target.responseText); // parse the JSON into a JavaScript object
        if(jsonData.success){  // in PHP, this was the "success" key in the associative array; in JavaScript, it's the .success property of jsonData
            var dataArray = jsonData.dataArray;
            
            for (var i = 0; i < dataArray.length; i++) {
                var curName = dataArray[i][0];
                var curStatus = dataArray[i][1];
                var curArg1 = dataArray[i][2];
                var curArg2 = dataArray[i][3];
                $('#table > tbody:last-child').append("<tr><td>"+curName+"</td><td>"+curStatus+"</td><td>"+curArg1+"</td><td>"+curArg2+"</td></tr>");
            }

        }else{ //JSON failed
            
        }
        
    }, false); // Bind the callback to the load event
    
    xmlHttp.send(); // Send the data
    
}
 
document.addEventListener("DOMContentLoaded", showPreferencesTable, false);