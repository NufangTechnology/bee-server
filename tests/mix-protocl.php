<?php

$httpServer = new \Swoole\Http\Server('0.0.0.0', 9001);

$httpServer->on('request', function ($req, $res) {
    $res->end('request');
});

/** @var \Swoole\Server $tcpServer */
$tcpServer = $httpServer->addlistener('0.0.0.0', 9002, SWOOLE_SOCK_TCP);
$tcpServer->set([]);
$tcpServer->on('receive', function ($server, $fd, $threadId, $data) {
    fwrite(STDOUT, $data);
    fwrite(STDOUT, $fd);
    /** @var \Swoole\Server $server */
    $server->send($fd, $data);
});

$httpServer->start();

