<?php
namespace App\Core\Queue;

use Redis;

/**
 * Redis 队列实现
 */
class RedisQueue implements DriverInterface
{
    protected string $channel; // 队列名称
    protected array $config; // Redis配置文件名称
    protected Redis $driver;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    public function setChannel(string $channel)
    {
        $this->channel = $channel;
    }

    // 从头部获取
    public function pop()
    {
        $value = $this->driver->rpop($this->channel);
        if ($value) {
            return $this->decodeValue($value);
        }
        return false;
    }

    // 尾部写入队列
    public function push(mixed $data)
    {
        $value = $this->encodeValue($data);
        return $this->driver->lpush($this->channel, $value);
    }

    // 头部写入
    public function rpush(mixed $data)
    {
        $value = $this->encodeValue($data);
        return $this->driver->rpush($this->channel, $value);
    }

    // 获取消息队列长度
    public function length(): int
    {
        return $this->driver->llen($this->channel);
    }

    // 关闭连接
    public function close()
    {
        $this->driver->close();
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

    // 链接
    public function connect(): Redis
    {
        $host     = $this->config['host'];
        $port     = $this->config['port'];
        $password = $this->config['auth'] ?? "";
        $database = $this->config['database'];

        $driver = new Redis();
        $status = $driver->pconnect($host, $port, 1, null, 5, 60, ['auth' => [$password]]);
        if ($status) {
            $driver->select($database);
        } else {
            throw new \Exception("Connect redis server failed!");
        }
        $this->driver = $driver;
        return $driver;
    }

    /**
     * 保存到缓存的值
     *
     * @param mixed $value
     * @return string
     */
    protected function encodeValue(mixed $value)
    {
        return json_encode($value);
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
}
