<?php
namespace App\Core\Queue;

use Exception;

/**
 * 消息队列
 */
class Queue implements QueueInterface
{
    protected string $channel; // 队列名称
    protected ?DriverInterface $driver = null;
    protected static array $configs = []; // 配置文件

    public function __construct(string $channel, string $driverName = "redis")
    {
        $this->setChannel($channel);

        if ($driverName == "redis") {
            $this->driver = new RedisQueue($this->channel);
        } else {
            throw new Exception("Not found queue driver!");
        }
    }

    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    // 从头部取出
    public function pop()
    {
        return $this->driver->pop();
    }

    // 写入队列尾
    public function push(mixed $data)
    {
        $this->driver->push($data);
    }

    // 写入队列头部
    public function rpush(mixed $data)
    {
        $this->driver->rpush($data);
    }

    // 获取队列长度
    public function length()
    {
        return $this->driver->length();
    }

    // 设置
    protected function setChannel(string $channel)
    {
        $channel = trim($channel);
        $this->validateChannel($channel);
        $this->channel = $channel;
    }

    // 验证
    protected function validateChannel(string $channel)
    {
        if ($channel == "") {
            throw new Exception("Invalid channel value! [{$channel}]");
        }
        if (preg_match("/^[a-zA-Z0-9\-\_\:\.]+$/", $channel) == false) {
            throw new Exception("Invalid channel value! [{$channel}]");
        }
    }
}
