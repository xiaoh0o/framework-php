<?php
// 消息队列
$CONFIG_REDIS = require(__DIR__.'/redis.php');

return [
    "server"   => "redis",
    'host'     => $CONFIG_REDIS['default']['host'],
    'auth'     => $CONFIG_REDIS['default']['auth'],
    'port'     => $CONFIG_REDIS['default']['port'],
    'database' => 1,
];
