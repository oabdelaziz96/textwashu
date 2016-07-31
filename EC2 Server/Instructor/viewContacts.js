function showContacts(event){

    var xmlHttp = new XMLHttpRequest(); // Initialize our XMLHttpRequest instance
    xmlHttp.open("POST", "getContacts.php", true); // Starting a POST request
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); // It's easy to forget this line for POST requests
    xmlHttp.addEventListener("load", function(event){
        var jsonData = JSON.parse(event.target.responseText); // parse the JSON into a JavaScript object
        if(jsonData.success){  // in PHP, this was the "success" key in the associative array; in JavaScript, it's the .success property of jsonData
            var dataArray = jsonData.dataArray;
            
            $(document).ready(function() {
                $('#table').DataTable( {
                    data: dataArray,
                    columns: [
                        { title: "Phone Number" },
                        { title: "First Name" },
                        { title: "Last Name" },
                        { title: "Email" },
                        { title: "WUSTL Key" },
                        { title: "ID Number" }
                    ]
                } );
            
            var table = $('#table').DataTable();
            table.search( searchField ).draw();
            $('input[type=search]').val('');
            
            } );
            
        }else{ //JSON failed
            alert(jsonData.message);
        }
        
    }, false); // Bind the callback to the load event
    
    xmlHttp.send(); // Send the data
    
}
 
document.addEventListener("DOMContentLoaded", showContacts, false);