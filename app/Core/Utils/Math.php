<?php
namespace App\Core\Utils;

use Exception;

/**
 * 常用 数学方法
 */
class Math
{
    // 是否为整型
    public static function isInt(mixed $value)
    {
        if (is_int($value)) {
            return true;
        }
        if (is_string($value)) {
            $value = trim($value);
            if (\preg_match('/^[-+]?\d+$/', $value)) {
                return true;
            }
        }
        return false;
    }

    // 是不是浮点型，不是整型
    public static function isFloat(mixed $value)
    {
        if (is_float($value)) {
            return true;
        }
        if (is_string($value)) {
            $value = trim($value);
            if ($value != "." && \preg_match("/^[-+]?(\d+)?\.(\d+)?$/", $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 是数字型，整型或浮点型
     *
     * @param mixed $value
     * @param int $bit 小数的位数
     * @return bool
     */
    public static function isNumber(mixed $value, ?int $bit = null)
    {
        if (self::isInt($value)) {
            return true;
        }
        if (self::isFloat($value)) {
            if (is_null($bit)) {
                return true;
            }

            // 判断小数的位数
            if (self::isInt($bit) && $bit > 0) {
                return \round($value, $bit) == \floatval($value);
            }
        }
        return false;
    }

    /**
     * 转换成 int
     */
    public static function toInt(mixed $value)
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_null($value) || $value == "") {
            return 0;
        }

        if (is_float($value) && intval($value) == $value) {
            return intval($value);
        }

        if (!self::isInt($value)) {
            throw new Exception("数据类型错误，无法转换成整型！");
        }

        // 字符串类型转换
        $convertInt = intval($value);
        if ($convertInt < PHP_INT_MAX) {
            return $convertInt;
        } else {
            throw new Exception("数据类型错误，整型溢出了！");
        }
    }

    /**
     * 转换成 浮点型
     */
    public static function toFloat(mixed $value)
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_int($value)) {
            return floatval($value);
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_null($value) || $value == "") {
            return 0;
        }

        if (!self::isNumber($value)) {
            throw new Exception("数据类型错误，无法转换成浮点型！");
        }
        return floatval($value);
    }
}
