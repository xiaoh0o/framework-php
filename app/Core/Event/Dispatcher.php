<?php
namespace App\Core\Event;

/**
 * 分配任务
 */
class Dispatcher
{
    protected int $maxHandleNumber = 1; // 最大处理量

    public function setMaxHandleNumber(int $maxHandleNumber)
    {
        if ($maxHandleNumber > 0 && $maxHandleNumber < 99) {
            $this->maxHandleNumber = $maxHandleNumber;
        }
    }

    public function run(?bool &$running = null)
    {
        $running ??= true;

        $event    = new Event();
        $queue    = $event->queue();
        $logger   = $event->logger();
        $handlers = $event->handlers();

        $number = 0;
        while ($running) {
            // 1) 分发待处理的信号（让 handler 有机会执行）
            pcntl_signal_dispatch();

            // 2) 信号可能刚把 running 置 false，立即检查
            if (!$running) {
                break;
            }

            // 3) 取一条数据（阻塞或非阻塞，见下文）
            $data = $queue->pop();
            if (empty($data)) {
                // 没数据，短暂休眠，避免空转
                usleep(200000);
                continue;
            }

            // 4) 处理数据
            $worker = new Worker();
            $worker->setLogger($logger);
            $worker->setHandlers($handlers);
            $worker->setData($data);
            $worker->run();

            $number += 1;

            // 5) 退出
            if ($number >= $this->maxHandleNumber) {
                break;
            }
        }
    }
}
