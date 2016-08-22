var app = require('express')();
var http = require('http').createServer(app);
var socketio = require('socket.io');
var io = socketio.listen(http);
var bodyParser = require('body-parser');
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({extended: true})); 
var cookieParser = require('cookie-parser');
app.use(cookieParser());
var session = require('express-session');
app.use(session({
  secret: 'WASHUTEXTING330',
  resave: true,
  saveUninitialized: false,
  cookie: { secure: false }
}));
var mysql = require("mysql");

//Main Pages:
  app.get('/texts', function(req, res){ //Live Texts Page
    res.sendfile('Texts.html');
  });
  
  app.get('/poll', function(req, res){ //Live Poll Page
    res.sendfile('Polls.html');
  });
  
  app.get('/quiz', function(req, res){ //Live Poll Page
    res.sendfile('Quiz.html');
  });
  
//User auth:
  app.get('/crossLogin', function(req, res){ //Backend processing of login request from php
    var phoneNumber = req.query.phoneNumber;
    var nodeCode = req.query.nodeCode;
    var location = "/"+req.query.location;
    
    //Check phone number regex
    var regex = new RegExp("^[0-9]{10}$");
    if (!regex.test(phoneNumber)) {
      res.send("An error occured");
      return;
    }
    
    //Check node code regex
    var regex = new RegExp("^[A-Za-z0-9]{10}$");
    if (!regex.test(nodeCode)) {
      res.send("An error occured");
      return;
    }
    
    // First create a connection to the db
      var con = mysql.createConnection({
        host: "localhost",
        user: "participationUsr",
        password: "WashU330",
        database: "participationDB"
      });
      
      con.connect(function(err){
        if(err){
          res.send("An error occured");
          return;
        }
      });
      
      con.query('SELECT nodeCode FROM accounts where twilio_phone_number = ' + phoneNumber,function(err,rows){
        if(err) throw err;
      
        if (rows.length !== 0) { //Phone  number exists
          
          var dbCode = rows[0].nodeCode;
          
          if (nodeCode == dbCode) { //codes match
            
            //Create new one time code
            dbCode = Math.random().toString(36).substring(2,12);
            
            con.query( //Send new one time code
              'UPDATE accounts SET nodeCode = ? Where twilio_phone_number = ?',
              [dbCode, phoneNumber],
              function (err, result) {
                if(err){
                  res.send("An error occured");
                  return;
                }
              });
            
            //Set session
            req.session.phoneNumber = phoneNumber;
            
            //Redirect to requested page
            res.redirect(location);
            
          } else { //codes don't match
            res.send("An error occured");
            return;
          }
          
        } else { //Phone number doesn't exist
            res.send("An error occured");
            return;
        }
        
        con.end(function(err) {});
      
      });
  
  });
  
  app.post('/checkLogin', function(req, res){ //Checks if user is logged in
    
    res.contentType('application/json');
    var phoneNumber = req.session.phoneNumber;
    
    if (phoneNumber) { //If session variable exsists
      
      var responseArray = {success: true, phoneNumber: phoneNumber};
      
    } else {
      
      var responseArray = {success: false, message: "Not logged in"};
      
    }
  
    var responseData = JSON.stringify(responseArray);
  
    res.send(responseData);
    
  });
  
  app.get('/logout', function(req, res){ //Logs user out
    
    req.session.destroy(function(err) {});
    res.redirect("http://instructor.textwashu.com");
    
  });
  

//Helper Resources:
  app.get('/highcharts.js', function(req, res){ //Highcharts library
    res.sendfile('highcharts.js');
  });
  
  app.get('/auth.js', function(req, res){ //Authentication Script
    res.sendfile('auth.js');
  });
  
  app.get('/pollScripts.js', function(req, res){ //Poll Script
    res.sendfile('pollScripts.js');
  });
  
  app.get('/quizScripts.js', function(req, res){ //Quiz Script
    res.sendfile('quizScripts.js');
  });
  

app.post('/newMessage', function(req, res){
  
  var toPhoneNumber = req.body.twilioNumber;
  io.sockets.in(toPhoneNumber).emit('message_to_client', {message:req.body.body, number:req.body.number});
  res.end('Message Received');
  
});

io.on('connection', function(socket){
    
  socket.on('declarePhoneNumber', function(phoneNumber) {
        socket.join(phoneNumber);
    });
  
});

http.listen(6543, function(){
  console.log('listening on *:6543');
});