<?php
// 缓存配置
$CONFIG_REDIS = require(__DIR__.'/redis.php');

/**
 * 配置调用方：App\Core\RedisConnection
 * 创建 Redis 连接 => App\Core\Cache\RedisCache
 */
return [
    "default" => "redis",
    "drivers" => [
        "redis" => [
            'host'     => $CONFIG_REDIS['default']['host'],
            'port'     => $CONFIG_REDIS['default']['port'],
            'auth'     => $CONFIG_REDIS['default']['auth'],
            'database' => 0,
        ],
    ]
];
