<?php
namespace App\Core\Utils;

/**
 * base64 编码字符中存在 + / = 三个特殊字符，在URL传输中可能产生错误
 */
class Base64URL
{
    public static function encode(string $value)
    {
        $base64 = base64_encode($value);
        $base64 = str_replace(["+", "/"], ["-", "_"], $base64);
        return rtrim($base64, "=");
    }

    public static function decode(string $content)
    {
        $content = trim($content);
        $content = str_replace(['-','_'], ['+','/'], $content);
        $mod4 = strlen($content) % 4;
        if ($mod4 > 0) {
            $content .= substr('====', $mod4);
        }
        return base64_decode($content);
    }
}
