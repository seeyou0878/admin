<?php

$table = new swoole_table(1024);
$table->column('fd', swoole_table::TYPE_INT, 4);
$table->column('id', swoole_table::TYPE_INT, 4);
$table->column('session_id', swoole_table::TYPE_STRING, 128);
$table->create();

//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("0.0.0.0", 9502, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$ws->set(array(
    'ssl_cert_file' => '/etc/nginx/ssl/sw.888bk.net.crt',
    'ssl_key_file' => '/etc/nginx/ssl/sw.888bk.net.key',
));
$ws->table = $table;

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $frame) {  
    $redis = new Swoole\Coroutine\Redis();
    $redis->connect('127.0.0.1', 6379);

    //get session id & member_id from querystring
    $str = $frame->get['auth'];
    $arr = json_decode(base64_decode($str), true);
    $ws->table->set($frame->fd, array('fd' => $frame->fd));

    //check if client or admin
    if (isset($arr['member_id'])) {
        //client
        $member_id = $arr['member_id'];
        $fd_session_id = $arr['session_id']??'';

        $ws->table->set($frame->fd, array('id' => $member_id)); 
        $ws->table->set($frame->fd, array('session_id' => $fd_session_id)); 

        $session = $redis->get('member_login_' . $member_id) ?? 0;

        if($fd_session_id != $session){
            echo 'on open kick session ' . $fd_session_id . "\n";
            $ws->push($frame->fd, json_encode(['method' => 'logout']));
        }else if($fd_session_id == $session){
            echo 'on open same session ' . $fd_session_id . "\n";
        }
    }else{
        //admin
        $fd_branch_id = $arr['branch_id'];
    }

    //subscribe to redis
    while (true) {
        $val = $redis->subscribe(['client']);

        //close unused connections
        if (!$ws->table->get($frame->fd)) {
            break;
        }

        //message received from redis.
        if($val){
            $msg = json_decode($val[2], true);
            $id = $msg['member_id']??'';
            $session_id = $msg['session_id']??'';
            $branch_id = $msg['branch_id']??'';
			
            switch($msg['method']){
                case 'logout':
                    echo 'login session ' . $session_id . "\n";
                    if(($member_id ?? 0) == $id && ($fd_session_id != $session_id)){
                        $ws->push($frame->fd, json_encode(['method' => 'logout']));
                        echo 'kick session ' . $fd_session_id . "\n";
                    }
                    break;
                case 'unread':
                    if(($member_id ?? 0) == $id){
                        $ws->push($frame->fd, json_encode(['method' => 'unread', 'data' => $msg['data']]));
                    }
                    break;
                case 'notice':
                    if(($fd_branch_id ?? 0) == $branch_id){
                        $ws->push($frame->fd, json_encode(['method' => 'notice', 'data' => $msg['data']]));
                    }
                    break;
                default:
                break;
            }
        }
    }
}); 

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
 
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n"; 
    $ws->table->del($fd);//从table中删除断开的id
});

$ws->start();
