//Express setup
var express = require('express');
var expressApp = express();

//Body Parser setup
var bodyParser = require('body-parser');
expressApp.use(bodyParser.urlencoded({ extended: false }));

//Twilio setup
var twilio = require('twilio');

//Import ProcessSubmission.js
var processSubmission = require('./ProcessSubmission');

//Necessary to handle GET requests but no actual use
expressApp.get('/', function(req, res){
    console.log('GET Request')
});

//POST request handler, which is where texts come in
expressApp.post('/twilio', function(req, res){
    
    //Get Relevant Data
    var data = {
        type : "Twilio",
        body : req.body.Body,
        number : req.body.From.substring(2),
        mediaExists : req.body.NumMedia > 0
    };
    
    if (data.mediaExists) data.mediaURL = req.body.MediaUrl0;
    
    console.log('New Text via POST:'); // ------------ For Testing --------------
    console.log("Type: " + data.type); // ------------ For Testing --------------
    console.log("Body: " + data.body); // ------------ For Testing --------------
    console.log("Number: " + data.number); // ------------ For Testing --------------
    console.log("Media Exists: " + data.mediaExists); // ------------ For Testing --------------
    console.log("MediaURL: " + data.mediaURL); // ------------ For Testing --------------
    
    //Send data to be processed
    var responseText = processSubmission.test(data);
    
    //Respond to request
    var response = new twilio.TwimlResponse();
    response.message("hello");

    res.writeHead(200, {
        'Content-Type':'text/xml'
    });
    
    res.end(response.toString());
    
});


//Activate application
port = 6543;
expressApp.listen(port);
console.log('Listening at http://localhost:' + port);

