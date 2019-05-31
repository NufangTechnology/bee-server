<?php

namespace Bee\Server;

use Swoole\Http\Server as SwooleHttpServer;
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

        // 设置进程名称
        swoole_set_process_name($this->name . ':reactor');

        // 服务对象初始化
        $this->swoole = new SwooleHttpServer($this->host, $this->port);
        $this->registerCallback();

        // 自定义工作进程初始化
        $this->initProcess();

        // 启动 HTTP 服务
        $this->swoole->set($this->option);
        $this->swoole->start();
    }

    /**
     * Http请求进来时回调此方法
     *
     * @param Request $request
     * @param Response $response
     */
    abstract public function onRequest($request, $response);
}

