function showHashtagsTable(event){

    var xmlHttp = new XMLHttpRequest(); // Initialize our XMLHttpRequest instance
    xmlHttp.open("POST", "getHashtagsTable.php", true); // Starting a POST request
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); // It's easy to forget this line for POST requests
    xmlHttp.addEventListener("load", function(event){
        var jsonData = JSON.parse(event.target.responseText); // parse the JSON into a JavaScript object
        if(jsonData.success){  // in PHP, this was the "success" key in the associative array; in JavaScript, it's the .success property of jsonData
            var dataArray = jsonData.dataArray;
            
            $(document).ready(function() {
                $('#table').DataTable( {
                    data: dataArray,
                    columns: [
                        { title: "Hashtag" },
                        { title: "Status" },
                        { title: "Reply" },
                        { title: "Options"}
                    ]
                } );
                
                $('.confirmation').on('click', function () {
                    return confirm('This hashtag is already archived. Are you sure you want to delete this hashtag and all associated messages?');
                });
                
            } );
            


            
        }else{ //JSON failed
            
        }
        
    }, false); // Bind the callback to the load event
    
    xmlHttp.send(); // Send the data
    
}
 
document.addEventListener("DOMContentLoaded", showHashtagsTable, false);