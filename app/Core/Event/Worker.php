<?php
namespace App\Core\Event;

/**
 * 处理队列消息
 */
class Worker
{
    protected EventLogger $logger;
    protected array $handlers = [];
    protected array $data = [];

    public function setLogger(EventLogger $logger)
    {
        $this->logger = $logger;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function setHandlers(array $handlers)
    {
        $this->handlers = $handlers;
    }

    // 处理消息
    public function run()
    {
        $name = $this->data['name'];
        $data = $this->data['data'];

        if (!isset($this->handlers[$name])) {
            $this->logger->warning("Not found event handler", $this->data);
            return;
        }

        // 循环执行事件处理类的处理方法
        foreach ($this->handlers[$name] as $handler) {
            try {
                app($handler)->handle($data);

                // 记录成功
                $this->logger->info("Worker handle success", [
                    "handler" => $handler,
                    "data"    => $this->data
                ]);
            } catch (\Throwable $e) {
                // 记录失败
                $this->logger->error("Worker handle failed", [
                    "handler" => $handler,
                    "data"    => $this->data,
                    "message" => [
                        "message" => $e->getMessage(),
                        "line"    => $e->getLine(),
                        "file"    => $e->getFile(),
                        "code"    => $e->getCode(),
                    ]
                ]);
            }
        }
    }
}
