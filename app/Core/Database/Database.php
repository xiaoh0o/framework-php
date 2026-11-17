<?php
namespace App\Core\Database;

use App\Core\HttpException;

/**
 * 数据库连接
 */
class Database
{
    private static $instances  = [];
    private static $timestamps = [];
    private static $timeout    = 7200; // 连接两个小时过期
    private static $configs    = [];

    private function __construct()
    {
    }

    /**
     * 配置数据库连接数据
     *
     * @param array $configs
     * [
     *    "default" => "default",
     *    "drivers" => [
     *        "default" => [
     *            "hostname" => "",
     *            "username" => "",
     *            "password" => "",
     *            "database" => ""
     *        ],
     *        "logs" => [
     *            "hostname" => "",
     *            "username" => "",
     *            "password" => "",
     *            "database" => ""
     *        ]
     *    ]
     * ]
     */
    public static function configs(array $configs)
    {
        static::$configs = $configs;
    }

    /**
     * 创建数据库连接
     *
     * @param string $name
     * @return DatabaseInterface
     */
    public static function connect($name = ""): DatabaseInterface
    {
        if ($name == "") {
            $name = "default";
        }

        // 获取引用的Key
        $key = static::instanceKey($name);

        if (isset(static::$configs['drivers'][$name])) {
            $config = static::$configs['drivers'][$name];
            if (!isset(static::$instances[$key])) {
                static::$instances[$key] = new MySQLiDatabase(
                    $config['hostname'],
                    $config['username'],
                    $config['password'],
                    $config['database']
                );
            }
        } else {
            throw new HttpException("Not found database config!");
        }

        return static::$instances[$key];
    }

    /**
     * 获取引用
     *
     * @param string $name
     * @return DatabaseInterface
     */
    public static function instance($name = "default")
    {
        if ($name == "") {
            $name = "default";
        }

        $key = static::instanceKey($name);
        $timestamp = static::$timestamps[$key] ?? 0;

        if (!isset(static::$instances[$key]) || time() - $timestamp > static::$timeout) {
            static::$timestamps[$key] = time();
            static::$instances[$key] = static::connect($name);
        }

        // 检验连接，并重连
        $db = static::$instances[$key];
        $db->ping();
        return $db;
    }

    /**
     * 获取事务
     *
     * @return Transaction
     */
    public static function transaction(DatabaseInterface $db): Transaction
    {
        return $db->transaction();
    }

    /**
     * 禁用复制
     *
     * @return void
     */
    public function __clone()
    {
        throw new HttpException("单例模式，不可复制！");
    }

    protected static function instanceKey(string $name)
    {
        if (PHP_SAPI == 'cli') {
            return "{$name}_".getmypid();
        }
        return $name;
    }
}
