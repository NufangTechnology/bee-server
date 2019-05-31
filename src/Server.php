<?php

namespace Bee\Server;

use Swoole\Server as SwooleServer;
use Swoole\Process as SwooleProcess;
use Ahc\Cli\Output\Writer;

/**
 * 服务基类
 *
 * @package Bee\Server
 */
abstract class Server implements ServerInterface
{
    /** @var string */
    protected $name = 'bee-server';

    /** @var string */
    protected $host = '127.0.0.1';

    /** @var int */
    protected $port = 9527;

    /** @var int */
    protected $scheme = SWOOLE_SOCK_TCP;

    /** @var array */
    protected $option = [
        'pid_file'          => '/tmp/bee-server.pid',
        'log_file'          => '/tmp/bee_server.log',
        'worker_num'        => 4,
        'task_worker_num'   => 8,
        'task_tmpdir'       => '/tmp',
        'open_cpu_affinity' => true,
    ];

    /** @var integer */
    protected $pid;

    /** @var string */
    protected $pidFile;

    /** @var SwooleServer */
    protected $swoole;

    /** @var Writer */
    protected $output;

    /** @var array */
    protected $processes = [];

    /**
     * constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // 服务名称
        if (isset($config['name'])) {
            $this->name = $config['name'];
        }

        // 服务地址
        if (isset($config['host'])) {
            $this->host = $config['host'];
        }

        // 服务端口号
        if (isset($config['port'])) {
            $this->port = $config['port'];
        }

        if (isset($config['option'])) {
            $this->option = array_merge($this->option, $config['option']);
        }

        // pid 文件
        $this->pidFile = $this->option['pid_file'];
        // 内容输出
        $this->output = new Writer;
    }

    /**
     * 自定义工作进程初始化
     *
     * @return bool
     */
    protected function initProcess()
    {
        foreach ($this->processes as $class) {

            // 检查类是否有效，防止累不存在导致死循环错误
            if (!class_exists($class, true)) {
                throw new Exception('class not exist', 0, $class);
            }

            // 实例化自定义工作进程
            // 检查类类型，防止类类型错误导致死循环错误
            $object  = new $class;
            if (!$object instanceof Process) {
                throw new Exception('自定义进程必须继承 \Bee\Server\Process', 0);
            }

            // $item 为自定义进程基类，限制了子类方法与参数
            // 此处通过匿名函数内部调用，将完整参数注入自定义进程中
            $process = new \swoole_process(function ($process) use ($object) {
                $object->handle($this->swoole, $process);
            });

            // 挂载进程
            $result  = $this->swoole->addProcess($process);
            if ($result === false) {
                throw new Exception('自定义工作进程添加失败', 0, $class);
            }
        }

        return true;
    }

    /**
     * 获取服务所在的 host 地址
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * 获取服务所在端口号
     *
     * @return string
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * 获取服务协议类型
     *
     * @return int
     */
    public function getScheme(): int
    {
        return $this->scheme;
    }

    /**
     * 检查服务是否处于运行中
     *
     * @return bool
     */
    protected function isRunning(): bool
    {
        if ($this->pid) {
            return true;
        }

        if (!is_file($this->pidFile)) {
            return false;
        }

        $pid = @file_get_contents($this->pidFile);

        if (empty($pid)) {
            return false;
        }

        return SwooleProcess::kill(intval($pid), SIG_DFL);
    }

    /**
     * 注册回调方法
     *
     * @return $this
     */
    protected function registerCallback()
    {
        $handles = get_class_methods($this);

        foreach ($handles as $value) {
            if ('on' == substr($value, 0, 2)) {
                $this->swoole->on(lcfirst(substr($value, 2)), [$this, $value]);
            }
        }

        return $this;
    }

    /**
     * 获取去当前进程ID
     *
     * @return int
     */
    public function pid() : int
    {
        if (!$this->isRunning()) {
            return 0;
        }

        if (!empty($this->pid)) {
            return $this->pid;
        }

        $pid = @file_get_contents($this->pidFile);

        return intval($pid);
    }

    /**
     * 删除进程pid文件
     *
     * @throws Exception
     */
    private function deletePidFile()
    {
        if (!is_file($this->pidFile)) {
            return false;
        }

        @unlink($this->pidFile);

        if (is_file($this->pidFile)) {
            throw new Exception('进程pid文件删除失败');
        }

        return true;
    }

    /**
     * 新增工作进程
     *
     * @param SwooleProcess $process
     * @return void
     */
    public function addProcess($process)
    {
        $this->processes[] = $process;
    }

    /**
     * 启动服务
     *
     * @return void
     */
    abstract public function start($daemon = true);

    /**
     * 获取服务进程状态
     */
    public function status()
    {
        if (!$this->isRunning()) {
            $this->output->warn('没有运行中的服务', true);
            return;
        }

        $pid = $this->pid();

        // FIXME 重写进程数据提取方式，保证提取正确数据
        // // 根据主进程ID获取相关进程（子进程）运行信息
        // exec("ps -A -o user,pid,ppid,pmem,pcpu,stat,comm,cmd | grep -E '{$pid}|%MEM|{$this->name}'", $result);
        // // 删除最后两行（shell指令自身）
        // array_pop($result);
        // array_pop($result);
        // // 提取并输出菜单栏
        // $this->output->ok(array_shift($result), true);
        // // 输出进程状态明细
        // foreach ($result as $line) {
            // $this->output->write($line, true);
        // }
    }

    /**
     * 重新worker进程
     *  - 该操作只能重新载入Worker进程启动后加载的PHP文件，
     *
     *  @return void
     */
    public function reload()
    {
        if ($this->isRunning()) {
            SwooleProcess::kill($this->pid(), SIGUSR1);
        } else {
            $this->output->warn('未找到运行中的服务', true);
        }
    }


    /**
     * 重启服务
     *
     * @param bool $force
     * @throws Exception
     */
    public function restart($force = false)
    {
        if ($this->isRunning()) {
            if ($force) {
                $this->shutdown();
            } else {
                $this->stop();
            }
        }

        $this->start();
    }

    /**
     * 停止服务（平滑停止）
     */
    public function stop()
    {
        if ($this->isRunning() == false) {
            $this->output->warn('未找到运行中的服务', true);
            return;
        }

        // 发送服务关闭信号
        SwooleProcess::kill($this->pid(), SIGTERM);

        // 等待全部进程退出
        // 软关闭过程主进程会等待子进程全部退出后最后退出
        while (true) {
            if ($this->isRunning() == false) {
                break;
            }
        }
    }

    /**
     * 强制退出主进程及子进程
     *
     * @throws Exception
     */
    public function shutdown()
    {
        // 强制杀死进程
        exec("ps -ef | grep {$this->name} | grep -vE 'grep|watcher' | cut -c 9-15 | xargs kill -s 9");

        while (true) {
            if ($this->isRunning() == false) {
                // 删除进程pid文件
                $this->deletePidFile();
                break;
            }
        }
    }

    /**
     * Server启动在主进程的主线程回调此方法
     *
     * @param SwooleServer $server
     * @return void
     */
    public function onStart($server)
    {
        swoole_set_process_name($this->name . ':master');
    }

    /**
     * 进程启动
     *
     * @param SwooleServer $server
     * @param int $workerId
     * @return void
     */
    public function onWorkerStart($server, $workerId)
    {
        if ($server->taskworker) {
            swoole_set_process_name($this->name . ':task');
        } else {
            swoole_set_process_name($this->name . ':worker');
        }
    }

    public function onManagerStart($server) {}

    public function onManagerStop($server) {}

    public function onShutdown($server) {}

    public function onWorkerStop($server, $workerId) {}

    public function onWorkerExit($server, $workerId) {}

    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal) {}

    public function onTask($server, Task $task) {}

    public function onFinish($server, $taskId, $data) {}

    public function onPipeMessage($server, $originWorkerId, $message) {}
}

