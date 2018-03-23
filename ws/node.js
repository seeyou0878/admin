
var frame = [];

const redis = require("redis");
const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 9502 });

wss.on('connection', function connection(ws, req){
	
	// parse params
	var params = req.url.split('?')[1].split('&');
	var get = {}
	for(var i  in params){
		var tmp = params[i].split(/=/);
		get[tmp[0]] = tmp[1];
	}
	
	var arr = JSON.parse(new Buffer(get['auth'], 'base64').toString('ascii'));
	var fd = ws._socket._handle.fd;
	
	if('member_id' in arr){
		// front
		var sub = redis.createClient();
		frame[fd] = {member_id: arr['member_id'], session_id: arr['session_id'], ws: ws, sub: sub};
		console.log('client-' + fd + '(front) connect');
		
		// check session first
		var tunnel = redis.createClient();
		tunnel.get('member_login_' + arr['member_id'], function(err, reply){
			
			if(reply == arr['session_id']){
				console.log('on open same session ' + arr['session_id']);
			}else{
				console.log('on open kick session ' + arr['session_id']);
				frame[fd]['ws'].send(JSON.stringify({method: 'logout'}));
			}
		});
		
		sub.subscribe('client');
		sub.on('message', function(channel, message){
			try{
				var msg = JSON.parse(message);
				
				switch(msg['method']){
					case 'logout':
						console.log('login session ' + msg['session_id']);
						if(msg['member_id'] == frame[fd]['member_id'] && msg['session_id'] != frame[fd]['session_id']){
							frame[fd]['ws'].send(JSON.stringify({method: 'logout'}));
							console.log('kick session ' + frame[fd]['session_id']);
						}
						break;
					case 'unread':
						if(msg['member_id'] == frame[fd]['member_id']){
							frame[fd]['ws'].send(JSON.stringify({method: 'unread', data: msg['data']}));
						}
						break;
					default:
						break;
				}
			}catch(err){}
		});
		
	}else{
		// admin
		var sub = redis.createClient();
		frame[fd] = {branch_id: arr['branch_id'], ws: ws, sub: sub};
		console.log('client-' + fd + '(admin branch_id:' + arr['branch_id'] + ') connect');
		
		sub.subscribe('client');
		sub.on('message', function(channel, message){
			try{
				var msg = JSON.parse(message);
				
				switch(msg['method']){
					case 'notice':
						if(msg['branch_id'] == frame[fd]['branch_id']){
							frame[fd]['ws'].send(JSON.stringify({method: 'notice', data: msg['data']}));
						}
						break;
					default:
						break;
				}
			}catch(err){}
		});
	}
	
	ws.on('message', function incoming(message){
		//console.log('received: %s', message);
	});
	
	ws.on('close', function(){
		console.log('client-' + fd + ' is closed');
		frame[fd].sub.unsubscribe();
	});
});