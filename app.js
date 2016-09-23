
var morgan = require('morgan');

var express = require("express");
var app = new express();

var http = require("http").Server(app);

// WebSocket server
var io = require("socket.io")(http);
//io.on('connection', require('./lib/socket'));

var Log = require('log'),
	log = new Log('debug');

var port = process.env.PORT || 3000;

app.use(express.static(__dirname + "/public"));

app.get('/', function( req, res) {
	res.redirect('index.html');
});

io.on('connection', function(socket) {
	socket.on('stream', function(image) {
		socket.broadcast.emit('stream', image );	
	});
});

http.listen( port, function() {
	log.info('Servidor escuchando a traves del puerto %s', port);
});

//module.exports.app = app;

