<?php
namespace App\Core\Queue;

use Exception;

/**
 * 消息队列
 */
class Queue implements QueueInterface
{
    protected string $channel; // 队列名称
    protected DriverInterface $driver;
    protected static array $configs = []; // 配置文件

    /**
     * 配置连接数据
     *
     * @param array $configs
     * [
     *    "redis" => [
     *        "host" => "127.0.0.1",
     *        "port" => 6379,
     *        "auth" => "",
     *        "database" => 0
     *    ]
     * ]
     */
    public static function configs(array $configs)
    {
        static::$configs = $configs;
    }

    public function __construct(string $channel, string $driverName = "redis")
    {
        $this->channel = trim($channel);
        if ($driverName == "redis" && isset(static::$configs[$driverName])) {
            $driver = new RedisQueue(static::$configs[$driverName]);
            $driver->setChannel($this->channel);
            $this->driver = $driver;
        } else {
            throw new Exception("Not found driver!");
        }
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
}
