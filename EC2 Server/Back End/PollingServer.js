// Require the packages we will use:
var http = require("http"),
	socketio = require("socket.io"),
	fs = require("fs");
 
// Listen for HTTP connections.  This is essentially a miniature static file server that only serves our one file, client.html:
var app = http.createServer(function(req, resp){
	// This callback runs when a new connection is made to our HTTP server.
 
	fs.readFile("Polling.html", function(err, data){
		// This callback runs when the client.html file has been read from the filesystem.
 
		if(err) return resp.writeHead(500);
		resp.writeHead(200);
		resp.end(data);
	});
});
app.listen(3456);
 
// Do the Socket.IO magic:
var io = socketio.listen(app);

//Express setup
var express = require('express');
var expressApp = express();

//Body Parser setup
var bodyParser = require('body-parser');
expressApp.use(bodyParser.json());

//Necessary to handle GET requests but no actual use
expressApp.get('/', function(req, res){});

//POST request handler, which is where texts come in
expressApp.post('/', function(req, res){

	io.sockets.emit("message_to_client",{message:req.body.body }) // broadcast the message to other users
    res.end('Message Received');
    
});

//Activate application
port = 6543;
expressApp.listen(port);
console.log('Listening for texts at http://localhost:' + port);