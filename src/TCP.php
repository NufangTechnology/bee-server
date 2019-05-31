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
     * 服务对象初始化
     *
     * @return void
     */
    public function initServer()
    {
        $this->swoole = new SwooleServer($this->host, $this->port);
        $this->registerCallback();
    }

    /**
     * 启动服务
     *
     * @param bool $daemonize 是否以守护进程模式运行
     * @return void
     */
    public function start($daemon = true)
    {
        if ($this->isRunning()) {
            $this->output->warn("无效操作，服务已经在[{$this->host}:{$this->port}]运行！");
            return;
        }

        // 以守护模式运行
        if ($daemon) {
            $this->option['daemonize'] = true;
        }

        // 启动 TCP 服务
        $this->swoole->set($this->option);
        $this->swoole->start();
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

