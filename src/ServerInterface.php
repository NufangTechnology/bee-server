<?php

namespace Bee\Server;

use Swoole\Server\Task;

interface ServerInterface
{

    /**
     * Server启动在主进程的主线程回调此方法
     *
     * 在此事件之前Server已进行了如下操作<Paste>
     *  - 已创建了manager进程
     *  - 已创建了worker子进程
     *  - 已监听所有TCP/UDP/UnixSocket端口，但未开始Accept连接和请求
     *  - 已监听了定时器
     *
     * 接下来要执行
     *  - 主Reactor开始接收事件，客户端可以connect到Server
     *
     * @param Server $server
     */
    public function onStart($server);
    
    /**
     * Server正常结束时回调此方法
     *
     * 在此之前Swoole\Server已进行了如下操作
     *  - 已关闭所有Reactor线程、HeartbeatCheck线程、UdpRecv线程
     *  - 已关闭所有Worker进程、Task进程、User进程
     *  - 已close所有TCP/UDP/UnixSocket监听端口
     *  - 已关闭主Reactor
     *
     * @param Server $server
     */
    public function onShutdown($server);

    /**
     * 管理进程启动时
     *  - 本函数中可以修改管理进程的名称。
     *  - 注意，manager进程中不能添加定时器，不能使用task、async、coroutine等功能
     *  - onManagerStart回调时，Task和Worker进程已创建
     *
     * @param Server $server
     * @return mixed
     */
    public function onManagerStart($server);

    /**
     * 管理进程结束时回调该方法
     *  - onManagerStop触发时，说明Task和Worker进程已结束运行，已被Manager进程回收。
     *
     * @param Server $server
     * @return mixed
     */
    public function onManagerStop($server);

    /**
     * Worker进程/Task进程启动时回调此方法
     *
     * @param Server $server
     * @param integer $workerId
     */
    public function onWorkerStart($server, $workerId);

    /**
     * worker进程终止时回调此方法
     *  - 在此函数中回收worker进程申请的各类资源
     *
     * @param Server $server
     * @param integer $workerId
     */
    public function onWorkerStop($server, $workerId);

    /**
     * 异步重启特性
     *  - 旧的Worker进程在退出时，事件循环的每个周期结束时调用onWorkerExit通知Worker进程退出
     *  - 在onWorkerExit中尽可能地移除/关闭异步的Socket连接，
     *  - 最终底层检测到Reactor中事件监听的句柄数量为0时退出进程。
     *
     * @param Server $server
     * @param $workerId
     */
    public function onWorkerExit($server, $workerId);

    /**
     * worker进程异常时回调此方法
     * 此函数主要用于报警和监控
     *
     * @param Server $server
     * @param integer $workerId
     * @param integer $workerPid
     * @param integer $exitCode
     * @param integer $signal
     *
     * @return mixed
     */
    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal);

    /**
     * task异步回调处理任务时回调此方法
     *
     * @param Server $server
     * @param \Swoole\Server\Task $task
     */
    public function onTask($server, $task);

    /**
     * worker进程都低的任务完成后回调此方法
     *
     * @param Server $server
     * @param integer $taskId
     * @param mixed $data
     */
    public function onFinish($server, $taskId, $data);

    /**
     * 工作进程收到由 sendMessage 发送的管道消息
     *
     * @param Server $server
     * @param int $originWorkerId
     * @param mixed $message
     * @return void
     */
    public function onPipeMessage($server, $originWorkerId, $message);
}

