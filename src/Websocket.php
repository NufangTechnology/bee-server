<?php

namespace Bee\Server;

use Swoole\WebSocket\Server as SwooleServer;
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
     * 初始化服务Server
     *
     * @return SwooleServer
     */
    public function createServer()
    {
        return new SwooleServer($this->host, $this->port);
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

    /**
     * 连接关闭
     *
     * @param SwooleWebsocketServer $server
     * @param int $fd
     * @param int $reatorId
     * @return void
     */
    abstract public function onClose($server, $fd, $reatorId);
}

