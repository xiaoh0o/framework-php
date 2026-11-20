<?php
// 缓存配置
$CONFIG_REDIS = require(__DIR__.'/redis.php');

return [
    "default" => "redis",
    "drivers" => [
        "redis" => [
            'host'     => $CONFIG_REDIS['default']['host'],
            'port'     => $CONFIG_REDIS['default']['port'],
            'auth'     => $CONFIG_REDIS['default']['auth'],
            'database' => 0,
        ],
        "hash" => [
            'host'     => $CONFIG_REDIS['default']['host'],
            'port'     => $CONFIG_REDIS['default']['port'],
            'auth'     => $CONFIG_REDIS['default']['auth'],
            'database' => 0,
        ]
    ]
];
