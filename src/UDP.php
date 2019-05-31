<?php

namespace Bee\Server;


class UDP extends Server
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
}
