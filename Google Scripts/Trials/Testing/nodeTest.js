// Require the packages we will use:
var http = require("http"),
	socketio = require("socket.io"),
	fs = require("fs");



 
// Listen for HTTP connections.  This is essentially a miniature static file server that only serves our one file, client.html:
var app = http.createServer(function(req, resp){
	// This callback runs when a new connection is made to our HTTP server.
 
	fs.readFile("client.html", function(err, data){
		// This callback runs when the client.html file has been read from the filesystem.
 
		if(err) return resp.writeHead(500);
		resp.writeHead(200);
		resp.end(data);
	});
});
app.listen(3456);
 
// Do the Socket.IO magic:
var io = socketio.listen(app);
io.sockets.on("connection", function(socket){
	// This callback runs when a new Socket.IO connection is established.
 
	socket.on('message_to_server', function(data) {
		// This callback runs when the server receives a new message from the client.
 

 
		console.log("message: "+data["message"]); // log it to the Node.JS output
		io.sockets.emit("message_to_client",{message:data["message"] }) // broadcast the message to other users
	});
    
});

    //Text In
var express = require('express');
var app2 = express();
var bodyParser = require('body-parser');

app2.use(bodyParser());

app2.get('/', function(req, res){
    console.log('GET /')
    //var html = '<html><body><form method="post" action="http://localhost:3000">Name: <input type="text" name="name" /><input type="submit" value="Submit" /></form></body>';
    //var html = fs.readFileSync('index.html');
    //res.writeHead(200, {'Content-Type': 'text/html'});
    //res.end(html);
});

app2.post('/', function(req, res){
    console.log('POST /');
    //var data = bodyParser.json
    console.dir(req.body.Body);
    io.sockets.emit("message_to_client",{message:req.body.Body}); // broadcast the message to other users
    
		// This callback runs when the server receives a new message from the client.
 
 
    
    
});

port = 6543;
app2.listen(port);