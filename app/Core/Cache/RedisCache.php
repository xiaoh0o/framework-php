<?php
namespace App\Core\Cache;

use App\Core\RedisConnection;
use Redis;

/**
 * Redis 缓存 保存的值都是 STIRNG
*/
class RedisCache implements CacheDriverInterface
{
    protected Redis $driver;

    public function __construct()
    {
        $this->driver = RedisConnection::instance("cache");
    }

    public function has(string $key): bool
    {
        return $this->driver->exists($key);
    }

    /**
     * 获取缓存值 （key 不存在时 返回 null）
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        $value = $this->driver->get($key);
        if ($value !== false) {
            return $this->decodeValue($value);
        }
        return null;
    }

    /**
     * 设置
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $expires
     * @return bool
     */
    public function set(string $key, mixed $value, ?int $expires = null): bool
    {
        $value = $this->encodeValue($value);
        if (is_int($expires) && $expires > 0) {
            return $this->driver->setex($key, $expires, $value);
        } else {
            return $this->driver->set($key, $value);
        }
    }

    // 设置过期时间
    public function expire(string $key, int $seconds): void
    {
        $this->driver->expire($key, $seconds);
    }

    /**
     * 删除
     *
     * @param string|array $key
     * @return int // 删除的数量
     */
    public function delete(string|array $key): int
    {
        if ($key === "") {
            return 0;
        }
        if (is_array($key) && count($key) == 0) {
            return 0;
        }

        return $this->driver->del($key);
    }

    /**
     * 获取多个
     *
     * @param array $keys
     * @return array
     */
    public function mget(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $result = [];
        $resources = $this->driver->mget($keys);
        if ($resources) {
            foreach ($resources as $index => $value) {
                $key = $keys[$index];
                if ($value !== false) {
                    $result[$key] = $this->decodeValue($value);
                }
            }
        }
        return $result;
    }

    /**
     * 自增
     * 如果 key 不存在，创建并初始化为 0，然后再自增
     *
     * @param string $key
     * @param int $number
     * @return int
     */
    public function incr(string $key, int $number): int
    {
        $number = abs($number);
        return $this->driver->incrBy($key, $number);
    }

    /**
     * 自减
     * 如果 key 不存在，创建并初始化为 0，然后再自减
     *
     * @param string $key
     * @param int $number
     * @return int
     */
    public function decr(string $key, int $number): int
    {
        $number = abs($number);
        return $this->driver->decrBy($key, $number);
    }

    // 获取剩余时间（秒）
    public function ttl(string $key)
    {
        return $this->driver->ttl($key);
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
}
