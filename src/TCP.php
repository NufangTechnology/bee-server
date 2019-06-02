<?php

namespace Bee\Server;

use Swoole\Server as SwooleServer;

/**
 * TCP 服务
 *
 * @package Bee\Server
 */
abstract class TCP extends Server
{
    /**
     * 初始化服务Server
     *
     * @return SwooleServer
     */
    public function createServer()
    {
        return new SwooleServer($this->host, $this->port);
    }

    /**
     * 有新的连接进入时，在worker进程中回调此函数
     *
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     * @return void
     */
    abstract public function onConnect($server, $fd, $reactorId);

    /**
     * 接收到数据时回调此函数，在worker进程中回调此函数
     *
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     * @return void
     */
    abstract public function onReceive($server, $fd, $reactorId, $data);

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     *
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     * @return void
     */
    abstract public function onClose($server, $fd, $reactorId);
}

