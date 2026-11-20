<?php
namespace App\Core;

use Exception;
use Redis;

/**
 * 统一处理 Redis 连接问题
 */
class RedisConnection
{
    protected static ?Redis $instance = null;

    public static function connect(string $host, int $port, string $password, int $database)
    {
        if (!class_exists('Redis')) {
            throw new Exception("缺少 Redis 扩展！");
        }

        // 连接
        $driver = new Redis();
        if (PHP_SAPI == "cli") {
            $status = $driver->connect($host, $port, 2, null, 5, 60, ['auth' => [$password]]);
        } else {
            $persistent_id = "persistent_{$host}_{$database}";
            $status = $driver->pconnect($host, $port, 2, $persistent_id, 5, 60, ['auth' => [$password]]);
        }
        if ($status == false) {
            throw new Exception("Connect redis server failed!");
        }
        $driver->select($database);

        return $driver;
    }

    // 获取
    public static function instance(string $name): Redis
    {
        try {
            // 没有则建立连接
            if (static::$instance === null) {
                static::connectFromConfig($name);
            }

            // 判断连接是否挂掉了
            if (!(static::$instance)->ping()) {
                static::connectFromConfig($name);
            }
        } catch (\Throwable $e) {
            static::connectFromConfig($name);
        }

        return static::$instance;
    }

    // 连接
    protected static function connectFromConfig(string $name)
    {
        $configs = Configure::get($name);
        $config = $configs['drivers']["redis"] ?? [];
        if (empty($config)) {
            throw new Exception("Not found config!");
        }

        static::$instance = static::connect(
            $config['host'],
            $config['port'],
            $config['auth'],
            $config['database']
        );
        return static::$instance;
    }
}
