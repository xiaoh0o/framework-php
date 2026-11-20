<?php
namespace App\Core\Cache;

use Exception;

/**
 * 缓存
 */
class Cache implements CacheInterface
{
    protected CacheDriverInterface $driver;
    protected string $prefix = ""; // 前缀
    protected int $expires = 604800; // 几秒钟后过期

    public function __construct(string $driverName = "redis")
    {
        if ($driverName == "redis") {
            $driver = new RedisCache();
            $this->setDriver($driver);
        } else {
            throw new Exception("Not found cache driver!");
        }
    }

    public function setDriver(CacheDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * 判断键是否存在
     *
     * @param string|int $key
     * @return boolean
     */
    public function has(string|int $key): bool
    {
        $cacheKey = $this->buildKey($key);
        return $this->driver->has($cacheKey);
    }

    /**
     * 获取一条缓存信息（没有查询到 返回 null）
     *
     * @param string|int $key
     * @return mixed
     */
    public function get(string|int $key): mixed
    {
        $cacheKey = $this->buildKey($key);
        return $this->driver->get($cacheKey);
    }

    /**
     * 批量获取
     *
     * @param array $keys
     * @return array
     */
    public function mget(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }
        $keys = array_unique($keys);
        $cacheKeys = [];
        $matchKeys = []; // 保存 cacheKey => key 关系
        foreach ($keys as $key) {
            $cacheKey = $this->buildKey($key);
            $cacheKeys[] = $cacheKey;
            $matchKeys[$cacheKey] = $key;
        }

        $result = [];
        $resources = $this->driver->mget($cacheKeys);
        if ($resources) {
            foreach ($resources as $cacheKey => $value) {
                $key = $matchKeys[$cacheKey];
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 设置一条缓存信息
     *
     * @param string $key
     * @param mixed $value
     * @param ?integer $expires
     * @return bool
     */
    public function set(string|int $key, mixed $value, ?int $expires = null): bool
    {
        $cacheKey = $this->buildKey($key);
        if (is_null($expires) || $expires <= 0) {
            $expires = $this->expires;
        }
        return $this->driver->set($cacheKey, $value, $expires);
    }

    /**
     * 删除
     *
     * @param string|int|array $key
     * @return int // 删除的数量
     */
    public function delete(string|int|array $key): int
    {
        $cacheKey = $this->buildKey($key);
        return $this->driver->delete($cacheKey);
    }

    /**
     * 自增 （必须为INT）
     *
     * @param string|int $key
     * @param integer $number
     * @return integer // 结果值
     */
    public function incr(string|int $key, int $number = 1): int
    {
        $cacheKey = $this->buildKey($key);
        return $this->driver->incr($cacheKey, $number);
    }

    /**
     * 自减 （必须为INT）
     *
     * @param string|int $key
     * @param integer $number
     * @return integer // 结果值
     */
    public function decr(string|int $key, int $number = 1): int
    {
        $cacheKey = $this->buildKey($key);
        return $this->driver->decr($cacheKey, $number);
    }

    // 设置缓存前缀
    public function setPrefix(string $prefix)
    {
        $prefix = trim($prefix);
        if (preg_match("/^[a-zA-Z0-9\-\_\:\.]+$/", $prefix)) {
            $this->prefix = $prefix;
        } else {
            throw new Exception("Invalid prefix string!");
        }
    }

    // 设置全局过期时间
    public function setExpires(int $expires)
    {
        if ($expires > 0) {
            $this->expires = $expires;
        } else {
            throw new Exception("无效的过期秒数！");
        }
    }

    // 缓存键
    protected function buildKey(string|int $key): string
    {
        if ($key == "") {
            throw new Exception("缓存键不能为空！[$key]");
        }
        $key = strtolower($key);
        if ($this->prefix == "") {
            $cacheKey = $key;
        } else {
            $cacheKey = "{$this->prefix}:{$key}";
        }
        $this->validateKey($cacheKey);
        return $cacheKey;
    }

    // 验证Key合法性
    protected function validateKey(string $key)
    {
        if (preg_match("/^[a-zA-Z0-9\-\_\:\.]+$/", $key)) {
            return $key;
        }
        throw new Exception("Invalid cache key.");
    }
}
