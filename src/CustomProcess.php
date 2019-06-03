<?php

namespace Bee\Server;

use Swoole\Process as SwooleProcess;
use swoole_process;

/**
 * 自定义工作进程
 *
 * @package \Bee\Server
 */
class CustomProcess
{
    /** @var string */
    protected $class = '';

    /** @var SwooleProcess */
    protected $instance;

    /**
     * @param \Swoole\Server|\Swoole\HTTP\Server|\Swoole\WebSocket\Server $server
     * @param string $class
     */
    public function __construct(string $class)
    {
        // 提前初始化，防止进程内部初始化出错导致进程死循环
        /** @var ProcessInterface $worker */
        $worker  = new $class;

        if (!($worker instanceof ProcessInterface)) {
            throw new Exception('Class must instanceof ProcessInterface', 0, $class);
        }

        // 创建工作进程
        $process = new SwooleProcess(function (SwooleProcess $process) use ($worker) {
            $worker->handle($this->server, $process);
        });

        $this->class    = $class;
        $this->instance = $process;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return swoole_process
     */
    public function getInstance()
    {
        return $this->instance;
    }
}
