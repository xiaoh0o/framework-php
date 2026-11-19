<?php
namespace App\Core\Event;

use App\Core\Configure;

// 事件
class Event implements EventInterface
{
    protected ?EventQueue  $queue    = null;
    protected ?EventLogger $logger   = null;
    protected ?array       $handlers = null;

    // 发布消息
    public function publish(string $name, mixed $data, string $position = "lpush"): void
    {
        $message = [
            "name" => $name,
            "data" => $data,
            "time" => time(),
        ];

        if ($position == "rpush") {
            // 写入队列的右端
            $this->queue()->rpush($message);
        } else {
            // 写入队列的左端
            $this->queue()->push($message);
        }

        // 处理时 从右端取值

        // 记录日志
        try {
            $this->logger()->info("publish", $message);
        } catch (\Throwable $e) {
        }
    }

    // 队列
    public function queue(): EventQueue
    {
        if (is_null($this->queue)) {
            $this->queue = new EventQueue();
        }
        return $this->queue;
    }

    // 日志
    public function logger(): EventLogger
    {
        if (is_null($this->logger)) {
            $this->logger = new EventLogger();
        }
        return $this->logger;
    }

    // 处理器
    public function handlers(): array
    {
        if (is_null($this->handlers)) {
            $this->handlers = $this->loadHandlers();
        }
        return $this->handlers;
    }

    // 加载配置文件中的事件处理器
    protected function loadHandlers(): array
    {
        $handlerMaps = [];
        $configs = Configure::get("event");
        if ($configs && count($configs) > 0) {
            foreach ($configs as $name => $handlers) {
                $handlers = (array)$handlers;
                foreach ($handlers as $handler) {
                    if (!isset($handlerMaps[$name])) {
                        $handlerMaps[$name] = [];
                    }
                    $handlerMaps[$name][] = $handler;
                }
            }
        }
        return $handlerMaps;
    }

    // 重置数据
    public function reset()
    {
        $this->queue    = null;
        $this->logger   = null;
        $this->handlers = null;
    }
}
