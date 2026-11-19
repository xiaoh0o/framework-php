<?php
namespace App\Core\Event;

use App\Core\Configure;

// 事件
class Event implements EventInterface
{
    protected static EventQueue $queue;
    protected static EventLogger $logger;
    protected static ?array $handlers = null;

    protected static int $queueTime   = 0;
    protected static int $handlerTime = 0;

    // 发布消息
    public function publish(string $name, mixed $data, string $position = "tail"): void
    {
        $message = [
            "name" => $name,
            "data" => $data,
            "time" => time(),
        ];

        // 加入事件队列
        if ($position == "head") {
            $this->queue()->rpush($message);
        } else {
            $this->queue()->push($message);
        }

        // 记录日志
        $this->logger()->info("publish", $message);
    }

    // 队列
    public function queue(): EventQueue
    {
        if (is_null(static::$queue) || time() - static::$queueTime > 300) {
            static::$queueTime = time();
            static::$queue = new EventQueue();
        }
        return static::$queue;
    }

    // 日志
    public function logger(): EventLogger
    {
        if (is_null(static::$logger)) {
            static::$logger = new EventLogger();
        }
        return static::$logger;
    }

    // 处理器
    public function handlers(): array
    {
        if (is_null(static::$handlers) && time() - static::$handlerTime > 300) {
            static::$handlers = $this->loadHandlers();
            static::$handlerTime = time();
        }
        return static::$handlers;
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

                    // 注意：这里可以写入多次同一个处理类，可能会被执行多次
                    $handlerMaps[$name][] = $handler;
                }
            }
        }
        return $handlerMaps;
    }
}
