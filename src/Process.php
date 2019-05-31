<?php

namespace Bee\Server;

use Swoole\Server as SwooleServer;
use Swoole\Process as SwooleProcess;

/**
 * 用户自定义进程基类
 *
 * @package Bee\Server
 */
abstract class Process
{
    /**
     * 执行自定义业务
     *
     * @param SwooleServer $server
     * @param SwooleProcess $process
     * @return mixed
     */
    abstract public function handle($server, $process);
}
