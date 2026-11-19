<?php
namespace App\Core\Database;

use App\Core\Configure;
use App\Core\HttpException;

/**
 * 数据库连接
 */
class Database
{
    private static array $instances  = [];
    private static array $timestamps = [];
    private static int   $timeout    = 7200; // 连接两个小时过期

    private function __construct()
    {
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

        $configs = Configure::get("database");
        if (isset($configs['drivers'][$name])) {
            $config = $configs['drivers'][$name];
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
    public static function instance($name = "default"): DatabaseInterface
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

        return static::$instances[$key];
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
