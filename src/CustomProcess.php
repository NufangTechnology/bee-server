<?php

namespace Bee\Server;

use Swoole\Process as SwooleProcess;

/**
 * 自定义工作进程
 *
 * @package \Bee\Server
 */
class CustomProcess
{
    /** @var \Swoole\Server|\Swoole\HTTP\Server|\Swoole\WebSocket\Server */
    protected $server;

    /** @var string */
    protected $class = '';

    /** @var SwooleProcess */
    protected $instance;

    /**
     * @param \Swoole\Server|\Swoole\HTTP\Server|\Swoole\WebSocket\Serve $server
     * @param string $class
     */
    public function __construct($server, string $class)
    {
        $this->server = $server;
        $this->class  = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return \swoole_process
     */
    public function getInstance()
    {
        // 提前初始化，防止进程内部初始化出错导致进程死循环
        /** @var ProcessInterface $worker */
        $worker  = new $this->class;

        // 创建工作进程
        $process = new SwooleProcess(function (SwooleProcess $process) use ($worker) {
            $worker->handle($this->server, $process);
        });

        return $process;
    }
}
