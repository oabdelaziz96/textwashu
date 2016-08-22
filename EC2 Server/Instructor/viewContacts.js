function showContacts(event){

    var xmlHttp = new XMLHttpRequest(); // Initialize our XMLHttpRequest instance
    xmlHttp.open("POST", "getContacts.php", true); // Starting a POST request
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); // It's easy to forget this line for POST requests
    xmlHttp.addEventListener("load", function(event){
        var jsonData = JSON.parse(event.target.responseText); // parse the JSON into a JavaScript object
        if(jsonData.success){  // in PHP, this was the "success" key in the associative array; in JavaScript, it's the .success property of jsonData
            var dataArray = jsonData.dataArray;
            var columnsArray = [{ title: "Phone Number" }];
            
            if (jsonData.preferences.enableName) {
                columnsArray.push({ title: "First Name" });
                columnsArray.push({ title: "Last Name" });
            }
            
            if (jsonData.preferences.enableEmail) {
                columnsArray.push({ title: "Email" });
            }
            
            if (jsonData.preferences.enableWuKey) {
                columnsArray.push({ title: "WUSTL Key" });
            }
            
            if (jsonData.preferences.enableID) {
                columnsArray.push({ title: "ID Number" });
            }
            
            columnsArray.push({ title: "Options" });
            
            $(document).ready(function() {
                $('#table').DataTable( {
                    data: dataArray,
                    columns: columnsArray
                } );
            
            var table = $('#table').DataTable();
            table.search( searchField ).draw();
            $('input[type=search]').val('');
            
            $('.confirmation').on('click', function () {
                return confirm('You are about to delete this contact and all associated text messgaes. Are you sure you want to delete this contact?');
            });
            
            } );
            
        }else{ //JSON failed
            alert(jsonData.message);
        }
        
    }, false); // Bind the callback to the load event
    
    xmlHttp.send(); // Send the data
    
}
 
document.addEventListener("DOMContentLoaded", showContacts, false);