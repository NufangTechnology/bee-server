<?php

namespace Bee\Server;

use Swoole\WebSocket\Server as SwooleWebsocketServer;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;

/**
 * WS 服务
 *
 * @package Bee\Server
 */
abstract class Websocket extends Server
{
    /**
     * 服务对象初始化
     *
     * @return void
     */
    public function initServer()
    {
        $this->swoole = new SwooleWebsocketServer($this->host, $this->port);
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

        // 启动 Websocket 服务
        $this->swoole->set($this->option);
        $this->swoole->start();
    }

    /**
     * 客户端打开连接时回调方法
     *  - 连接检查/身份鉴权
     *
     * @param SwooleWebsocketServer $server
     * @param Request $request
     */
    abstract public function onOpen($server, $request);

    /**
     * 客户端消息接收时回调方法
     *  $frame->data = [
     *      c: 动作码（0,0/主码,子码）
     *      d: 数据体
     *  ]
     *
     * @param SwooleWebsocketServer $server
     * @param Frame $frame
     */
    abstract public function onMessage($server, $frame);
}

