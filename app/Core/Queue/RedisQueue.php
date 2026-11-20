<?php
namespace App\Core\Queue;

use App\Core\RedisConnection;
use Redis;

/**
 * Redis 队列实现
 */
class RedisQueue implements DriverInterface
{
    protected string $channel; // 队列名称
    protected Redis $driver;

    public function __construct(string $channel)
    {
        $this->setChannel($channel);
        $this->driver = RedisConnection::instance("queue");
    }

    public function setChannel(string $channel)
    {
        $this->channel = $channel;
    }

    // 从头部获取
    public function pop()
    {
        $value = $this->driver->rpop($this->buildKey());
        if ($value) {
            return $this->decodeValue($value);
        }
        return false;
    }

    // 尾部写入队列
    public function push(mixed $data)
    {
        $value = $this->encodeValue($data);
        return $this->driver->lpush($this->buildKey(), $value);
    }

    // 头部写入
    public function rpush(mixed $data)
    {
        $value = $this->encodeValue($data);
        return $this->driver->rpush($this->buildKey(), $value);
    }

    // 获取消息队列长度
    public function length(): int
    {
        return $this->driver->llen($this->buildKey());
    }

    // 判断连接
    public function isConnected()
    {
        $status = false;
        try {
            $resp = $this->driver->ping();
            if ($resp == true || $resp == "+PONG") {
                $status = true;
            }
        } catch (\Throwable $e) {
            $status = false;
        }
        return $status;
    }

    /**
     * 保存到缓存的值
     *
     * @param mixed $value
     * @return string
     */
    protected function encodeValue(mixed $value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 取回的数据解码
     *
     * @param string $value
     * @return mixed
     */
    protected function decodeValue(string $value)
    {
        return json_decode($value, true);
    }

    // 统一增加前缀
    protected function buildKey()
    {
        return "QUEUE:". strtolower($this->channel);
    }
}
