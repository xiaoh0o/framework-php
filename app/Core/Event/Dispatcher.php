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

    public function run()
    {
        $event    = new Event();
        $queue    = $event->queue();
        $logger   = $event->logger();
        $handlers = $event->handlers();

        // 循环处理
        for ($number = 0; $number < $this->maxHandleNumber; $number++) {
            try {
                $data = $queue->pop();
                if ($data) {
                    // 处理数据
                    $worker = new Worker();
                    $worker->setLogger($logger);
                    $worker->setHandlers($handlers);
                    $worker->setData($data);
                    $worker->run();
                } else {
                    // 没有记录，跳出循环
                    break;
                }
            } catch (\Throwable $e) {
                $logger->error("Event worker failed", [
                    "message" => $e->getMessage()
                ]);
            }
        }

        // 当没有处理任何数据时，暂停1秒
        if ($number == 0) {
            sleep(1);
        }

        exit(0);
    }
}
