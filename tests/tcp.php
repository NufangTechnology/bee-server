<?php
require dirname(__DIR__) . '/vendor/autoload.php';


class tcp extends \Bee\Server\TCP
{
    public function onConnect($server, $fd, $reactorId)
    {
    }

    public function onReceive($server, $fd, $reactorId, $data)
    {
        $server->send($fd, $data);
    }

    public function onClose($server, $fd, $reactorId)
    {
    }
}

$tcp = new tcp(
    [
        'host' => '0.0.0.0',
        'port' => 9001,
        'option' => [
            'pid_file'          => __DIR__ . '/bee-server.pid',
            'log_file'          => __DIR__ . '/bee_server.log',
            'worker_num'        => 4,
            'task_worker_num'   => 8,
            'task_tmpdir'       => '/tmp',
            'open_cpu_affinity' => true,
        ]
    ]
);
$tcp->start(false);
