<?php

namespace Bee\Server;

use Swoole\Http\Server as SwooleServer;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * HTTP 服务
 *
 * @package Bee\Server
 */
abstract class HTTP extends Server
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
     * Http请求进来时回调此方法
     *
     * @param Request $request
     * @param Response $response
     */
    abstract public function onRequest($request, $response);
}

