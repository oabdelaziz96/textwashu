//Express setup
var express = require('express');
var expressApp = express();

//Body Parser setup
var bodyParser = require('body-parser');
expressApp.use(bodyParser.json());

//Necessary to handle GET requests but no actual use
expressApp.get('/', function(req, res){
    console.log('GET Request')
});

//POST request handler, which is where texts come in
expressApp.post('/', function(req, res){

    console.log("New Text Message Received via POST ---");
    console.log("Body: " + req.body.body);
    console.log("Number: " + req.body.number);
    console.log("End of request -----------------------");
    //to-do: forward the message to the connected nodes.
    res.end('Message Received');
    
});


//Activate application
port = 6543;
expressApp.listen(port);
console.log('Listening at http://localhost:' + port);

