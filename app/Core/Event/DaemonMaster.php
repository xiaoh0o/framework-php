<?php
namespace App\Core\Event;

use Exception;

/**
 * 守护进程
 */
class DaemonMaster
{
    protected int $maxWorkers = 5; // 最大进程数
    protected int $maxHandleNumber = 1; // 一个进程最大处理数量（一个 Worker 消费几个数据后退出）
    protected array $childs = [];
    protected EventStore $store;
    protected EventLogger $logger;
    protected string $signal = "";

    public function __construct()
    {
        if (! function_exists("pcntl_fork")) {
            throw new Exception("pcntl_fork not found");
        }
        $this->store = new EventStore();
        $this->logger = (new Event())->logger();
    }

    // 设置最大工作进程数
    public function setMaxWorkers(int $maxWorkers)
    {
        if ($maxWorkers > 0) {
            $this->maxWorkers = $maxWorkers;
        }
    }

    public function setMaxHandleNumber(int $maxHandleNumber)
    {
        if ($maxHandleNumber > 0 && $maxHandleNumber < 99) {
            $this->maxHandleNumber = $maxHandleNumber;
        }
    }

    /**
     * 开启
     *
     * 父进程 Parent
     *   └── 主进程 Master
     *         └── 子进程 Child
     *         └── 子进程
     *         └── 子进程
     *         └── 子进程
     *         └── 子进程
     */
    public function run()
    {
        // 终止信号
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);

        $mPid = pcntl_fork();
        if ($mPid == -1) {
            throw new Exception("pcntl_fork fork failed");
        }

        if ($mPid > 0) {
            // 父进程结束
            exit();
        }

        // 主进程
        posix_setsid(); // 设置新会话组长，脱离终端

        // 保存主进程ID
        $mainPid = posix_getpid();
        $this->store->set($mainPid);
        echo "Event main process start [{$mainPid}]".PHP_EOL;

        /**
         * 创建初始进程
        */
        for ($i = 0; $i < $this->maxWorkers; $i++) {
            $this->forkWorker();
        }

        while (true) {
            // 判断是否退出
            pcntl_signal_dispatch();

            // 向所有子进程发出 退出信号
            if ($this->signal == "exit") {
                foreach ($this->childs as $pid) {
                    posix_kill($pid, SIGTERM);
                    unset($this->childs[$pid]);
                }
                break;
            }

            foreach ($this->childs as $pid) {
                $res = pcntl_waitpid($pid, $status, WNOHANG);
                if ($res > 0 || $res == -1) {
                    $this->forkWorker();
                    unset($this->childs[$pid]);
                }
            }

            sleep(1);
        }

        // 主进程退出
        exit(0);
    }

    // 停止
    public function stop()
    {
        $mainPid = $this->store->get();

        // 判断
        if (empty($mainPid)) {
            echo "Not found event main process".PHP_EOL;
            return 0;
        }

        // 删除保存的主进程ID
        $this->store->delete();

        // 向主进程 发出退出信号
        $this->logger->info("Event main process stopping [{$mainPid}]");
        posix_kill($mainPid, SIGTERM);

        // 判断主进程是否退出
        pcntl_waitpid($mainPid, $status, WNOHANG);

        $message = "Event main process stopped [{$mainPid}]";
        $this->logger->info($message);
        echo $message.PHP_EOL;
    }

    // 开启分支
    protected function forkWorker()
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new Exception("pcntl_fork fork failed");
        }

        if ($pid > 0) {
            $this->childs[$pid] = $pid;
        }

        // 子进程 消费
        if ($pid == 0) {
            $dispatcher = new Dispatcher();
            $dispatcher->setMaxHandleNumber($this->maxHandleNumber);
            $dispatcher->run();
            exit(0);
        }
    }

    // 信号处理
    public function signalHandler(int $signo)
    {
        switch ($signo) {
            case SIGTERM: // 停止信号
                $this->signal = "exit";
                $this->store->delete();
                break;
            default:
                $this->signal = "";
        }
    }
}
