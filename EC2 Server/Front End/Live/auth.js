function getPhoneNumber(event){

    var xmlHttp = new XMLHttpRequest(); // Initialize our XMLHttpRequest instance
    xmlHttp.open("POST", "/checkLogin", true); // Starting a POST request
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); // It's easy to forget this line for POST requests
    xmlHttp.addEventListener("load", function(event){
        var jsonData = JSON.parse(event.target.responseText); // parse the JSON into a JavaScript object
        if(jsonData.success){
            
            var phoneNumber = jsonData.phoneNumber;
            
            var socketio = io.connect();
         
            socketio.on('connect', function() {
               socketio.emit('declarePhoneNumber', phoneNumber);
            });
            
            socketio.on("message_to_client", function(data) {processSocketMessage(data)});
            
            var formatedNumber = "(" + phoneNumber.substring(0,3) + ") " + phoneNumber.substring(3,6) + "-" + phoneNumber.substring(6,10);
            
            setTitle(formatedNumber);
            
        }else{ //JSON failed
            
            window.location.replace("http://textwashu.com/mainMenu.php");
        
        }
        
    }, false); // Bind the callback to the load event
    
    xmlHttp.send(); // Send the data
    
}
 
document.addEventListener("DOMContentLoaded", getPhoneNumber, false);