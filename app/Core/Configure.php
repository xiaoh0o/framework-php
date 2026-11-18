<?php
namespace App\Core;

/**
 * 配置类
 */
class Configure
{
    protected static string $dir  = CONFIG_PATH; // 配置文件目录路径
    protected static array  $data = []; // 配置数据

    // 获取配置项
    public static function get(string $name)
    {
        if (isset(static::$data[$name])) {
            if (PHP_SAPI != "cli") {
                return static::$data[$name]['data'] ?? [];
            }
            // 判断时间
            $seconds = time() - static::$data[$name]['mtime'] ?? 0;
            if ($seconds < 360) { // 6分钟内
                return static::$data[$name]['data'] ?? [];
            }
        }

        // 读取文件
        $file = static::$dir. "/{$name}.php";
        if (file_exists($file)) {
            $mtime = filemtime($file);
            $data  = require($file);

            static::$data[$name] = [
                "mtime" => $mtime,
                "data"  => $data
            ];

            return static::$data[$name]['data'];
        }
        throw new \Exception("Not found config file! [{$name}]");
    }
}
